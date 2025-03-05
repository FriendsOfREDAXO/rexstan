<?php
declare(strict_types = 1);

namespace Spaze\PHPStan\Rules\Disallowed\Allowed;

use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\NullType;
use PHPStan\Type\VerbosityLevel;
use Spaze\PHPStan\Rules\Disallowed\Exceptions\UnsupportedParamTypeInConfigException;
use Spaze\PHPStan\Rules\Disallowed\Normalizer\Normalizer;
use Spaze\PHPStan\Rules\Disallowed\Params\ParamValue;
use Spaze\PHPStan\Rules\Disallowed\Params\ParamValueAny;
use Spaze\PHPStan\Rules\Disallowed\Params\ParamValueCaseInsensitiveExcept;
use Spaze\PHPStan\Rules\Disallowed\Params\ParamValueExcept;
use Spaze\PHPStan\Rules\Disallowed\Params\ParamValueExceptAny;
use Spaze\PHPStan\Rules\Disallowed\Params\ParamValueFlag;
use Spaze\PHPStan\Rules\Disallowed\Params\ParamValueFlagExcept;
use Spaze\PHPStan\Rules\Disallowed\Params\ParamValueFlagSpecific;
use Spaze\PHPStan\Rules\Disallowed\Params\ParamValueSpecific;

class AllowedConfigFactory
{

	private Normalizer $normalizer;

	private TypeStringResolver $typeStringResolver;


	public function __construct(
		Normalizer $normalizer,
		TypeStringResolver $typeStringResolver
	) {
		$this->normalizer = $normalizer;
		$this->typeStringResolver = $typeStringResolver;
	}


	/**
	 * @param array $allowed
	 * @phpstan-param AllowDirectivesConfig $allowed
	 * @return AllowedConfig
	 * @throws UnsupportedParamTypeInConfigException
	 */
	public function getConfig(array $allowed): AllowedConfig
	{
		$allowInCalls = $allowExceptInCalls = $allowInInstanceOf = $allowExceptInInstanceOf = $allowInClassWithAttributes = $allowExceptInClassWithAttributes = [];
		$allowInCallsWithAttributes = $allowExceptInCallsWithAttributes = $allowInClassWithMethodAttributes = $allowExceptInClassWithMethodAttributes = [];
		$allowParamsInAllowed = $allowParamsAnywhere = $allowExceptParamsInAllowed = $allowExceptParams = [];

		foreach ($allowed['allowInFunctions'] ?? $allowed['allowInMethods'] ?? [] as $allowedCall) {
			$allowInCalls[] = $this->normalizer->normalizeCall($allowedCall);
		}
		foreach ($allowed['allowExceptInFunctions'] ?? $allowed['allowExceptInMethods'] ?? $allowed['disallowInFunctions'] ?? $allowed['disallowInMethods'] ?? [] as $disallowedCall) {
			$allowExceptInCalls[] = $this->normalizer->normalizeCall($disallowedCall);
		}
		foreach ($allowed['allowInInstanceOf'] ?? [] as $allowedInstanceOf) {
			$allowInInstanceOf[] = $this->normalizer->normalizeNamespace($allowedInstanceOf);
		}
		foreach ($allowed['allowExceptInInstanceOf'] ?? $allowed['disallowInInstanceOf'] ?? [] as $disallowedInstanceOf) {
			$allowExceptInInstanceOf[] = $this->normalizer->normalizeNamespace($disallowedInstanceOf);
		}
		foreach ($allowed['allowInClassWithAttributes'] ?? [] as $allowInClassAttribute) {
			$allowInClassWithAttributes[] = $this->normalizer->normalizeAttribute($allowInClassAttribute);
		}
		foreach ($allowed['allowExceptInClassWithAttributes'] ?? $allowed['disallowInClassWithAttributes'] ?? [] as $allowExceptInClassAttribute) {
			$allowExceptInClassWithAttributes[] = $this->normalizer->normalizeAttribute($allowExceptInClassAttribute);
		}
		foreach ($allowed['allowInFunctionsWithAttributes'] ?? $allowed['allowInMethodsWithAttributes'] ?? [] as $allowInMethodAttribute) {
			$allowInCallsWithAttributes[] = $this->normalizer->normalizeAttribute($allowInMethodAttribute);
		}
		foreach ($allowed['allowExceptInFunctionsWithAttributes'] ?? $allowed['allowExceptInMethodsWithAttributes'] ?? $allowed['disallowInFunctionsWithAttributes'] ?? $allowed['disallowInMethodsWithAttributes'] ?? [] as $allowExceptInMethodAttribute) {
			$allowExceptInCallsWithAttributes[] = $this->normalizer->normalizeAttribute($allowExceptInMethodAttribute);
		}
		foreach ($allowed['allowInClassWithMethodAttributes'] ?? [] as $allowInAnyMethodAttribute) {
			$allowInClassWithMethodAttributes[] = $this->normalizer->normalizeAttribute($allowInAnyMethodAttribute);
		}
		foreach ($allowed['allowExceptInClassWithMethodAttributes'] ?? $allowed['disallowInClassWithMethodAttributes'] ?? [] as $allowExceptInAnyMethodAttribute) {
			$allowExceptInClassWithMethodAttributes[] = $this->normalizer->normalizeAttribute($allowExceptInAnyMethodAttribute);
		}
		foreach ($allowed['allowParamsInAllowed'] ?? [] as $param => $value) {
			$allowParamsInAllowed[$param] = $this->paramFactory(ParamValueSpecific::class, $param, $value);
		}
		foreach ($allowed['allowParamsInAllowedAnyValue'] ?? [] as $param => $value) {
			$allowParamsInAllowed[$param] = $this->paramFactory(ParamValueAny::class, $param, $value);
		}
		foreach ($allowed['allowParamFlagsInAllowed'] ?? [] as $param => $value) {
			$allowParamsInAllowed[$param] = $this->paramFactory(ParamValueFlagSpecific::class, $param, $value);
		}
		foreach ($allowed['allowParamsAnywhere'] ?? [] as $param => $value) {
			$allowParamsAnywhere[$param] = $this->paramFactory(ParamValueSpecific::class, $param, $value);
		}
		foreach ($allowed['allowParamsAnywhereAnyValue'] ?? [] as $param => $value) {
			$allowParamsAnywhere[$param] = $this->paramFactory(ParamValueAny::class, $param, $value);
		}
		foreach ($allowed['allowParamFlagsAnywhere'] ?? [] as $param => $value) {
			$allowParamsAnywhere[$param] = $this->paramFactory(ParamValueFlagSpecific::class, $param, $value);
		}
		foreach ($allowed['allowExceptParamsInAllowed'] ?? $allowed['disallowParamsInAllowed'] ?? [] as $param => $value) {
			$allowExceptParamsInAllowed[$param] = $this->paramFactory(ParamValueExcept::class, $param, $value);
		}
		foreach ($allowed['allowExceptParamFlagsInAllowed'] ?? $allowed['disallowParamFlagsInAllowed'] ?? [] as $param => $value) {
			$allowExceptParamsInAllowed[$param] = $this->paramFactory(ParamValueFlagExcept::class, $param, $value);
		}
		foreach ($allowed['allowExceptParams'] ?? $allowed['disallowParams'] ?? [] as $param => $value) {
			$allowExceptParams[$param] = $this->paramFactory(ParamValueExcept::class, $param, $value);
		}
		foreach ($allowed['allowExceptParamsAnyValue'] ?? $allowed['disallowParamsAnyValue'] ?? [] as $param => $value) {
			$allowExceptParams[$param] = $this->paramFactory(ParamValueExceptAny::class, $param, $value);
		}
		foreach ($allowed['allowExceptParamFlags'] ?? $allowed['disallowParamFlags'] ?? [] as $param => $value) {
			$allowExceptParams[$param] = $this->paramFactory(ParamValueFlagExcept::class, $param, $value);
		}
		foreach ($allowed['allowExceptCaseInsensitiveParams'] ?? $allowed['disallowCaseInsensitiveParams'] ?? [] as $param => $value) {
			$allowExceptParams[$param] = $this->paramFactory(ParamValueCaseInsensitiveExcept::class, $param, $value);
		}
		return new AllowedConfig(
			$allowed['allowIn'] ?? [],
			$allowed['allowExceptIn'] ?? $allowed['disallowIn'] ?? [],
			$allowInCalls,
			$allowExceptInCalls,
			$allowInInstanceOf,
			$allowExceptInInstanceOf,
			$allowInClassWithAttributes,
			$allowExceptInClassWithAttributes,
			$allowInCallsWithAttributes,
			$allowExceptInCallsWithAttributes,
			$allowInClassWithMethodAttributes,
			$allowExceptInClassWithMethodAttributes,
			$allowParamsInAllowed,
			$allowParamsAnywhere,
			$allowExceptParamsInAllowed,
			$allowExceptParams
		);
	}


