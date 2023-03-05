<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type;

use Dogma\Exception;
use Throwable;
use function get_class;

class OneWayHandlerException extends Exception implements MappingTypeException
{

    /** @var mixed */
    private $instance;

    /** @var TypeHandler */
    private $handler;

    /**
     * @param mixed $instance
     */
    public function __construct($instance, TypeHandler $handler, ?Throwable $previous = null)
    {
        $class = get_class($instance);
        $handlerClass = get_class($handler);

        parent::__construct("Cannot export an instance of $class using one way type handler $handlerClass.", $previous);

        $this->instance = $instance;
        $this->handler = $handler;
    }

    /**
     * @return mixed
     */
    public function getInstance()
    {
        return $this->instance;
    }

    public function getHandler(): TypeHandler
    {
        return $this->handler;
    }

}
