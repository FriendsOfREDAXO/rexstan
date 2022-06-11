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

        rex_file::put(self::userconfig(), rex_string::yamlEncode($file, 3));
    }

    static public function getLevel(): int {
        $neon = rex_file::get(self::userconfig());
        $settings = rex_string::yamlDecode($neon);
        return (int) $settings['parameters']['level'];
    }

    /**
     * @return array<string>
     */
    static public function getPaths(): array {
        $neon = rex_file::get(self::userconfig());
        $settings = rex_string::yamlDecode($neon);
        return $settings['parameters']['paths'];
    }

    static private function userconfig():string {
        return rex_addon::get('rexstan')->getDataPath('user-config.neon');
    }
}
