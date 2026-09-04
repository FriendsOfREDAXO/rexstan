<?php

namespace FriendsOfRedaxo\RexStan;

use rex_addon;
use rex_file;

/**
 * Persists the state of a background "analyze" run (started from the web UI,
 * see pages/analysis.php + Api\AnalysisApi) to plain files, so a detached
 * background process and the browser polling for its result always agree on
 * the same ground truth - without this, showing analysis results would mean
 * blocking the whole page request on however long the actual PHPStan run
 * takes, which can be minutes on a larger codebase.
 */
final class RexStanRunStore
{
    private const LOCK_FILE = 'web-analysis-running.lock';
    private const RESULT_FILE = 'web-analysis-result.json';
    private const RESULT_TMP_FILE = 'web-analysis-result.json.tmp';
    private const ERROR_LOG_FILE = 'web-analysis-error.log';

    // A lock older than this is treated as an orphaned/crashed run rather than
    // an active one, so a new run can always be started instead of getting
    // stuck forever behind a background process that died without cleaning
    // up after itself (e.g. killed by a deploy, OOM, hosting time limit).
    private const STALE_AFTER_SECONDS = 900;

    public static function isRunning(): bool
    {
        $path = self::lockPath();
        if (!is_file($path)) {
            return false;
        }

        $startedAt = (int) rex_file::get($path, '0');
        if ($startedAt > 0 && (time() - $startedAt) > self::STALE_AFTER_SECONDS) {
            return false;
        }

        return true;
    }

    public static function markStarted(): void
    {
        rex_file::put(self::lockPath(), (string) time());
    }

    /**
     * Reads the last completed background-run result.
     *
     * @return array<string, mixed>|string|null null if no cached result exists yet;
     *                                           a string if PHPStan's output could not
     *                                           be parsed as JSON (mirrors RexStan::runFromWeb())
     */
    public static function readCachedResult()
    {
        $path = self::resultPath();
        if (!is_file($path)) {
            return null;
        }

        $content = rex_file::get($path, '');
        if ('' === $content) {
            return null;
        }

        return RexStan::interpretAnalysisOutput($content, '');
    }

    public static function readErrorLog(): string
    {
        return rex_file::get(self::errorLogPath(), '');
    }

    /**
     * @return int|null unix timestamp the cached result was generated at, null if there is none
     */
    public static function getCachedResultTimestamp(): ?int
    {
        $path = self::resultPath();
        if (!is_file($path)) {
            return null;
        }

        $mtime = filemtime($path);

        return false !== $mtime ? $mtime : null;
    }

    public static function clearCachedResult(): void
    {
        @unlink(self::resultPath());
        @unlink(self::errorLogPath());
    }

    public static function lockPath(): string
    {
        return self::dataPath(self::LOCK_FILE);
    }

    public static function resultPath(): string
    {
        return self::dataPath(self::RESULT_FILE);
    }

    public static function resultTmpPath(): string
    {
        return self::dataPath(self::RESULT_TMP_FILE);
    }

    public static function errorLogPath(): string
    {
        return self::dataPath(self::ERROR_LOG_FILE);
    }

    private static function dataPath(string $file): string
    {
        return rex_addon::get('rexstan')->getDataPath($file);
    }
}
