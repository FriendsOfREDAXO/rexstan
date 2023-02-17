<?php declare(strict_types = 1);

namespace Dogma\Tests\Math;

use Dogma\Math\Graph\BreadthFirstSearchPathFinder;
use Dogma\Math\Graph\FloydWarshallPathFinder;
use Dogma\System\Php;
use Dogma\Tester\Assert;
use function array_map;

require_once __DIR__ . '/../../bootstrap.php';

//     a b c d e f g h
//   x 1 2 3 4 5 6 7 8
// y +-----------------+
// 8 | ♜♞♝♛♚♝♞♜ |
// 7 | ♟♟♟♟♟♟♟♟ |
// 6 |                 |
// 5 |                 |
// 4 |                 |
// 3 |                 |
// 2 | ♙♙♙♙♙♙♙♙ |
// 1 | ♖♘♗♕♔♗♘♖ |
//   +-----------------+

// construct all graph edges, that represent valid moves of a chess knight
$c = [1 => 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
$edges = []; // weighted edges for FW
$edges2 = []; // non-weighted edges for BFS
for ($y = 1; $y < 8; $y++) { // for each row
    for ($x = 1; $x <= 8; $x++) { // for each column
        if ($x >= 3) {
            $x2 = $x - 2;
            $y2 = $y + 1;
            $edges[$c[$x] . $y][$c[$x2] . $y2] = 1;
            $edges[$c[$x2] . $y2][$c[$x] . $y] = 1;

            $edges2[$c[$x] . $y][] = $c[$x2] . $y2;
            $edges2[$c[$x2] . $y2][] = $c[$x] . $y;
        }
        if ($x >= 2 && $y <= 6) {
            $x2 = $x - 1;
            $y2 = $y + 2;
            $edges[$c[$x] . $y][$c[$x2] . $y2] = 1;
            $edges[$c[$x2] . $y2][$c[$x] . $y] = 1;

            $edges2[$c[$x] . $y][] = $c[$x2] . $y2;
            $edges2[$c[$x2] . $y2][] = $c[$x] . $y;
        }
        if ($x <= 7 && $y <= 6) {
            $x2 = $x + 1;
            $y2 = $y + 2;
            $edges[$c[$x] . $y][$c[$x2] . $y2] = 1;
            $edges[$c[$x2] . $y2][$c[$x] . $y] = 1;

            $edges2[$c[$x] . $y][] = $c[$x2] . $y2;
            $edges2[$c[$x2] . $y2][] = $c[$x] . $y;
        }
        if ($x <= 6) {
            $x2 = $x + 2;
            $y2 = $y + 1;
            $edges[$c[$x] . $y][$c[$x2] . $y2] = 1;
            $edges[$c[$x2] . $y2][$c[$x] . $y] = 1;

            $edges2[$c[$x] . $y][] = $c[$x2] . $y2;
            $edges2[$c[$x2] . $y2][] = $c[$x] . $y;
        }
    }
}
$count = array_sum(array_map(static function ($a) {
    return array_sum($a);
}, $edges));

// 7 rows × 6 columns × 2 directions × 2 orientations × 2 oriented graph edge directions
Assert::same($count, 336);


$fw = new FloydWarshallPathFinder($edges);
$bfs = new BreadthFirstSearchPathFinder($edges2);


// show all path lengths and initial edges
if (!Php::getSapi()->isCli()) {
    echo $fw->printDistances();
    echo $fw->printGraphMatrix();
}


$path = $fw->getPath('a1', 'h8');
Assert::same($path, ['a1', 'b3', 'd2', 'c4', 'e5', 'g6', 'h8']);
$path = $fw->getPath('a1', 'a2');
Assert::same($path, ['a1', 'b3', 'c1', 'a2']);

$path = $bfs->getPath('a1', 'h8');
Assert::same($path, ['a1', 'b3', 'c1', 'd3', 'e5', 'f7', 'h8']);
$path = $bfs->getPath('a1', 'a2');
Assert::same($path, ['a1', 'b3', 'c1', 'a2']);


// check that BFS finds path with the same length as FW in all cases
for ($y = 1; $y <= 8; $y++) {
    for ($x = 1; $x <= 8; $x++) {
        for ($y2 = 1; $y2 <= 8; $y2++) {
            for ($x2 = 1; $x2 <= 8; $x2++) {
                $a = $c[$x] . $y;
                $b = $c[$x2] . $y2;
                Assert::same($fw->getDistance($a, $b), $bfs->getDistance($a, $b));
            }
        }
    }
}
