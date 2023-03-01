<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\VerbosityLevel;
use staabm\PHPStanDba\QueryReflection\QueryReflection;
use function count;
use function in_array;

/**
 * @implements Rule<MethodCall>
 */
final class RexSqlSetTableRule implements Rule
{
    /**
     * @var QueryReflection
     */
    private $queryReflection;

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $methodCall, Scope $scope): array
    {
        $args = $methodCall->getArgs();
        if (count($args) !== 1) {
            return [];
        }

        if (!$methodCall->name instanceof Node\Identifier) {
            return [];
        }

        if (!in_array(strtolower($methodCall->name->toString()), ['settable'], true)) {
            return [];
        }

        if ($this->queryReflection === null) {
            $this->queryReflection = new QueryReflection();
        }
        $schemaReflection = $this->queryReflection->getSchemaReflection();

        $tableNameType = $scope->getType($args[0]->value);
        $errors = [];
        foreach($tableNameType->getConstantStrings() as $constantString) {
            if ($schemaReflection->getTable($constantString->getValue()) === null) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf("Table '%s' does not exist.", $constantString->getValue())
                )->build();
            }
        }

        return $errors;
    }
}
