<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use InvalidArgumentException;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Symfony\ConsoleApplicationResolver;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use function array_unique;
use function count;
use function in_array;

final class InputInterfaceHasArgumentDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	private ConsoleApplicationResolver $consoleApplicationResolver;

	public function __construct(ConsoleApplicationResolver $consoleApplicationResolver)
	{
		$this->consoleApplicationResolver = $consoleApplicationResolver;
	}

	public function getClass(): string
	{
		return 'Symfony\Component\Console\Input\InputInterface';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'hasArgument';
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
	{
		if (!isset($methodCall->getArgs()[0])) {
			return null;
		}

		$classReflection = $scope->getClassReflection();
		if ($classReflection === null) {
			return null;
		}

		$argStrings = $scope->getType($methodCall->getArgs()[0]->value)->getConstantStrings();
		if (count($argStrings) !== 1) {
			return null;
		}
		$argName = $argStrings[0]->getValue();

		if ($argName === 'command') {
			$method = $scope->getFunction();
			if (
				$method instanceof MethodReflection
				&& ($method->getName() === 'interact' || $method->getName() === 'initialize')
				&& in_array('Symfony\Component\Console\Command\Command', $method->getDeclaringClass()->getParentClassesNames(), true)
			) {
				return null;
			}
		}

		$returnTypes = [];
		foreach ($this->consoleApplicationResolver->findCommands($classReflection) as $command) {
			try {
				$command->mergeApplicationDefinition();
				$command->getDefinition()->getArgument($argName);
				$returnTypes[] = true;
			} catch (InvalidArgumentException $e) {
				$returnTypes[] = false;
			}
		}

		if (count($returnTypes) === 0) {
			return null;
		}

		$returnTypes = array_unique($returnTypes);
		return count($returnTypes) === 1 ? new ConstantBooleanType($returnTypes[0]) : null;
	}

}
