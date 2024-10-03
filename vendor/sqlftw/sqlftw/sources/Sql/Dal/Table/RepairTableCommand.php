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

class RepairTableCommand extends StatementImpl implements DalTablesCommand
{

    /** @var non-empty-list<ObjectIdentifier> */
    private array $names;

    private bool $local;

    private bool $quick;

    private bool $extended;

    private bool $useFrm;

    /**
     * @param non-empty-list<ObjectIdentifier> $names
     */
    public function __construct(
        array $names,
        bool $local = false,
        bool $quick = false,
        bool $extended = false,
        bool $useFrm = false
    ) {
        $this->names = $names;
        $this->local = $local;
        $this->quick = $quick;
        $this->extended = $extended;
        $this->useFrm = $useFrm;
    }

    /**
     * @return non-empty-list<ObjectIdentifier>
     */
    public function getTables(): array
    {
        return $this->names;
    }

    public function isLocal(): bool
    {
        return $this->local;
    }

    public function isQuick(): bool
    {
        return $this->quick;
    }

    public function isExtended(): bool
    {
        return $this->extended;
    }

    public function useFrm(): bool
    {
        return $this->useFrm;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'REPAIR';
        if ($this->local) {
            $result .= ' LOCAL';
        }
        $result .= ' TABLE ' . $formatter->formatSerializablesList($this->names);

        if ($this->quick) {
            $result .= ' QUICK';
        }
        if ($this->extended) {
            $result .= ' EXTENDED';
        }
        if ($this->useFrm) {
            $result .= ' USE_FRM';
        }

        return $result;
    }

}
