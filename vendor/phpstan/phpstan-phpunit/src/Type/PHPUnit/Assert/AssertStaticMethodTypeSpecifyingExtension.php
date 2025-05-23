<?php declare(strict_types = 1);

namespace PHPStan\Type\PHPUnit\Assert;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\StaticMethodTypeSpecifyingExtension;

class AssertStaticMethodTypeSpecifyingExtension implements StaticMethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	private TypeSpecifier $typeSpecifier;

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

	public function getClass(): string
	{
		return 'PHPUnit\Framework\Assert';
	}

	public function isStaticMethodSupported(
		MethodReflection $methodReflection,
		StaticCall $node,
		TypeSpecifierContext $context
	): bool
	{
		return AssertTypeSpecifyingExtensionHelper::isSupported(
			$methodReflection->getName(),
			$node->getArgs(),
		);
	}

	public function specifyTypes(
		MethodReflection $functionReflection,
		StaticCall $node,
		Scope $scope,
		TypeSpecifierContext $context
	): SpecifiedTypes
	{
		return AssertTypeSpecifyingExtensionHelper::specifyTypes(
			$this->typeSpecifier,
			$scope,
			$functionReflection->getName(),
			$node->getArgs(),
		);
	}

}
