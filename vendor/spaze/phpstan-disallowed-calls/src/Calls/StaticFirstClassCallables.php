<?php
declare(strict_types = 1);

namespace Spaze\PHPStan\Rules\Disallowed\Calls;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\StaticMethodCallableNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\ShouldNotHappenException;
use Spaze\PHPStan\Rules\Disallowed\DisallowedCall;
use Spaze\PHPStan\Rules\Disallowed\DisallowedCallFactory;
use Spaze\PHPStan\Rules\Disallowed\RuleErrors\DisallowedMethodRuleErrors;

/**
 * Reports on first class callable syntax for a disallowed static method.
 *
 * Dynamic calls have a different rule, <code>MethodFirstClassCallables</code>
 *
 * @package Spaze\PHPStan\Rules\Disallowed
 * @implements Rule<StaticMethodCallableNode>
 */
class StaticFirstClassCallables implements Rule
{

	private DisallowedMethodRuleErrors $disallowedMethodRuleErrors;

	/** @var list<DisallowedCall> */
	private array $disallowedCalls;


	/**
	 * @param DisallowedMethodRuleErrors $disallowedMethodRuleErrors
	 * @param DisallowedCallFactory $disallowedCallFactory
	 * @param array $forbiddenCalls
	 * @phpstan-param ForbiddenCallsConfig $forbiddenCalls
	 * @noinspection PhpUndefinedClassInspection ForbiddenCallsConfig is a type alias defined in PHPStan config
	 * @throws ShouldNotHappenException
	 */
	public function __construct(DisallowedMethodRuleErrors $disallowedMethodRuleErrors, DisallowedCallFactory $disallowedCallFactory, array $forbiddenCalls)
	{
		$this->disallowedMethodRuleErrors = $disallowedMethodRuleErrors;
		$this->disallowedCalls = $disallowedCallFactory->createFromConfig($forbiddenCalls);
	}


	public function getNodeType(): string
	{
		return StaticMethodCallableNode::class;
	}


	/**
	 * @param StaticMethodCallableNode $node
	 * @param Scope $scope
	 * @return list<RuleError>
	 * @throws ShouldNotHappenException
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$originalNode = $node->getOriginalNode();
		return $this->disallowedMethodRuleErrors->get($originalNode->class, $originalNode, $scope, $this->disallowedCalls);
	}

}
