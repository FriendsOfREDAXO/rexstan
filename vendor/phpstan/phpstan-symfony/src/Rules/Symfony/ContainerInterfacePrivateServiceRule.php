<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Symfony\ServiceMap;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use function sprintf;

/**
 * @implements Rule<MethodCall>
 */
final class ContainerInterfacePrivateServiceRule implements Rule
{

	private ServiceMap $serviceMap;

	public function __construct(ServiceMap $symfonyServiceMap)
	{
		$this->serviceMap = $symfonyServiceMap;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Node\Identifier) {
			return [];
		}

		if ($node->name->name !== 'get' || !isset($node->getArgs()[0])) {
			return [];
		}

		$argType = $scope->getType($node->var);

		$isTestContainer = $this->isTestContainer($argType, $scope);
		$isOldServiceSubscriber = (new ObjectType('Symfony\Component\DependencyInjection\ServiceSubscriberInterface'))->isSuperTypeOf($argType);
		$isServiceSubscriber = $this->isServiceSubscriber($argType, $scope);
		$isServiceLocator = (new ObjectType('Symfony\Component\DependencyInjection\ServiceLocator'))->isSuperTypeOf($argType);
		if ($isTestContainer->yes() || $isOldServiceSubscriber->yes() || $isServiceSubscriber->yes() || $isServiceLocator->yes()) {
			return [];
		}

		$isControllerType = (new ObjectType('Symfony\Bundle\FrameworkBundle\Controller\Controller'))->isSuperTypeOf($argType);
		$isAbstractControllerType = (new ObjectType('Symfony\Bundle\FrameworkBundle\Controller\AbstractController'))->isSuperTypeOf($argType);
		$isContainerType = (new ObjectType('Symfony\Component\DependencyInjection\ContainerInterface'))->isSuperTypeOf($argType);
		$isPsrContainerType = (new ObjectType('Psr\Container\ContainerInterface'))->isSuperTypeOf($argType);
		if (
			!$isControllerType->yes()
			&& !$isAbstractControllerType->yes()
			&& !$isContainerType->yes()
			&& !$isPsrContainerType->yes()
		) {
			return [];
		}

		$serviceId = $this->serviceMap::getServiceIdFromNode($node->getArgs()[0]->value, $scope);
		if ($serviceId !== null) {
			$service = $this->serviceMap->getService($serviceId);
			if ($service !== null && !$service->isPublic()) {
				return [
					RuleErrorBuilder::message(sprintf('Service "%s" is private.', $serviceId))
						->identifier('symfonyContainer.privateService')
						->build(),
				];
			}
		}

		return [];
	}

	private function isServiceSubscriber(Type $containerType, Scope $scope): TrinaryLogic
	{
		$serviceSubscriberInterfaceType = new ObjectType('Symfony\Contracts\Service\ServiceSubscriberInterface');
		$isContainerServiceSubscriber = $serviceSubscriberInterfaceType->isSuperTypeOf($containerType)->result;
		$classReflection = $scope->getClassReflection();
		if ($classReflection === null) {
			return $isContainerServiceSubscriber;
		}
		$containedClassType = new ObjectType($classReflection->getName());
		return $isContainerServiceSubscriber->or($serviceSubscriberInterfaceType->isSuperTypeOf($containedClassType)->result);
	}

	private function isTestContainer(Type $containerType, Scope $scope): TrinaryLogic
	{
		$testContainer = new ObjectType('Symfony\Bundle\FrameworkBundle\Test\TestContainer');
		$isTestContainer = $testContainer->isSuperTypeOf($containerType)->result;

		$classReflection = $scope->getClassReflection();
		if ($classReflection === null) {
			return $isTestContainer;
		}

		$containerInterface = new ObjectType('Symfony\Component\DependencyInjection\ContainerInterface');
		$kernelTestCase = new ObjectType('Symfony\Bundle\FrameworkBundle\Test\KernelTestCase');
		$containedClassType = new ObjectType($classReflection->getName());

		return $isTestContainer->or(
			$containerInterface->isSuperTypeOf($containerType)->result->and(
				$kernelTestCase->isSuperTypeOf($containedClassType)->result,
			),
		);
	}

}
