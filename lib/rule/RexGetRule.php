<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;
use rex_article;
use rex_category;
use rex_media;
use rex_user;

use function count;
use function in_array;

/**
 * @implements Rule<StaticCall>
 */
final class RexGetRule implements Rule
{
    /**
     * @var array<class-string>
     */
    private $classes = [
        rex_user::class,
        rex_article::class,
        rex_category::class,
        rex_media::class,
    ];

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(Node $methodCall, Scope $scope): array
    {
        $args = $methodCall->getArgs();
        if (count($args) < 1) {
            return [];
        }

        if (!$methodCall->name instanceof Node\Identifier) {
            return [];
        }
        if (!$methodCall->class instanceof Node\Name) {
            return [];
        }

        if (!in_array(strtolower($methodCall->name->toString()), ['get'], true)) {
            return [];
        }

        $callerType = $scope->resolveTypeByName($methodCall->class);
        $methodReflection = $scope->getMethodReflection($callerType, $methodCall->name->toString());
        if ($methodReflection === null) {
            return [];
        }

        if (!in_array($callerType->getClassName(), $this->classes, true)) {
            return [];
        }

        $idType = $scope->getType($args[0]->value);

        if ($callerType->getClassName() === rex_media::class) {
            $ids = $idType->getConstantStrings();
            foreach ($ids as $id) {
                // don't report errors on magic rex-vars, which get resolved at code generation time.
                if (str_starts_with($id->getValue(), 'REX_')) {
                    continue;
                }

                $object = rex_media::get($id->getValue());

                if ($object === null) {
                    return [
                        RuleErrorBuilder::message(
                            sprintf('No %s found with id %s.', $callerType->getClassName(), $idType->describe(VerbosityLevel::precise()))
                        )->identifier('rexstan.rexGet')->build(),
                    ];
                }
            }

            return [];
        }

        $ids = TypeUtils::getConstantIntegers($idType);
        foreach ($ids as $id) {
            switch ($callerType->getClassName()) {
                case rex_user::class:
                    $object = rex_user::get($id->getValue());
                    break;
                case rex_article::class:
                    $object = rex_article::get($id->getValue());
                    break;
                case rex_category::class:
                    $object = rex_category::get($id->getValue());
                    break;
                default: throw new ShouldNotHappenException();
            }

            if ($object === null) {
                return [
                    RuleErrorBuilder::message(
                        sprintf('No %s found with id %s.', $callerType->getClassName(), $idType->describe(VerbosityLevel::precise()))
                    )->identifier('rexstan.rexGet')->build(),
                ];
            }
        }

        return [];
    }
}
