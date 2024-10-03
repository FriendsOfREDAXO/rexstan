<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Plugin;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\StatementImpl;

class UninstallPluginCommand extends StatementImpl implements PluginCommand
{

    private string $pluginName;

    public function __construct(string $pluginName)
    {
        $this->pluginName = $pluginName;
    }

    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'UNINSTALL PLUGIN ' . $formatter->formatName($this->pluginName);
    }

}
