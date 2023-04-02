<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
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
        if (1 !== count($args)) {
            return [];
        }

        if (!$methodCall->name instanceof Node\Identifier) {
            return [];
        }

        if (!in_array(strtolower($methodCall->name->toString()), ['settable'], true)) {
            return [];
        }

        if (null === $this->queryReflection) {
            $this->queryReflection = new QueryReflection();
        }
        $schemaReflection = $this->queryReflection->getSchemaReflection();

        $tableNameType = $scope->getType($args[0]->value);
        $errors = [];
        foreach ($tableNameType->getConstantStrings() as $constantString) {
            if (null === $schemaReflection->getTable($constantString->getValue())) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf("Table '%s' does not exist.", $constantString->getValue())
                )->build();
            }
        }

        return $errors;
    }
}
