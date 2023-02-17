<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Dom;

use Dogma\NotImplementedException;
use Dogma\StaticClassMixin;
use DOMCdataSection;
use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use function count;
use function htmlspecialchars;
use function preg_replace;
use function str_repeat;
use function trim;

class Dumper
{
    use StaticClassMixin;

    /**
     * @param Element|NodeList|DOMNode $node
     */
    public static function dump($node, int $maxDepth = 15, int $depth = 0, bool $onlyChild = false): void
    {
        if ($depth > $maxDepth) {
            echo '…';
        }
        if ($depth === 0) {
            echo '<pre><code>';
        }

        if ($node instanceof Element || $node instanceof DOMElement) {
            self::dumpElement($node, $maxDepth, $depth, $onlyChild);

        } elseif ($node instanceof DOMDocument) {
            if ($depth === 0) {
                echo "<b>Document:</b>\n";
            }
            /** @var DOMDocument $element */
            $element = $node->documentElement;

            self::dump($element, $maxDepth);

        } elseif ($node instanceof DOMCdataSection) {
            if ($depth === 0) {
                echo "<b>CdataSection:</b>\n";
            }
            echo '<i style="color: purple">', htmlspecialchars(trim($node->data)), '</i>';

        } elseif ($node instanceof DOMComment) {
            if ($depth === 0) {
                echo "<b>Comment:</b>\n";
            }
            echo '<i style="color: gray">&lt;!-- ', trim($node->data), " --&gt;</i>\n";

        } elseif ($node instanceof DOMText) {
            if ($depth === 0) {
                echo "<b>Text:</b>\n";
            }
            $string = preg_replace('/[ \\t]+/', ' ', trim($node->wholeText));
            echo '<i>', $string, '</i>';

        } elseif ($node instanceof NodeList) {
            echo '<b>NodeList (', count($node), ")</b>\n";
            foreach ($node as $item) {
                echo '<hr style="border: silver solid; border-width: 1px 0 0 0">';
                echo '    ';
                self::dump($item, $maxDepth, $depth + 1, true);
            }
        } else {
            echo '[something]';
            throw new NotImplementedException('Dom dumper found some strange thing.');
        }

        if ($depth === 0) {
            echo '<code></pre>';
        }
    }

    /**
     * @param Element|DOMNode $node
     */
    private static function dumpElement($node, int $maxDepth = 15, int $depth = 0, bool $onlyChild = false): void
    {
        if ($depth === 0) {
            echo "<b>Element:</b>\n";
        }
        if (!$onlyChild) {
            echo str_repeat('    ', $depth);
        }
        echo '<b>&lt;</b><b style="color: red">', $node->nodeName, '</b>';

        foreach ($node->attributes ?? [] as $attribute) {
            echo ' <span style="color: green">', $attribute->name, '</span>=<span style="color: blue">"', $attribute->value, '"</span>';
        }

        echo '<b>&gt;</b>';

        if ($node->childNodes->length > 1) {
            echo "\n";
        }
        foreach ($node->childNodes as $child) {
            self::dump($child, $maxDepth, $depth + 1, $node->childNodes->length === 1);
        }
        if ($node->childNodes->length > 1) {
            echo str_repeat('    ', $depth);
        }

        echo '<b>&lt;</b>/<b style="color: red">', $node->nodeName, '</b><b>&gt;</b>';
        if (!$onlyChild) {
            echo "\n";
        }
    }

}
