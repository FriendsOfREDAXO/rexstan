<?php

namespace rexstan;

use rex_developer_manager;

final class DeveloperAddonIntegration {
    static public function getModulesDir(): ?string {
        if (class_exists(rex_developer_manager::class)) {
            return rex_developer_manager::getBasePath() .'modules/';
        }

        return null;
    }

    static public function getTemplatesDir(): ?string {
        if (class_exists(rex_developer_manager::class)) {
            return rex_developer_manager::getBasePath() .'templates/';
        }

        return null;
    }
}
