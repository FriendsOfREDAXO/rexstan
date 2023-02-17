<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Instance;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\DdlCommand;
use SqlFtw\Sql\Statement;

class AlterInstanceCommand extends Statement implements DdlCommand
{

    private AlterInstanceAction $action;

    private ?string $forChannel;

    private bool $noRollbackOnError;

    public function __construct(
        AlterInstanceAction $action,
        ?string $forChannel = null,
        bool $noRollbackOnError = false
    )
    {
        $this->action = $action;
        $this->forChannel = $forChannel;
        $this->noRollbackOnError = $noRollbackOnError;
    }

    public function getAction(): AlterInstanceAction
    {
        return $this->action;
    }

    public function getForChannel(): ?string
    {
        return $this->forChannel;
    }

    public function noRollbackOnError(): bool
    {
        return $this->noRollbackOnError;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'ALTER INSTANCE ' . $this->action->serialize($formatter);
        if ($this->forChannel !== null) {
            $result .= ' FOR CHANNEL ' . $this->forChannel;
        }
        if ($this->noRollbackOnError) {
            $result .= ' NO ROLLBACK ON ERROR';
        }

        return $result;
    }

}
