<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\NodeAnalyzer;

use PhpParser\Node\Arg;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ConstantScalarType;

final class ScalarValueResolver
{
    /**
     * @param array<Arg> $args
     * @return mixed[]
     */
    public function resolveValuesCountFromArgs(array $args, Scope $scope): array
    {
        $resolveValues = $this->resolvedValues($args, $scope);

        // filter out false/true values
        $resolvedValuesWithoutBool = \array_filter(
            $resolveValues,
            function ($value) : bool {
                return ! $this->shouldSkipValue($value);
            }
        );
        if ($resolvedValuesWithoutBool === []) {
            return [];
        }

        return $this->countValues($resolvedValuesWithoutBool);
    }

    /**
     * @param array<Arg> $args
     * @return mixed[]
     */
    private function resolvedValues(array $args, Scope $scope): array
    {
        $passedValues = [];
        foreach ($args as $arg) {
            if (! $arg instanceof Arg) {
                continue;
            }

            $valueType = $scope->getType($arg->value);
            if (! $valueType instanceof ConstantScalarType) {
                continue;
            }

            $resolvedValue = $valueType->getValue();

            // skip simple values
            if ($resolvedValue === '') {
                continue;
            }

            $passedValues[] = $resolvedValue;
        }

        return $passedValues;
    }

    /**
     * @param mixed[] $values
     * @return mixed[]
     */
    private function countValues(array $values): array
    {
        if ($values === []) {
            return [];
        }

        // the array_count_values ignores "null", so we have to translate it to string here
        /** @var array<string|int> $values */
        $values = array_filter($values, function ($value) : bool {
            return $this->isFilterableValue($value);
        });

        return \array_count_values($values);
    }

    /**
     * Makes values ready for array_count_values(), it accepts only numeric or strings; no objects nor arrays
     * @param mixed $value
     */
    private function isFilterableValue($value): bool
    {
        if (is_numeric($value)) {
            return true;
        }

        return is_string($value);
    }

    /**
     * @param mixed $value
     */
    private function shouldSkipValue($value): bool
    {
        // value could not be resolved
        if ($value === null) {
            return true;
        }

        if (is_array($value)) {
            return true;
        }

        // simple values, probably boolean markers or type constants
        if (\in_array($value, [0, 1], true)) {
            return true;
        }

        return \is_bool($value);
    }
}
