<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type;

/**
 * Interface converting value objects to scalars.
 */
interface Exportable
{

    /**
     * @return mixed|mixed[] Scalar value or map of scalar values
     */
    public function export();

}
