<?php

declare(strict_types=1);

namespace rexstan;

use rex_string;

use function array_key_exists;
use function count;

final class RexTemplateVarsReflection
{
    /**
     * @return list<array{string, array<string, scalar>}>
     */
    public static function matchVars(string $template): array
    {
        if (false !== preg_match_all('{(?P<var>REX_TEMPLATE)\[(?P<args>[^\]]+)\]}', $template, $matches)) {
            $result = [];
            for ($i = 0, $len = count($matches['var']); $i < $len; ++$i) {
                $args = rex_string::split($matches['args'][$i]);

                if (1 === count($args) && array_key_exists(0, $args)) {
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
