<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Rules\Complexity;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\TypeWithClassName;
use Symplify\PHPStanRules\Enum\RuleIdentifier;

/**
 * @implements Rule<Array_>
 * @see \Symplify\PHPStanRules\Tests\Rules\Complexity\ForbiddenArrayMethodCallRule\ForbiddenArrayMethodCallRuleTest
 */
final class ForbiddenArrayMethodCallRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Array method calls [$this, "method"] are not allowed. Use explicit method instead to help PhpStorm, PHPStan and Rector understand your code';

    public function getNodeType(): string
    {
        return Array_::class;
    }

    /**
     * @param Array_ $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (count($node->items) !== 2) {
            return [];
        }

        $typeWithClassName = $this->resolveFirstArrayItemClassType($node, $scope);
        if (! $typeWithClassName instanceof TypeWithClassName) {
            return [];
        }

        $methodName = $this->resolveSecondArrayItemMethodName($node, $scope);
        if ($methodName === null) {
            return [];
        }

        // does method exist?
        if (! $typeWithClassName->hasMethod($methodName)->yes()) {
            return [];
        }

        return [RuleErrorBuilder::message(self::ERROR_MESSAGE)
            ->identifier(RuleIdentifier::FORBIDDEN_ARRAY_METHOD_CALL)
            ->build()];
    }

    private function resolveFirstArrayItemClassType(Array_ $array, Scope $scope): ?TypeWithClassName
    {
        $firstItem = $array->items[0];
        if (! $firstItem instanceof ArrayItem) {
            return null;
        }

        $firstItemType = $scope->getType($firstItem->value);
        if (! $firstItemType instanceof TypeWithClassName) {
            return null;
        }

        return $firstItemType;
    }

    private function resolveSecondArrayItemMethodName(Array_ $array, Scope $scope): ?string
    {
        $secondItem = $array->items[1];
        if (! $secondItem instanceof ArrayItem) {
            return null;
        }

        $secondItemValue = $secondItem->value;

        $secondItemType = $scope->getType($secondItemValue);
        if (! $secondItemType instanceof ConstantStringType) {
            return null;
        }

        return $secondItemType->getValue();
    }
}
