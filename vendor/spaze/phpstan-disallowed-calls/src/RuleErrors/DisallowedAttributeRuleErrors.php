<?php
declare(strict_types = 1);

namespace Spaze\PHPStan\Rules\Disallowed\RuleErrors;

use PhpParser\Node\Attribute;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use Spaze\PHPStan\Rules\Disallowed\Allowed;
use Spaze\PHPStan\Rules\Disallowed\DisallowedAttribute;

class DisallowedAttributeRuleErrors
{

	/** @var Allowed */
	private $allowed;


	public function __construct(Allowed $allowed)
	{
		$this->allowed = $allowed;
	}


	/**
	 * @param Attribute $attribute
	 * @param Scope $scope
	 * @param DisallowedAttribute[] $disallowedAttributes
	 * @return RuleError[]
	 */
	public function get(Attribute $attribute, Scope $scope, array $disallowedAttributes): array
	{
		foreach ($disallowedAttributes as $disallowedAttribute) {
			$attributeName = $attribute->name->toString();
			if (!$this->matchesAttribute($disallowedAttribute->getAttribute(), $attributeName)) {
				continue;
			}
			if ($this->allowed->isAllowed($scope, $attribute->args, $disallowedAttribute)) {
				continue;
			}

			$errorBuilder = RuleErrorBuilder::message(sprintf(
				'Attribute %s is forbidden, %s%s',
				$attributeName,
				$disallowedAttribute->getMessage(),
				$disallowedAttribute->getAttribute() !== $attributeName ? " [{$attributeName} matches {$disallowedAttribute->getAttribute()}]" : ''
			));
			if ($disallowedAttribute->getErrorIdentifier()) {
				$errorBuilder->identifier($disallowedAttribute->getErrorIdentifier());
			}
			if ($disallowedAttribute->getErrorTip()) {
				$errorBuilder->tip($disallowedAttribute->getErrorTip());
			}
			return [
				$errorBuilder->build(),
			];
		}

		return [];
	}


	private function matchesAttribute(string $pattern, string $value): bool
	{
		if ($pattern === $value) {
			return true;
		}

		if (fnmatch($pattern, $value, FNM_NOESCAPE | FNM_CASEFOLD)) {
			return true;
		}

		return false;
	}

}
