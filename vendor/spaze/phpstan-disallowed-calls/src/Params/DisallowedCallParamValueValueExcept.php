<?php
declare(strict_types = 1);

namespace Spaze\PHPStan\Rules\Disallowed\Params;

use PHPStan\Type\ConstantScalarType;

/**
 * @extends DisallowedCallParamValue<int|bool|string|null>
 */
class DisallowedCallParamValueValueExcept extends DisallowedCallParamValue
{

	public function matches(ConstantScalarType $type): bool
	{
		return $this->getValue() !== $type->getValue();
	}

}