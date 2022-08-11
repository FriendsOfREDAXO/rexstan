<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Rules\Explicit;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Detect array dim fetch assigns to unknown arrays. The dim type e.g. array<string, mixed> should be defined there.
 *
 * @see \Symplify\PHPStanRules\Tests\Rules\Explicit\NoMixedArrayDimFetchRule\NoMixedArrayDimFetchRuleTest
 */
final class NoMixedArrayDimFetchRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Add explicit array type to assigned "%s" expression';

    /**
     * @var \PhpParser\PrettyPrinter\Standard
     */
    private $printerStandard;

    public function __construct()
    {
        $this->printerStandard = new Standard();
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return Assign::class;
    }

    /**
     * @param Assign $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->var instanceof ArrayDimFetch) {
            return [];
        }

        $arrayDimFetch = $node->var;

        // only check for exact dim values
        if ($arrayDimFetch->dim === null) {
            return [];
        }

        if (! $arrayDimFetch->var instanceof PropertyFetch && ! $arrayDimFetch->var instanceof Variable) {
            return [];
        }

        if ($this->isExternalClass($arrayDimFetch, $scope)) {
            return [];
        }

        $rootDimFetchType = $scope->getType($arrayDimFetch->var);

        // skip complex types for now
        if ($this->shouldSkipRootDimFetchType($rootDimFetchType)) {
            return [];
        }

        $printedVariable = $this->printerStandard->prettyPrintExpr($arrayDimFetch->var);

        return [sprintf(self::ERROR_MESSAGE, $printedVariable)];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [new CodeSample(
            <<<'CODE_SAMPLE'
class SomeClass
{
    private $items = [];

    public function addItem(string $key, string $value)
    {
        $this->items[$key] = $value;
    }
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var array<string, string>
     */
    private $items = [];

    public function addItem(string $key, string $value)
    {
        $this->items[$key] = $value;
    }
}
CODE_SAMPLE
        ),
        ]);
    }

    private function shouldSkipRootDimFetchType(Type $type): bool
    {
        if ($type instanceof UnionType) {
            return true;
        }

        if ($type instanceof IntersectionType) {
            return true;
        }

        if ($type instanceof StringType) {
            return true;
        }

        return $type instanceof ArrayType && ! $type->getKeyType() instanceof MixedType;
    }

    private function isExternalClass(ArrayDimFetch $arrayDimFetch, Scope $scope): bool
    {
        if (! $arrayDimFetch->var instanceof PropertyFetch) {
            return false;
        }

        $propertyFetch = $arrayDimFetch->var;

        $propertyFetcherType = $scope->getType($propertyFetch->var);
        if (! $propertyFetcherType instanceof ObjectType) {
            return false;
        }

        $classReflection = $propertyFetcherType->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return true;
        }

        if ($classReflection->isInternal()) {
            return true;
        }

        $fileName = $classReflection->getFileName();
        if ($fileName === null) {
            return false;
        }

        return strpos($fileName, '/vendor/') !== false;
    }
}
