<?php

namespace rexstan;

use rex_url;

final class RexStanTip
{
    private const FAQ_ANCHORS = [
        '{with generic class rex_extension_point but does not specify its types}' => 'wie-mit-dem-fehler-parameter-ep-with-generic-class-rex-extension-point-but-does-not-specify-its-types-t-umgehen',
        '{Instantiated class .* not found}' => 'wie-mit-dem-fehler-instantiated-class-x-not-found-umgehen',
        '{Function .* not found}' => 'wie-mit-dem-fehler-function-x-not-found-umgehen',
        '{unknown class}' => 'wie-mit-dem-fehler-instantiated-class-x-not-found-umgehen',
        '{Call to an undefined method}' => 'wie-mit-dem-fehler-call-to-an-undefined-method-cc-mm-umgehen',
        '{Loose comparison via}' => 'wie-mit-dem-fehler-loose-comparison-via-is-not-allowed-umgehen',
        '{Variable \$this might not be defined.}' => 'wie-mit-dem-fehler-variable-this-might-not-be-defined-umgehen',
    ];

    public static function renderTip(string $message, ?string $tip): ?string
    {
        $faqUrl = rex_url::backendPage('rexstan/faq');

        foreach (self::FAQ_ANCHORS as $messagePattern => $anchor) {
            if (preg_match($messagePattern, $message) === 1) {
                return 'See: <a href="'. $faqUrl .'#'. $anchor .'">FAQ</a>';
            }
        }

        if ($tip !== null) {
            return self::escapeButPreserveUris($tip);
        }

        return null;
    }

    /**
     * Escapes a string for output in an HTML document, but preserves
     * URIs within it, and converts them to clickable anchor elements.
     *
     * @param  string $raw
     * @return string
     */
    private static function escapeButPreserveUris($raw)
    {
        $escaped = rex_escape($raw);
        return (string) preg_replace(
            "@([A-z]+?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@",
            '<a href="$1" target="_blank" rel="noreferrer noopener">$1</a>',
            $escaped
        );
    }
}
