<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type;

use Dogma\StrictBehaviorMixin;
use Dogma\Type;

/**
 * Extracts raw data from instance via Exportable interface
 */
class ExportableHandler extends ConstructorHandler
{
    use StrictBehaviorMixin;
    use ExportableHandlerMixin;

    public function acceptsType(Type $type): bool
    {
        return $type->isImplementing(Exportable::class);
    }

}
