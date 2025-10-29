<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexStan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use rex_article;
use rex_article_slice;
use rex_category;
use rex_media;
use rex_user;

use function count;
use function in_array;

/**
 * @implements Rule<MethodCall>
 */
final class RexGetValueRule implements Rule
{
    /**
     * @var array<class-string>
     */
    private $classes = [
        rex_user::class,
        rex_article_slice::class,
        rex_article::class,
        rex_category::class,
        rex_media::class,
    ];

    public function getNodeType(): string
    {
        return MethodCall::class;
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

        if (!in_array(strtolower($methodCall->name->toString()), ['getvalue'], true)) {
            return [];
        }

        $methodReflection = $scope->getMethodReflection($scope->getType($methodCall->var), $methodCall->name->toString());
        if ($methodReflection === null) {
            return [];
        }

        $errors = [];
        $callerType = $scope->getType($methodCall->var);
        $classNames = $callerType->getObjectClassNames();
        foreach ($classNames as $className) {
            if (!in_array($className, $this->classes, true)) {
                continue;
            }

            $nameType = $scope->getType($args[0]->value);
            $names = $nameType->getConstantStrings();
            if (count($names) === 0) {
                return [];
            }

            $valueReflection = new RexGetValueReflection();
            if ($valueReflection->getValueType($nameType, $className) !== null) {
                return [];
            }

            $errors[] =
                RuleErrorBuilder::message(
                    sprintf(
                        'Unknown name %s given to %s::getValue().',
                        $nameType->describe(VerbosityLevel::precise()),
                        $className
                    )
                )->identifier('rexstan.rexGetValue')->build()
            ;
        }

        return $errors;
    }
}
