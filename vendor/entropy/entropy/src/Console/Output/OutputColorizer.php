<?php

declare(strict_types=1);

namespace Entropy\Console\Output;

use Entropy\Attribute\RelatedTest;
use Entropy\Console\Enum\Color;
use Entropy\Tests\Console\Output\OutputColozierTest;

#[RelatedTest(OutputColozierTest::class)]
final class OutputColorizer
{
    private bool $useColors;

    public function __construct()
    {
        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            // enable colors during unit tests
            $this->useColors = true;
            return;
        }

        $this->useColors = $this->isTty();
    }

    /**
     * @api used in tests
     */
    public function colorize(string $text): string
    {
        // foreground colors: <fg=green>text</>
        if (preg_match_all('#<fg=(green|yellow|red|cyan)>(.*?)</>#su', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $text = str_replace($match[0], $this->color($match[2], $match[1]), $text);
            }
        }

        // background colors: <bg=green>text</>
        if (preg_match_all('#<bg=(green|yellow|red|cyan)>(.*?)</>#su', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $content = $match[2];
                $color = $match[1];

                $text = str_replace($match[0], $this->background($content, $color), $text);
            }
        }

        // underscore: <options=underscore>text</>
        if (preg_match_all('#<options=underscore>(.*?)</>#su', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $text = str_replace($match[0], $this->underscore($match[1]), $text);
            }
        }

        // bold: <options=bold>text</>
        if (preg_match_all('#<options=bold>(.*?)</>#su', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $text = str_replace($match[0], $this->bold($match[1]), $text);
            }
        }

        return $text;
    }

    /**
     * @api used in tests
     */
    public function underscore(string $text): string
    {
        if (! $this->useColors) {
            return $text;
        }

        return "\033[4m" . $text . "\033[0m";
    }

    /**
     * @api used in tests
     */
    public function bold(string $text): string
    {
        if (! $this->useColors) {
            return $text;
        }

        return "\033[1m" . $text . "\033[0m";
    }

    /**
     * @param Color::* $color
     */
    public function color(string $text, string $color): string
    {
        if (! $this->useColors) {
            return $text;
        }

        if ($color === Color::GREEN) {
            return "\033[32m" . $text . "\033[0m";
        }

        if ($color === Color::YELLOW) {
            return "\033[33m" . $text . "\033[0m";
        }

        if ($color === Color::RED) {
            return "\033[31m" . $text . "\033[0m";
        }

        if ($color === Color::CYAN) {
            return "\033[36m" . $text . "\033[0m";
        }

        if ($color === Color::GREY) {
            // bright black, readable on light and dark backgrounds
            return "\033[90m" . $text . "\033[0m";
        }

        throw new \RuntimeException('Unhandled color value');
    }

    /**
     * @param Color::* $color
     */
    public function background(string $text, string $color): string
    {
        $text = $this->padding($text);

        if (! $this->useColors) {
            return $text;
        }

        if ($color === Color::GREEN) {
            // background ; foreground
            return "\033[42;30m" . $text . "\033[0m";
        }

        if ($color === Color::YELLOW || $color === 'orange') {
            return "\033[43;30m" . $text . "\033[0m";
        }

        if ($color === Color::RED) {
            // WHITE on red (important)
            return "\033[41;30m" . $text . "\033[0m";
        }

        if ($color === Color::CYAN) {
            return "\033[46;30m" . $text . "\033[0m";
        }

        throw new \RuntimeException('Unhandled color value');
    }

    private function padding(string $text): string
    {
        return ' ' . $text . ' ';
    }

    private function isTty(): bool
    {
        if (function_exists('stream_isatty') && defined('STDOUT')) {
            return stream_isatty(STDOUT);
        }

        // Fallback: respect NO_COLOR if present
        return getenv('NO_COLOR') === false;
    }
}
