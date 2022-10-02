<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
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
        if (null === $methodReflection) {
            return [];
        }

        $callerType = $scope->getType($methodCall->var);
        if (!$callerType instanceof TypeWithClassName) {
            return [];
        }

        if (!in_array($callerType->getClassname(), $this->classes, true)) {
            return [];
        }

        $nameType = $scope->getType($args[0]->value);
        $names = TypeUtils::getConstantStrings($nameType);
        if (0 === count($names)) {
            return [];
        }

        $valueReflection = new RexGetValueReflection();
        if (null !== $valueReflection->getValueType($nameType, $callerType->getClassname())) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf("Unknown name '%s' given to getValue().", $nameType->describe(VerbosityLevel::precise()))
            )->build(),
        ];
    }
}
