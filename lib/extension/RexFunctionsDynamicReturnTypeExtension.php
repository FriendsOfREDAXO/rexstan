<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use rex_request;

use function count;
use function in_array;

final class RexFunctionsDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension, DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex_request::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array(
            strtolower($methodReflection->getName()),
            ['get', 'post', 'request', 'server', 'session', 'cookie', 'files', 'env'],
            true
        );
    }

    public function getTypeFromStaticMethodCall(
        MethodReflection $methodReflection,
        StaticCall $methodCall,
        Scope $scope
    ): ?Type {
        return $this->getType($methodCall->getArgs(), $scope);
    }

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return in_array($functionReflection->getName(), ['rex_get', 'rex_post', 'rex_request', 'rex_server', 'rex_session', 'rex_cookie', 'rex_files', 'rex_env'], true);
    }

    public function getTypeFromFunctionCall(
        FunctionReflection $functionReflection,
        FuncCall $functionCall,
        Scope $scope
    ): ?Type {
        return $this->getType($functionCall->getArgs(), $scope);
    }

    /**
     * @param Arg[] $args
     */
    private function getType(array $args, Scope $scope): ?Type
    {
        if (count($args) < 2) {
            return null;
        }

        $results = [];
        if (count($args) >= 3) {
            $defaultArgType = $scope->getType($args[2]->value);
            $defaultArgTypeStrings = $defaultArgType->getConstantStrings();
            if (count($defaultArgTypeStrings) !== 1 || $defaultArgTypeStrings[0]->getValue() !== '') {
                $results[] = $defaultArgType;
            }
        }

        $typeStrings = $scope->getType($args[1]->value)->getConstantStrings();
        if (count($typeStrings) > 0) {
            foreach ($typeStrings as $typeString) {
                $resolvedType = $this->resolveTypeFromString($typeString->getValue());
                if ($resolvedType === null) {
                    return null;
                }

                $results[] = $resolvedType;
            }

            return TypeCombinator::union(...$results);
        }

        return null;
    }

    private function resolveTypeFromString(string $vartype): ?Type
    {
        if (in_array($vartype, [
            'bool',
            'boolean',
        ], true)) {
            return new BooleanType();
        }

        if (in_array($vartype, [
            'int',
            'integer',
        ], true)) {
            return new IntegerType();
        }

        if (in_array($vartype, [
            'double',
            'float',
            'real',
        ], true)) {
            return new FloatType();
        }

        if (in_array($vartype, [
            'string',
        ], true)) {
            return new StringType();
        }

        if (in_array($vartype, [
            'object',
        ], true)) {
            return new ObjectWithoutClassType();
        }

        if (in_array($vartype, [
            'array',
        ], true)) {
            return new ArrayType(new MixedType(), new MixedType());
        }

        if (preg_match('/^array\[(.+)\]$/', $vartype, $match) === 1) {
            $valueType = $this->resolveTypeFromString($match[1]);

            if ($valueType === null) {
                throw new ShouldNotHappenException();
            }

            return new ArrayType(
                new MixedType(),
                $valueType
            );
        }

        return null;
    }
}
