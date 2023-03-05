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
use Dogma\StrictBehaviorMixin;
use Dogma\Type;
use function array_flip;

class MappingStep
{
    use StrictBehaviorMixin;

    /** @var Type */
    private $type;

    /** @var TypeHandler */
    private $handler;

    /** @var string[] */
    private $handlerKeys;

    /** @var string[] */
    private $sourceKeys;

    /** @var string */
    private $destinationKey;

    /**
     * @param string[] $handlerKeys ($sourceKey => $handlerKey)
     */
    public function __construct(Type $type, TypeHandler $handler, array $handlerKeys, string $destinationKey)
    {
        $this->type = $type;
        $this->handler = $handler;
        $this->handlerKeys = $handlerKeys;
        $this->destinationKey = $destinationKey;

        $this->sourceKeys = array_flip($handlerKeys);
    }

    /**
     * Does a forward transformation step on the data
     * @param mixed[] $data
     */
    public function stepForward(array &$data, Mapper $mapper): void
    {
        $sourceData = [];
        $onlyNull = true;
        foreach ($this->handlerKeys as $sourceKey => $handlerKey) {
            if ($handlerKey === TypeHandler::SINGLE_PARAMETER) {
                $sourceData = $data[$sourceKey];
                $onlyNull = false;
            } else {
                $sourceData[$handlerKey] = $data[$sourceKey];
                $onlyNull &= $data[$sourceKey] === null;
            }
            unset($data[$sourceKey]);
        }
        // skip null
        if ($onlyNull) {
            $data[$this->destinationKey] = null;
            return;
        }
        $data[$this->destinationKey] = $this->handler->createInstance($this->type, $sourceData, $mapper);
    }

    /**
     * Does a backward transformation step on the data
     * @param mixed[] $data
     */
    public function stepBack(array &$data, Mapper $mapper): void
    {
        $instance = $data[$this->destinationKey];
        unset($data[$this->destinationKey]);
        if ($instance === null) {
            // expand null
            foreach ($this->sourceKeys as $sourceKey) {
                $data[$sourceKey] = null;
            }
            return;
        }
        $sourceData = $this->handler->exportInstance($this->type, $instance, $mapper);
        if (isset($this->sourceKeys[TypeHandler::SINGLE_PARAMETER])) {
            $data[$this->sourceKeys[TypeHandler::SINGLE_PARAMETER]] = $sourceData;
        } else {
            foreach ($sourceData as $handlerKey => $value) {
                $data[$this->sourceKeys[$handlerKey]] = $value;
            }
        }
    }

}
