<?php

namespace FriendsOfRedaxo\RexStan;

use rex_developer_manager;

final class DeveloperAddonIntegration
{
    public static function getModulesDir(): ?string
    {
        if (class_exists(rex_developer_manager::class)) {
            $path = rex_developer_manager::getBasePath() .'modules/';
            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
    }

    public static function getTemplatesDir(): ?string
    {
        if (class_exists(rex_developer_manager::class)) {
            $path = rex_developer_manager::getBasePath() .'templates/';
            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
    }
}
