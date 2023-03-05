<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

/**
 * Urban dictionary: pokeable
 *
 * Simply that which is considered worthy of being poked.
 *
 * "Sure, that Hilton is dumb as a post, but she's still pokeable."
 *
 * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
 */
interface Pokeable
{

    /**
     * Fill internal caches of an object prior to being dumped.
     */
    public function poke(): void;

}
