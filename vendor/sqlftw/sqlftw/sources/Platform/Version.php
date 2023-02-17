<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Platform;

use function explode;
use function floor;
use function is_int;

class Version
{

    private int $major;

    private int $minor;

    private int $patch;

    /**
     * @param string|int $version
     */
    public function __construct($version)
    {
        if (is_int($version)) {
            $this->major = (int) floor($version / 10000);
            $this->minor = (int) floor(($version % 10000) / 100);
            $this->patch = ($version % 100) ?: 99; // @phpstan-ignore-line ?:
        } else {
            $parts = explode('.', $version);
            $this->major = (int) $parts[0];
            $this->minor = isset($parts[1]) ? (int) $parts[1] : 99;
            $this->patch = isset($parts[2]) ? (int) $parts[2] : 99;
        }
    }

    public function getId(): int
    {
        if ($this->major > 90) {
            return $this->major;
        } else {
            return $this->major * 10000 + ($this->minor) * 100 + ($this->patch);
        }
    }

    public function getMajor(): int
    {
        return $this->major;
    }

    public function getMinor(): int
    {
        return $this->minor;
    }

    public function getPatch(): int
    {
        return $this->patch;
    }

    public function getMajorMinor(): string
    {
        return $this->major . '.' . $this->minor;
    }

    public function format(): string
    {
        return $this->major . '.' . $this->minor . '.' . $this->patch;
    }

}
