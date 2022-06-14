<?php

final class RexStanUserConfig {
    /**
     * @param int   $level
     * @param array<string> $paths
     * @param array<string> $includes
     *
     * @return void
     */
    static public function save(int $level, array $paths, array $includes) {
        $file = [];
        $file['includes'] = $includes;
        $file['parameters']['level'] = $level;
        $file['parameters']['paths'] = $paths;

        $prefix = "# rexstan auto generated file - do not edit\n\n";

        rex_file::put(self::userconfig(), $prefix . rex_string::yamlEncode($file, 3));
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

    /**
     * @return array<string>
     */
    static public function getIncludes(): array {
        $neon = rex_file::get(self::userconfig());
        $settings = rex_string::yamlDecode($neon);
        return $settings['includes'];
    }

    static private function userconfig():string {
        return rex_addon::get('rexstan')->getDataPath('user-config.neon');
    }
}
