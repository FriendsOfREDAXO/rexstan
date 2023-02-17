<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Web;

use Dogma\Check;
use Dogma\Str;
use Dogma\StrictBehaviorMixin;
use function end;
use function explode;

class Host
{
    use StrictBehaviorMixin;

    /** @var string */
    private $host;

    /** @var int|null */
    private $port;

    public function __construct(string $host, ?int $port = null)
    {
        if ($port === null && Str::contains($host, ':')) {
            [$host, $port] = Str::splitByFirst($host, ':');
        }
        Check::nullableInt($port, 0, 65536);

        $this->host = $host;
        $this->port = $port;
    }

    public function format(): string
    {
        return $this->port === null ? $this->host : $this->host . ':' . $this->port;
    }

    public function getTld(): Tld
    {
        $parts = explode('.', $this->host);
        /** @var string $tld */
        $tld = end($parts);

        return Tld::get($tld);
    }

}
