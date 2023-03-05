<?php declare(strict_types = 1);

namespace Dogma\Tests\Math\Interval;

use Dogma\Math\Interval\IntInterval;
use Dogma\Math\Interval\IntIntervalSet;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$interval = new IntInterval(1, 5);
$empty = IntInterval::empty();
$all = IntInterval::all();

$r = static function (int $start, int $end) {
    return new IntInterval($start, $end);
};
$s = static function (IntInterval ...$items) {
    return new IntIntervalSet($items);
};


shift:
Assert::equal($interval->shift(10), $r(11, 15));


multiply:
Assert::equal($interval->multiply(10), $r(10, 50));


getStart:
Assert::same($interval->getStart(), 1);


getEnd:
Assert::same($interval->getEnd(), 5);


getLength:
Assert::same($interval->getLength(), 4);
Assert::same($empty->getLength(), 0);


getCount:
Assert::same($interval->getCount(), 5);
Assert::same($empty->getCount(), 0);


isEmpty:
Assert::false($interval->isEmpty());
Assert::false((new IntInterval(1, 1))->isEmpty());
Assert::false($all->isEmpty());
Assert::true($empty->isEmpty());


equals:
Assert::true($interval->equals($r(1, 5)));
Assert::false($interval->equals($r(1, 4)));
Assert::false($interval->equals($r(2, 5)));


containsValue:
Assert::true($interval->containsValue(1));
Assert::true($interval->containsValue(3));
Assert::true($interval->containsValue(5));
Assert::false($interval->containsValue(0));
Assert::false($interval->containsValue(6));


contains:
Assert::true($interval->contains($r(1, 5)));
Assert::true($interval->contains($r(1, 3)));
Assert::true($interval->contains($r(3, 5)));
Assert::false($interval->contains($r(0, 5)));
Assert::false($interval->contains($r(1, 6)));
Assert::false($interval->contains($r(-1, 0)));
Assert::false($interval->contains($empty));


intersects:
Assert::true($interval->intersects($r(-5, 10)));
Assert::true($interval->intersects($r(1, 5)));
Assert::true($interval->intersects($r(0, 1)));
Assert::true($interval->intersects($r(5, 6)));
Assert::false($interval->intersects($r(-1, 0)));
Assert::false($interval->intersects($empty));


touches:
Assert::true($interval->touches($r(-10, 0)));
Assert::true($interval->touches($r(6, 10)));
Assert::false($interval->touches($r(-10, 1)));
Assert::false($interval->touches($r(5, 10)));


split:
Assert::equal($interval->split(1), $s($interval));
Assert::equal($interval->split(2), $s($r(1, 3), $r(4, 5)));
Assert::equal($interval->split(3), $s($r(1, 2), $r(3, 3), $r(4, 5)));
Assert::equal($interval->split(4), $s($r(1, 1), $r(2, 3), $r(4, 4), $r(5, 5)));
Assert::equal($interval->split(5), $s($r(1, 1), $r(2, 2), $r(3, 3), $r(4, 4), $r(5, 5)));
Assert::equal($interval->split(9), $s($r(1, 1), $r(2, 2), $r(3, 3), $r(4, 4), $r(5, 5)));
Assert::equal($empty->split(5), $s($empty));


splitBy:
Assert::equal($interval->splitBy([-10, 2, 4, 10]), $s($r(1, 1), $r(2, 3), $r(4, 5)));


envelope:
Assert::equal($interval->envelope($r(5, 6)), $r(1, 6));
Assert::equal($interval->envelope($r(0, 1)), $r(0, 5));
Assert::equal($interval->envelope($r(-10, -5)), $r(-10, 5));
Assert::equal($interval->envelope($r(-10, -5), $r(5, 10)), $r(-10, 10));
Assert::equal($interval->envelope($empty), $interval);


intersect:
Assert::equal($interval->intersect($r(3, 6)), $r(3, 5));
Assert::equal($interval->intersect($r(0, 1)), $r(1, 1));
Assert::equal($interval->intersect($r(3, 6), $r(0, 4)), $r(3, 4));
Assert::equal($interval->intersect($r(-10, -5)), $empty);
Assert::equal($interval->intersect($r(-10, -5), $r(5, 10)), $empty);
Assert::equal($interval->intersect($empty), $empty);


union:
Assert::equal($interval->union($r(3, 6)), $s($r(1, 6)));
Assert::equal($interval->union($r(0, 1)), $s($r(0, 5)));
Assert::equal($interval->union($r(3, 6), $r(0, 4)), $s($r(0, 6)));
Assert::equal($interval->union($r(10, 20)), $s($interval, $r(10, 20)));
Assert::equal($interval->union($all), $s($all));
Assert::equal($interval->union($empty), $s($interval));


difference:
Assert::equal($interval->difference($r(3, 6)), $s($r(1, 2), $r(6, 6)));
Assert::equal($interval->difference($r(0, 1)), $s($r(0, 0), $r(2, 5)));
Assert::equal($interval->difference($r(3, 6), $r(0, 4)), $s($r(0, 0), $r(6, 6)));
Assert::equal($interval->difference($r(10, 20)), $s($interval, $r(10, 20)));
Assert::equal($interval->difference($all), $s($r(IntInterval::MIN, 0), $r(6, IntInterval::MAX)));
Assert::equal($interval->difference($empty), $s($interval));


subtract:
Assert::equal($interval->subtract($r(0, 2)), $s($r(3, 5)));
Assert::equal($interval->subtract($r(4, 6)), $s($r(1, 3)));
Assert::equal($interval->subtract($r(2, 3)), $s($r(1, 1), $r(4, 5)));
Assert::equal($interval->subtract($r(0, 1), $r(4, 6)), $s($r(2, 3)));
Assert::equal($interval->subtract($empty), $s($interval));
Assert::equal($interval->subtract($all), $s());
Assert::equal($all->subtract($empty), $s($all));
Assert::equal($empty->subtract($empty), $s($empty));


invert:
Assert::equal($interval->invert(), $s($r(IntInterval::MIN, 0), $r(6, IntInterval::MAX)));
Assert::equal($empty->invert(), $s($all));
Assert::equal($all->invert(), $s());


countOverlaps:
Assert::equal(IntInterval::countOverlaps($empty), []);
Assert::equal(IntInterval::countOverlaps($interval, $r(0, 1)), [
    [$r(0, 0), 1],
    [$r(1, 1), 2],
    [$r(2, 5), 1],
]);
Assert::equal(IntInterval::countOverlaps($r(0, 5), $r(1, 6), $r(2, 7)), [
    [$r(0, 0), 1],
    [$r(1, 1), 2],
    [$r(2, 5), 3],
    [$r(6, 6), 2],
    [$r(7, 7), 1],
]);


explodeOverlaps:
Assert::equal(IntInterval::explodeOverlaps($empty), []);
Assert::equal(IntInterval::explodeOverlaps($interval, $r(0, 1)), [
    $r(0, 0),
    $r(1, 1),
    $r(1, 1),
    $r(2, 5),
]);
Assert::equal(IntInterval::explodeOverlaps($r(0, 5), $r(1, 6), $r(2, 7)), [
    $r(0, 0),
    $r(1, 1),
    $r(1, 1),
    $r(2, 5),
    $r(2, 5),
    $r(2, 5),
    $r(6, 6),
    $r(6, 6),
    $r(7, 7),
]);
