<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Reset;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dal\DalCommand;
use SqlFtw\Sql\StatementImpl;

class ResetCommand extends StatementImpl implements DalCommand
{

    /** @var non-empty-list<ResetOption> */
    private array $options;

    /**
     * @param non-empty-list<ResetOption> $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return non-empty-list<ResetOption>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'RESET ' . $formatter->formatSerializablesList($this->options);
    }

}
