<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Rules;

use PhpParser\Node\Attribute;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantStringType;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Rules\CheckAttributteArgumentClassExistsRule\CheckAttributteArgumentClassExistsRuleTest
 */
final class CheckAttributteArgumentClassExistsRule extends AbstractAttributeRule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Class was not found';
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return string[]
     */
    public function processAttribute(Attribute $attribute, Scope $scope): array
    {
        $errors = [];

        foreach ($attribute->args as $arg) {
            $value = $arg->value;
            if (! $this->isClassConstFetch($value)) {
                continue;
            }

            $valueType = $scope->getType($value);
            if (! $valueType instanceof ConstantStringType) {
                $errors[] = self::ERROR_MESSAGE;
                continue;
            }

            if ($this->reflectionProvider->hasClass($valueType->getValue())) {
                continue;
            }

            $errors[] = self::ERROR_MESSAGE;
        }

        return $errors;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
#[SomeAttribute(firstName: 'MissingClass::class')]
class SomeClass
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
#[SomeAttribute(firstName: ExistingClass::class)]
class SomeClass
{
}
CODE_SAMPLE
            ),
        ]);
    }
    private function isClassConstFetch(Expr $expr): bool
    {
        if (! $expr instanceof ClassConstFetch) {
            return false;
        }

        if (! $expr->name instanceof Identifier) {
            return false;
        }

        return $expr->name->toString() === 'class';
    }
}
