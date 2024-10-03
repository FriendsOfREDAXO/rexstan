<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Table;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\StatementImpl;

class ChecksumTableCommand extends StatementImpl implements DalTablesCommand
{

    /** @var non-empty-list<ObjectIdentifier> */
    private array $names;

    private bool $quick;

    private bool $extended;

    /**
     * @param non-empty-list<ObjectIdentifier> $names
     */
    public function __construct(array $names, bool $quick, bool $extended)
    {
        $this->names = $names;
        $this->quick = $quick;
        $this->extended = $extended;
    }

    /**
     * @return non-empty-list<ObjectIdentifier>
     */
    public function getTables(): array
    {
        return $this->names;
    }

    public function isQuick(): bool
    {
        return $this->quick;
    }

    public function isExtended(): bool
    {
        return $this->extended;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'CHECKSUM TABLE ' . $formatter->formatSerializablesList($this->names);

        if ($this->quick) {
            $result .= ' QUICK';
        }
        if ($this->extended) {
            $result .= ' EXTENDED';
        }

        return $result;
    }

}
