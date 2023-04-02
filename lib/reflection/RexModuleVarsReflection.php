<?php

declare(strict_types=1);

namespace rexstan;

use rex_string;

use function array_key_exists;
use function count;

final class RexModuleVarsReflection
{
    /**
     * @return list<array{string, array<string, scalar>}>
     */
    public static function matchInputVars(string $input): array
    {
        if (false !== preg_match_all('{(?P<var>REX_INPUT_VALUE|REX_VALUE|REX_LINK|REX_LINKLIST|REX_MEDIA|REX_MEDIALIST)\[(?P<args>[^\]]+)\]}', $input, $matches)) {
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

    /**
     * @return list<array{string, array<string, scalar>}>
     */
    public static function matchOutputVars(string $output): array
    {
        if (false !== preg_match_all('{(?P<var>REX_VALUE|REX_LINK|REX_LINKLIST|REX_MEDIA|REX_MEDIALIST)\[(?P<args>[^\]]+)\]}', $output, $matches)) {
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
