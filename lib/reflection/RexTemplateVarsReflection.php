<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexStan;

use rex_string;

use function array_key_exists;
use function count;

final class RexTemplateVarsReflection
{
    /**
     * @return non-empty-list<array{string, array<string, scalar>}>|null
     */
    public static function matchVars(string $template): ?array
    {
        if (preg_match_all('{(?P<var>REX_TEMPLATE)\[(?P<args>[^\]]+)\]}', $template, $matches) !== false) {
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

            if ($result !== []) {
                return $result;
            }
        }

        return null;
    }
}
