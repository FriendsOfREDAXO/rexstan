<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dal;

use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Dal\Plugin\InstallPluginCommand;
use SqlFtw\Sql\Dal\Plugin\UninstallPluginCommand;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Keyword;

class PluginCommandsParser
{

    /**
     * INSTALL PLUGIN plugin_name SONAME 'shared_library_name'
     */
    public function parseInstallPlugin(TokenList $tokenList): InstallPluginCommand
    {
        $tokenList->expectKeywords(Keyword::INSTALL, Keyword::PLUGIN);
        $pluginName = $tokenList->expectName(EntityType::PLUGIN);
        $tokenList->expectKeyword(Keyword::SONAME);
        $libName = $tokenList->expectString();

        return new InstallPluginCommand($pluginName, $libName);
    }

    /**
     * UNINSTALL PLUGIN plugin_name
     */
    public function parseUninstallPlugin(TokenList $tokenList): UninstallPluginCommand
    {
        $tokenList->expectKeywords(Keyword::UNINSTALL, Keyword::PLUGIN);
        $pluginName = $tokenList->expectName(EntityType::PLUGIN);

        return new UninstallPluginCommand($pluginName);
    }

}
