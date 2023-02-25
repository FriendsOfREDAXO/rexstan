<?php

namespace rexstan;

use rex_developer_manager;

final class DeveloperAddonIntegration {
    static public function getModulesDir(): ?string {
        if (class_exists(rex_developer_manager::class)) {
            $path = rex_developer_manager::getBasePath() .'modules/';
            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
    }

    static public function getTemplatesDir(): ?string {
        if (class_exists(rex_developer_manager::class)) {
            $path = rex_developer_manager::getBasePath() .'templates/';
            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
    }
}
