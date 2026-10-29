<?php

declare(strict_types=1);

namespace Entropy\Console\Mapper;

use Entropy\Attribute\RelatedTest;
use Entropy\Console\Contract\CommandInterface;
use Entropy\Console\Exception\InvalidCommandException;
use Entropy\Console\ValueObject\Argument;
use Entropy\Console\ValueObject\ArgumentsAndOptions;
use Entropy\Console\ValueObject\Option;
use Entropy\Reflection\ParameterDescriptionResolver;
use Entropy\Reflection\ParameterOptionMarkerResolver;
use Entropy\Tests\Console\Mapper\CommandRunParametersMapperTest;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * @see \Entropy\Tests\Console\Mapper\CommandRunParametersMapperTest
 */
#[RelatedTest(CommandRunParametersMapperTest::class)]
final class CommandRunParametersMapper
{
    public function map(CommandInterface $command): ArgumentsAndOptions
    {
        $runReflectionMethod = new ReflectionMethod($command, 'run');

        $paramDescriptions = ParameterDescriptionResolver::resolve($runReflectionMethod);
        $optionMarkers = ParameterOptionMarkerResolver::resolve($runReflectionMethod);

        $arguments = [];
        $options = [];

        foreach ($runReflectionMethod->getParameters() as $key => $reflectionParameter) {
            $parameterType = $reflectionParameter->getType();
            if (! $parameterType instanceof ReflectionNamedType) {
                throw new InvalidCommandException(sprintf(
                    'Parameter "%s" of command "%s" must have explicit type declaration',
                    $reflectionParameter->getName(),
                    $command->getName()
                ));
            }

            $parameterName = $reflectionParameter->getName();
            $parameterType = $parameterType->getName();

            $description = $paramDescriptions[$parameterName] ?? null;

            // 1st param is argument by convention
            $acceptsMultipleValue = $parameterType === 'array';

            $defaultValue = null;
            if ($reflectionParameter->isDefaultValueAvailable()) {
                $defaultValue = $reflectionParameter->getDefaultValue();

                // array default is not a scalar option value, join it or drop when empty
                if (is_array($defaultValue)) {
                    $defaultValue = $defaultValue === [] ? null : implode(', ', $defaultValue);
                }
            }

            // first param can be an arg by convention, only "string" and "array" are allowed types,
            // unless explicitly marked as an option via "@option $paramName" in the docblock
            $isExplicitOption = isset($optionMarkers[$parameterName]);

            if ($key === 0 && ! $isExplicitOption && in_array($parameterType, ['string', 'array'], true)) {
                $arguments[] = new Argument($parameterName, $description, $acceptsMultipleValue);
            } else {
                // correct plural argumen to singular --option name
                if ($acceptsMultipleValue && str_ends_with($parameterName, 's')) {
                    $parameterName = substr($parameterName, 0, -1);
                }

                $options[] = new Option(
                    $parameterName,
                    $parameterType,
                    $description,
                    $acceptsMultipleValue,
                    $defaultValue
                );
            }
        }

        return new ArgumentsAndOptions($arguments, $options);
    }
}
