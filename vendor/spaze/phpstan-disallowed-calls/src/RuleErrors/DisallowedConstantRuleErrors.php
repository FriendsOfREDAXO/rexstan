<?php
declare(strict_types = 1);

namespace Spaze\PHPStan\Rules\Disallowed\RuleErrors;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use Spaze\PHPStan\Rules\Disallowed\Allowed\AllowedPath;
use Spaze\PHPStan\Rules\Disallowed\DisallowedConstant;
use Spaze\PHPStan\Rules\Disallowed\Formatter\Formatter;

class DisallowedConstantRuleErrors
{

	private AllowedPath $allowedPath;

	private Formatter $formatter;


	public function __construct(AllowedPath $allowedPath, Formatter $formatter)
	{
		$this->allowedPath = $allowedPath;
		$this->formatter = $formatter;
	}


	/**
	 * @param string $constant
	 * @param Scope $scope
	 * @param string|null $displayName
	 * @param list<DisallowedConstant> $disallowedConstants
	 * @param string $identifier
	 * @return list<IdentifierRuleError>
	 * @throws ShouldNotHappenException
	 */
	public function get(string $constant, Scope $scope, ?string $displayName, array $disallowedConstants, string $identifier): array
	{
		foreach ($disallowedConstants as $disallowedConstant) {
			if ($disallowedConstant->getConstant() === $constant && !$this->allowedPath->isAllowedPath($scope, $disallowedConstant)) {
				$errorBuilder = RuleErrorBuilder::message(sprintf(
					'Using %s%s is forbidden%s',
					$disallowedConstant->getConstant(),
					$displayName && $displayName !== $disallowedConstant->getConstant() ? ' (as ' . $displayName . ')' : '',
					$this->formatter->formatDisallowedMessage($disallowedConstant->getMessage())
				));
				$errorBuilder->identifier($disallowedConstant->getErrorIdentifier() ?? $identifier);
				if ($disallowedConstant->getErrorTip()) {
					$errorBuilder->tip($disallowedConstant->getErrorTip());
				}
				return [
					$errorBuilder->build(),
				];
			}
		}
		return [];
	}

}
