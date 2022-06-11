<?php

class RexStanUserConfig {
    /**
     * @param int   $level
     * @param array<string> $paths
     */
    static public function save(int $level, array $paths) {
        $file = [];
        $file['parameters']['level'] = $level;
        $file['parameters']['paths'] = $paths;

        rex_file::put(rex_addon::get('rexstan')->getDataPath('user-config.neon'), rex_string::yamlEncode($file, 3));
    }
}
