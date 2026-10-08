<?php

namespace FriendsOfRedaxo\RexStan\Api;

use FriendsOfRedaxo\RexStan\RexResultsRenderer;
use FriendsOfRedaxo\RexStan\RexStan;
use FriendsOfRedaxo\RexStan\RexStanRunStore;
use rex;
use rex_api_exception;
use rex_api_function;
use rex_request;

/**
 * Ajax endpoint backing the non-blocking analysis page (pages/analysis.php +
 * assets/rexstan-analysis.js): starting a PHPStan run and checking on its
 * result must not themselves block on however long the run takes, otherwise
 * we'd be back to the original blocking-page problem, just moved into an
 * XHR request instead of the page load itself.
 *
 * Registered in boot.php as "rexstan_analysis". Called as
 * "index.php?rex-api-call=rexstan_analysis&action=start|status" - no "page"
 * parameter is required, the permission check happens explicitly below
 * (mirrors other backend-only rex_api_function endpoints in this ecosystem).
 */
class AnalysisApi extends rex_api_function
{
    use JsonResponseTrait;

    protected $published = false;

    public function execute()
    {
        $user = rex::getUser();
        if (!$user || !$user->isAdmin()) {
            throw new rex_api_exception('Unauthorized');
        }

        // A background run doesn't need this request's session locked, and
        // holding it would block every other backend tab/request until this
        // one returns.
        if (session_id()) {
            session_write_close();
        }

        ob_start();

        $action = rex_request('action', 'string', '');

        if ('start' === $action) {
            $this->handleStart();
        }

        if ('status' === $action) {
            $this->handleStatus();
        }

        $this->sendJsonClean(['success' => false, 'error' => 'Unknown action']);
    }

    private function handleStart(): void
    {
        if (RexStanRunStore::isRunning()) {
            $this->sendJsonClean(['success' => true, 'started' => false, 'reason' => 'already_running']);
        }

        $started = RexStan::startBackgroundWebAnalysis();
        if (!$started) {
            $this->sendJsonClean(['success' => true, 'started' => false, 'reason' => 'already_running']);
        }

        $this->sendJsonClean(['success' => true, 'started' => true]);
    }

    private function handleStatus(): void
    {
        if (RexStanRunStore::isRunning()) {
            $this->sendJsonClean(['success' => true, 'running' => true]);
        }

        $result = RexStanRunStore::readCachedResult();
        if (null === $result) {
            // finished, but no result made it into place - most likely the
            // background process died before it could rename its result file
            // into place; surface whatever ended up in its error log instead
            // of leaving the polling UI stuck forever.
            $errorLog = RexStanRunStore::readErrorLog();
            $result = '' !== $errorLog ? $errorLog : 'Unbekannter Fehler: der Hintergrundlauf wurde beendet, ohne ein Ergebnis zu hinterlassen.';
        }

        $this->sendJsonClean([
            'success' => true,
            'running' => false,
            'html' => RexResultsRenderer::renderAnalysisBody($result),
        ]);
    }
}
