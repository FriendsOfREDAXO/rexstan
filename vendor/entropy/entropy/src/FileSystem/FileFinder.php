<?php

declare(strict_types=1);

namespace Entropy\FileSystem;

use Entropy\Attribute\RelatedTest;
use Entropy\Tests\FileSystem\FileFinder\FileFinderTest;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

#[RelatedTest(FileFinderTest::class)]
final class FileFinder
{
    /**
     * Generic recursive finder. Returns files across all directories, sorted by path,
     * each aware of its path relative to the directory it was found in.
     *
     * @api
     * @param string[] $directories
     * @param (callable(FileInfo): bool)|null $filter
     * @return FileInfo[]
     */
    public static function find(array $directories, ?callable $filter = null): array
    {
        $fileInfos = [];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            $baseDirectory = str_replace('\\', '/', (string) realpath($directory));

            $recursiveDirectoryIterator = new RecursiveDirectoryIterator(
                $directory,
                RecursiveDirectoryIterator::SKIP_DOTS
            );
            $recursiveIteratorIterator = new RecursiveIteratorIterator($recursiveDirectoryIterator);

            foreach ($recursiveIteratorIterator as $splFileInfo) {
                if (! $splFileInfo instanceof SplFileInfo) {
                    continue;
                }

                if (! $splFileInfo->isFile()) {
                    continue;
                }

                $filePath = (string) $splFileInfo->getRealPath();
                $relativePathname = self::resolveRelativePathname($filePath, $baseDirectory);
                $relativeDirectory = dirname($relativePathname);

                $fileInfo = new FileInfo(
                    $filePath,
                    $relativeDirectory === '.' ? '' : $relativeDirectory,
                    $relativePathname
                );

                if ($filter !== null && ! $filter($fileInfo)) {
                    continue;
                }

                $fileInfos[$filePath] = $fileInfo;
            }
        }

        ksort($fileInfos);

        return array_values($fileInfos);
    }

    /**
     * @api used in tests
     * @return string[]
     */
    public static function findPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $fileInfo) {
            if (! $fileInfo->isFile()) {
                continue;
            }

            if ($fileInfo->getExtension() !== 'php') {
                continue;
            }

            if (self::isNonService($fileInfo)) {
                continue;
            }

            $files[] = $fileInfo->getPathname();
        }

        return $files;
    }

    private static function resolveRelativePathname(string $filePath, string $baseDirectory): string
    {
        $filePath = str_replace('\\', '/', $filePath);

        if ($baseDirectory !== '' && str_starts_with($filePath, $baseDirectory . '/')) {
            return substr($filePath, strlen($baseDirectory) + 1);
        }

        return $filePath;
    }

    private static function isNonService(SplFileInfo $fileInfo): bool
    {
        if (str_contains($fileInfo->getPathname(), '/ValueObject/')) {
            return true;
        }

        return str_contains($fileInfo->getPathname(), '/Enum/');
    }
}
