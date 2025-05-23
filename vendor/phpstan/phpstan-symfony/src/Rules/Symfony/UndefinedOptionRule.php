<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Printer\Printer;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Symfony\ConsoleApplicationResolver;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Symfony\Helper;
use function count;
use function sprintf;

/**
 * @implements Rule<MethodCall>
 */
final class UndefinedOptionRule implements Rule
{

	private ConsoleApplicationResolver $consoleApplicationResolver;

	private Printer $printer;

	public function __construct(ConsoleApplicationResolver $consoleApplicationResolver, Printer $printer)
	{
		$this->consoleApplicationResolver = $consoleApplicationResolver;
		$this->printer = $printer;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$classReflection = $scope->getClassReflection();
		if ($classReflection === null) {
			return [];
		}

		if (!(new ObjectType('Symfony\Component\Console\Command\Command'))->isSuperTypeOf(new ObjectType($classReflection->getName()))->yes()) {
			return [];
		}
		if (!(new ObjectType('Symfony\Component\Console\Input\InputInterface'))->isSuperTypeOf($scope->getType($node->var))->yes()) {
			return [];
		}
		if (!$node->name instanceof Node\Identifier || $node->name->name !== 'getOption') {
			return [];
		}
		if (!isset($node->getArgs()[0])) {
			return [];
		}

		$optType = $scope->getType($node->getArgs()[0]->value);
		$optStrings = $optType->getConstantStrings();
		if (count($optStrings) !== 1) {
			return [];
		}
		$optName = $optStrings[0]->getValue();

		$errors = [];
		foreach ($this->consoleApplicationResolver->findCommands($classReflection) as $name => $command) {
			try {
				$command->mergeApplicationDefinition();
				$command->getDefinition()->getOption($optName);
			} catch (InvalidArgumentException $e) {
				if ($scope->getType(Helper::createMarkerNode($node->var, $optType, $this->printer))->equals($optType)) {
					continue;
				}
				$errors[] = RuleErrorBuilder::message(sprintf('Command "%s" does not define option "%s".', $name, $optName))
					->identifier('symfonyConsole.optionNotFound')
					->build();
			}
		}

		return $errors;
	}

}
