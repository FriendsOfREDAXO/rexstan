<?php
declare(strict_types = 1);

namespace Spaze\PHPStan\Rules\Disallowed\ControlStructures;

use PhpParser\Node;
use PhpParser\Node\Stmt\Continue_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\ShouldNotHappenException;
use Spaze\PHPStan\Rules\Disallowed\DisallowedControlStructure;
use Spaze\PHPStan\Rules\Disallowed\RuleErrors\DisallowedControlStructureRuleErrors;
use Spaze\PHPStan\Rules\Disallowed\RuleErrors\ErrorIdentifiers;

/**
 * Reports on using the continue control structure.
 *
 * @package Spaze\PHPStan\Rules\Disallowed
 * @implements Rule<Continue_>
 */
class ContinueControlStructure implements Rule
{

	private DisallowedControlStructureRuleErrors $disallowedControlStructureRuleErrors;

	/** @var list<DisallowedControlStructure> */
	private array $disallowedControlStructures;


	/**
	 * @param DisallowedControlStructureRuleErrors $disallowedControlStructureRuleErrors
	 * @param list<DisallowedControlStructure> $disallowedControlStructures
	 */
	public function __construct(DisallowedControlStructureRuleErrors $disallowedControlStructureRuleErrors, array $disallowedControlStructures)
	{
		$this->disallowedControlStructureRuleErrors = $disallowedControlStructureRuleErrors;
		$this->disallowedControlStructures = $disallowedControlStructures;
	}


	public function getNodeType(): string
	{
		return Continue_::class;
	}


	/**
	 * @param Continue_ $node
	 * @param Scope $scope
	 * @return list<RuleError>
	 * @throws ShouldNotHappenException
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		return $this->disallowedControlStructureRuleErrors->get($node, $scope, 'continue', $this->disallowedControlStructures, ErrorIdentifiers::DISALLOWED_CONTINUE);
	}

}
