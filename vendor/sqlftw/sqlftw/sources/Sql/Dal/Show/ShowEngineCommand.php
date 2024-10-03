<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Show;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\Table\Option\StorageEngine;
use SqlFtw\Sql\StatementImpl;

class ShowEngineCommand extends StatementImpl implements ShowCommand
{

    private StorageEngine $engine;

    private ShowEngineOption $option;

    public function __construct(StorageEngine $engine, ShowEngineOption $option)
    {
        $this->engine = $engine;
        $this->option = $option;
    }

    public function getEngine(): StorageEngine
    {
        return $this->engine;
    }

    public function getOption(): ShowEngineOption
    {
        return $this->option;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'SHOW ENGINE ' . $this->engine->serialize($formatter) . ' ' . $this->option->serialize($formatter);
    }

}
