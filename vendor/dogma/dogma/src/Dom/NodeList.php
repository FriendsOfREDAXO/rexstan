<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Dom;

use Countable;
use Dogma\StrictBehaviorMixin;
use DOMElement;
use DOMNode;
use DOMNodeList;
use Iterator;

/**
 * @implements Iterator<int, DOMNode>
 */
class NodeList implements Countable, Iterator
{
    use StrictBehaviorMixin;

    /** @var DOMNodeList<DOMNode> */
    private $nodeList;

    /** @var QueryEngine */
    private $engine;

    /** @var int */
    private $offset = 0;

    /**
     * @param DOMNodeList<DOMNode> $nodeList
     */
    public function __construct(DOMNodeList $nodeList, QueryEngine $engine)
    {
        $this->nodeList = $nodeList;
        $this->engine = $engine;
    }

    /**
     * @return Element|DOMNode
     */
    public function item(int $offset)
    {
        /** @var DOMNode $item */
        $item = $this->nodeList->item($offset);

        return $this->wrap($item);
    }

    public function count(): int
    {
        // PHP bug - cannot count items using $length
        $n = 0;
        while ($this->nodeList->item($n)) {
            $n++;
        }
        return $n;
    }

    /**
     * @return Element|DOMNode
     */
    public function current()
    {
        /** @var DOMNode $item */
        $item = $this->nodeList->item($this->offset);

        return $this->wrap($item);
    }

    public function key(): int
    {
        return $this->offset;
    }

    public function next(): void
    {
        $this->offset++;
    }

    public function rewind(): void
    {
        $this->offset = 0;
    }

    public function valid(): bool
    {
        // PHP bug - cannot iterate through items
        return $this->nodeList->item($this->offset) !== null;
    }

    /**
     * @return Element|DOMNode
     */
    private function wrap(DOMNode $node)
    {
        if ($node instanceof DOMElement) {
            return new Element($node, $this->engine);
        } else {
            return $node;
        }
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function dump(): void
    {
        Dumper::dump($this);
    }

}
