<?php

declare(strict_types=1);

namespace rexstan;

use rex_string;

final class RexTemplateVarsReflection {
    /**
     * @param string $template
     * @return list<array{string, array<string, scalar>}>
     */
    static public function matchVars(string $template): array {
        if(preg_match_all('{(?P<var>REX_TEMPLATE)\[(?P<args>[^\]]+)\]}', $template, $matches) !== false) {
            $result = [];
            for ($i = 0, $len = count($matches['var']); $i < $len; ++$i) {
                $args = rex_string::split($matches['args'][$i]);

                if (count($args) === 1 && array_key_exists(0, $args)) {
                    $args = ['id' => $args[0]]; // default arg is "id"
                }

                $result[] = [
                    $matches['var'][$i],
                    $args,
                ];
            }

            return $result;
        }

        return [];
    }

}
