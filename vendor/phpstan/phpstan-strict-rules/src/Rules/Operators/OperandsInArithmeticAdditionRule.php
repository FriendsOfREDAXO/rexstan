<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\AssignOp\Plus as AssignOpPlus;
use PhpParser\Node\Expr\BinaryOp\Plus as BinaryOpPlus;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\VerbosityLevel;
use function count;
use function sprintf;

class OperandsInArithmeticAdditionRule implements Rule
{

	/** @var OperatorRuleHelper */
	private $helper;

	/** @var bool */
	private $bleedingEdge;

	public function __construct(OperatorRuleHelper $helper, bool $bleedingEdge)
	{
		$this->helper = $helper;
		$this->bleedingEdge = $bleedingEdge;
	}

	public function getNodeType(): string
	{
		return Expr::class;
	}

	/**
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node instanceof BinaryOpPlus) {
			$left = $node->left;
			$right = $node->right;
		} elseif ($node instanceof AssignOpPlus && $this->bleedingEdge) {
			$left = $node->var;
			$right = $node->expr;
		} else {
			return [];
		}

		$leftType = $scope->getType($left);
		$rightType = $scope->getType($right);
		if (count($leftType->getArrays()) > 0 && count($rightType->getArrays()) > 0) {
			return [];
		}

		$messages = [];
		if (!$this->helper->isValidForArithmeticOperation($scope, $left)) {
			$messages[] = sprintf(
				'Only numeric types are allowed in +, %s given on the left side.',
				$leftType->describe(VerbosityLevel::typeOnly())
			);
		}
		if (!$this->helper->isValidForArithmeticOperation($scope, $right)) {
			$messages[] = sprintf(
				'Only numeric types are allowed in +, %s given on the right side.',
				$rightType->describe(VerbosityLevel::typeOnly())
			);
		}

		return $messages;
	}

}
