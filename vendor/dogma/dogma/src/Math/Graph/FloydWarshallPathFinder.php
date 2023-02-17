<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Graph;

use Dogma\StrictBehaviorMixin;
use const PHP_INT_MAX;
use function array_keys;
use function array_reverse;
use function count;
use function range;

/**
 * Floyd-Warshall algorithm for finding all shortest paths in oriented weighted graph.
 * Initial cost O(n³) where n is count of nodes. Path cost O(1)
 *
 * @see http://en.wikipedia.org/wiki/Floyd–Warshall_algorithm
 * @see https://github.com/pierre-fromager/PeopleFloydWarshall/blob/4731f8d1e6dd5e659f5945d03ddf8746a578a665/class/floyd-warshall.class.php
 */
class FloydWarshallPathFinder
{
    use StrictBehaviorMixin;

    /** @var int[][] */
    private $weights;

    /** @var int */
    private $nodeCount;

    /** @var string[]|int[] */
    private $nodeNames = [];

    /** @var int[][] */
    private $distances = [[]];

    /** @var mixed[][] */
    private $predecessors = [[]];

    /**
     * @param int[][] $weights graph edge weights. may be sparse
     */
    public function __construct(array $weights)
    {
        if (array_keys($weights) === range(0, count($weights))) {
            // array: assumption, that all nodes has an outgoing edge
            $this->weights = $weights;
            /// bug: wrong if last nodes has no outgoing edges
            $this->nodeCount = count($this->weights);
        } else {
            // hashmap: replace keys with numeric indexes
            $n = 0;
            $nodeNames = [];
            $normalized = [];
            foreach ($weights as $i => $nodes) {
                if (!isset($nodeNames[$i])) {
                    $nodeNames[$i] = $n++;
                }
                foreach ($nodes as $j => $weight) {
                    if (!isset($nodeNames[$j])) {
                        $nodeNames[$j] = $n++;
                    }
                    $normalized[$nodeNames[$i]][$nodeNames[$j]] = $weight;
                }
            }
            $this->weights = $normalized;
            $this->nodeNames = $nodeNames;
            $this->nodeCount = count($nodeNames);
        }

        $this->calculatePaths();
    }

    private function calculatePaths(): void
    {
        // init
        for ($i = 0; $i < $this->nodeCount; $i++) {
            for ($j = 0; $j < $this->nodeCount; $j++) {
                if ($i === $j) {
                    $this->distances[$i][$j] = 0;
                } elseif (isset($this->weights[$i][$j]) && $this->weights[$i][$j] > 0) {
                    $this->distances[$i][$j] = $this->weights[$i][$j];
                } else {
                    $this->distances[$i][$j] = PHP_INT_MAX;
                }
                $this->predecessors[$i][$j] = $i;
            }
        }

        // run
        for ($k = 0; $k < $this->nodeCount; $k++) {
            for ($i = 0; $i < $this->nodeCount; $i++) {
                for ($j = 0; $j < $this->nodeCount; $j++) {
                    if ($this->distances[$i][$j] > ($this->distances[$i][$k] + $this->distances[$k][$j])) {
                        $this->distances[$i][$j] = $this->distances[$i][$k] + $this->distances[$k][$j];
                        $this->predecessors[$i][$j] = $this->predecessors[$k][$j];
                    }
                }
            }
        }
    }

    /**
     * Get total cost (distance) between point a and b
     * @param int|string $i
     * @param int|string $j
     * @return int
     */
    public function getDistance($i, $j): int
    {
        if ($this->nodeNames !== []) {
            $i = $this->nodeNames[$i];
            $j = $this->nodeNames[$j];
        }

        return $this->distances[$i][$j];
    }

    /**
     * Get nodes between a and b
     * @param int|string $i
     * @param int|string $j
     * @return int[]|string[]
     */
    public function getPath($i, $j): array
    {
        $names = array_keys($this->nodeNames);
        if ($this->nodeNames !== []) {
            $i = $this->nodeNames[$i];
            $j = $this->nodeNames[$j];
        }

        $path = [];
        $k = $j;
        do {
            $path[] = $names[$k] ?? $k;
            $k = $this->predecessors[$i][$k];
        } while ($i !== $k);

        $start = $names[$i] ?? $i;
        if ($path !== [$start]) {
            $path[] = $names[$i] ?? $i;
        }

        return array_reverse($path);
    }

    /**
     * Print out the original Graph matrix in HTML
     */
    public function printGraphMatrix(): string
    {
        $names = array_keys($this->nodeNames);
        $rt = "<table>\n";
        if (!empty($this->nodeNames)) {
            $rt .= '<tr>';
            $rt .= '<td>&nbsp;</td>';
            for ($n = 0; $n < $this->nodeCount; $n++) {
                $rt .= '<td>' . ($names[$n] ?? $n) . '</td>';
            }
        }
        $rt .= '</tr>';
        for ($i = 0; $i < $this->nodeCount; $i++) {
            $rt .= '<tr>';
            if (!empty($this->nodeNames)) {
                $rt .= '<td>' . ($names[$i] ?? $i) . '</td>';
            }
            for ($j = 0; $j < $this->nodeCount; $j++) {
                $rt .= '<td>' . ($this->weights[$i][$j] ?? '') . '</td>';
            }
            $rt .= '</tr>';
        }
        $rt .= '</table>';

        return $rt;
    }

    /**
     * Print out distances matrix in HTML
     */
    public function printDistances(): string
    {
        $names = array_keys($this->nodeNames);
        $rt = "<table>\n";
        if (!empty($this->nodeNames)) {
            $rt .= '<tr>';
            $rt .= "<td>&nbsp;</td>\n";
            for ($n = 0; $n < $this->nodeCount; $n++) {
                $rt .= '<td>' . ($names[$n] ?? $n) . '</td>';
            }
        }
        $rt .= '</tr>';
        for ($i = 0; $i < $this->nodeCount; $i++) {
            $rt .= '<tr>';
            if (!empty($this->nodeNames)) {
                $rt .= '<td>' . ($names[$i] ?? $i) . "</td>\n";
            }
            for ($j = 0; $j < $this->nodeCount; $j++) {
                $rt .= '<td>' . $this->distances[$i][$j] . "</td>\n";
            }
            $rt .= '</tr>';
        }
        $rt .= "</table>\n";

        return $rt;
    }

    /**
     * Print out predecessors matrix in HTML
     */
    public function printPredecessors(): string
    {
        $names = array_keys($this->nodeNames);
        $rt = "<table>\n";
        if (!empty($this->nodeNames)) {
            $rt .= '<tr>';
            $rt .= '<td>&nbsp;</td>';
            for ($n = 0; $n < $this->nodeCount; $n++) {
                $rt .= '<td>' . ($names[$n] ?? $n) . "</td>\n";
            }
        }
        $rt .= '</tr>';
        for ($i = 0; $i < $this->nodeCount; $i++) {
            $rt .= '<tr>';
            if (!empty($this->nodeNames)) {
                $rt .= '<td>' . ($names[$i] ?? $i) . "</td>\n";
            }
            for ($j = 0; $j < $this->nodeCount; $j++) {
                $rt .= '<td>' . $this->predecessors[$i][$j] . "</td>\n";
            }
            $rt .= "</tr>\n";
        }
        $rt .= "</table>\n";

        return $rt;
    }

}
