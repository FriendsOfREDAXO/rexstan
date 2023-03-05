<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping;

use Dogma\Mapping\Type\TypeHandler;
use Dogma\ReverseArrayIterator;
use Dogma\StrictBehaviorMixin;
use Dogma\Type;

class Mapping
{
    use StrictBehaviorMixin;

    /** @var Type */
    private $type;

    /** @var MappingStep[] */
    private $steps;

    /** @var MappingStep[]|ReverseArrayIterator */
    private $reverseSteps;

    /**
     * @param mixed[] $steps
     */
    public function __construct(Type $type, array $steps)
    {
        $this->type = $type;
        $this->steps = $steps;

        $this->reverseSteps = new ReverseArrayIterator($steps);
    }

    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return MappingStep[]
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * @param mixed[] $data
     * @return mixed
     */
    public function mapForward(array $data, Mapper $mapper)
    {
        foreach ($this->steps as $step) {
            $step->stepForward($data, $mapper);
        }
        return $data[TypeHandler::SINGLE_PARAMETER];
    }

    /**
     * @param mixed $instance
     * @return mixed[]
     */
    public function mapBack($instance, Mapper $mapper): array
    {
        $data = [TypeHandler::SINGLE_PARAMETER => $instance];
        foreach ($this->reverseSteps as $step) {
            $step->stepBack($data, $mapper);
        }
        return $data;
    }

}
