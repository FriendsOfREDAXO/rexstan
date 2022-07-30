<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use rex_sql;
use function count;
use PhpParser\Node;

/**
 * @implements Rule<MethodCall>
 */
final class RexSqlGetValueRule implements Rule
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $methodCall, Scope $scope): array
    {
        if (!$methodCall->name instanceof Node\Identifier) {
            return [];
        }

        $args = $methodCall->getArgs();
        if (1 < count($args)) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return [];
        }
        
        $className = $classReflection->getName();
        if ($className !== rex_sql::class) {
            return [];
        }

        $methodName = $methodCall->name->toString();
        if (strtolower($methodName) !== 'getvalue') {
            return [];
        }

        $statementType = $scope->getType($methodCall->var);
        if (!$statementType instanceof GenericObjectType) {
            return [];
        }
        if (rex_sql::class !== $statementType->getClassName()) {
            return [];
        }

        $valueNameType = $scope->getType($args[0]->value);
        if (!$valueNameType instanceof ConstantStringType) {
            return [];
        }

        $sqlResultType = $statementType->getTypes()[0];
        if (!$sqlResultType instanceof ConstantArrayType) {
            return [];
        }

        if ($sqlResultType->hasOffsetValueType($valueNameType)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'New Person instance can be created only in PersonFactory.'
            )->build(),
        ];
    }
}
