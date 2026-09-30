<?php

namespace FriendsOfRedaxo\RexStan\Api;

use rex_response;

/**
 * A single PHP warning/notice that REDAXO's error handler writes directly into
 * the output in debug mode would otherwise land BEFORE the JSON in the
 * response body and make it invalid for any JSON parser - visible client-side
 * often only as a cryptic network/parse error instead of a clear message.
 *
 * Usage: call ob_start() at the beginning of execute() (catches stray output),
 * then use sendJsonClean() instead of rex_response::sendJson() directly before
 * every response.
 */
trait JsonResponseTrait
{
    /**
     * @param array<string, mixed> $data
     */
    private function sendJsonClean(array $data): never
    {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        rex_response::sendJson($data);
        exit;
    }
}