	/**
	 * @template T of ParamValue
	 * @param class-string<T> $class
	 * @param int|string $key
	 * @param int|bool|string|null|array{position:int, value?:int|bool|string, typeString?:string, name?:string} $value
	 * @return T
	 * @throws UnsupportedParamTypeInConfigException
	 */
	private function paramFactory(string $class, $key, $value): ParamValue
	{
		if (is_numeric($key)) {
			if (is_array($value)) {
				$paramPosition = $value['position'];
				$paramName = $value['name'] ?? null;
				$paramValue = $value['value'] ?? null;
				$typeString = $value['typeString'] ?? null;
			} elseif (in_array($class, [ParamValueAny::class, ParamValueExceptAny::class], true)) {
				if (is_numeric($value)) {
					$paramPosition = (int)$value;
					$paramName = null;
				} else {
					$paramPosition = null;
					$paramName = (string)$value;
				}
				$paramValue = $typeString = null;
			} else {
				$paramPosition = (int)$key;
				$paramName = null;
				$paramValue = $value;
				$typeString = null;
			}
		} else {
			$paramPosition = null;
			$paramName = $key;
			$paramValue = $value;
			$typeString = null;
		}

		if ($typeString) {
			$type = $this->typeStringResolver->resolve($typeString);
		} elseif (is_int($paramValue)) {
			$type = new ConstantIntegerType($paramValue);
		} elseif (is_bool($paramValue)) {
			$type = new ConstantBooleanType($paramValue);
		} elseif (is_string($paramValue)) {
			$type = new ConstantStringType($paramValue);
		} elseif (is_null($paramValue)) {
			$type = new NullType();
		} else {
			throw new UnsupportedParamTypeInConfigException($paramPosition, $paramName, gettype($paramValue));
		}
		if (is_subclass_of($class, ParamValueFlag::class)) {
			foreach ($type->getConstantScalarValues() as $value) {
				if (!is_int($value)) {
					throw new UnsupportedParamTypeInConfigException($paramPosition, $paramName, gettype($value) . ' of ' . $type->describe(VerbosityLevel::precise()));
				}
			}
		}
		return new $class($paramPosition, $paramName, $type);
	}

}
