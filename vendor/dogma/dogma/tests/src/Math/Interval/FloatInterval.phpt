<?php declare(strict_types = 1);

namespace Dogma\Tests\Math\Interval;

use Dogma\Call;
use Dogma\IntersectResult;
use Dogma\Math\Interval\FloatInterval;
use Dogma\Math\Interval\FloatIntervalSet;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$closed = FloatInterval::closed(1.0, 5.0);
$open = FloatInterval::open(1.0, 5.0);
$empty = FloatInterval::empty();
$all = FloatInterval::all();

$r = static function (int $start, int $end, bool $openStart = false, bool $openEnd = false) {
    return new FloatInterval((float) $start, (float) $end, $openStart, $openEnd);
};
$s = static function (FloatInterval ...$items) {
    return new FloatIntervalSet($items);
};


shift:
Assert::equal($closed->shift(10.0), $r(11, 15));
Assert::equal($open->shift(10.0), $r(11, 15, true, true));


multiply:
Assert::equal($closed->multiply(10.0), $r(10, 50));
Assert::equal($open->multiply(10.0), $r(10, 50, true, true));


getStart:
Assert::same($closed->getStart(), 1.0);
Assert::same($open->getStart(), 1.0);


getEnd:
Assert::same($closed->getEnd(), 5.0);
Assert::same($open->getEnd(), 5.0);


getLength:
Assert::same($closed->getLength(), 4.0);
Assert::same($open->getLength(), 4.0);
Assert::same($empty->getLength(), 0.0);


hasOpenStart:
Assert::false($closed->hasOpenStart());
Assert::true($open->hasOpenStart());


hasOpenEnd:
Assert::false($closed->hasOpenEnd());
Assert::true($open->hasOpenEnd());


isEmpty:
Assert::false($closed->isEmpty());
Assert::false($open->isEmpty());

Assert::false((new FloatInterval(1.0, 1.0))->isEmpty());
Assert::true((new FloatInterval(1.0, 1.0, true, true))->isEmpty());

Assert::false($all->isEmpty());
Assert::true($empty->isEmpty());


equals:
Assert::true($closed->equals($r(1, 5)));
Assert::false($closed->equals($r(1, 4)));
Assert::false($closed->equals($r(2, 5)));

Assert::true($open->equals($r(1, 5, true, true)));
Assert::false($open->equals($r(1, 5)));
Assert::false($open->equals($r(1, 5, true)));
Assert::false($open->equals($r(1, 5, false, true)));

Assert::true($empty->equals(new FloatInterval(1.0, 1.0, true, true)));


compareIntersects:
Call::withArgs(static function (array $a, array $b, int $expected): void {
    $a = new FloatInterval(...$a);
    $b = new FloatInterval(...$b);
    Assert::same($a->compareIntersects($b), $expected);
}, [
    [[1, 2, false, false], [3, 4, false, false], IntersectResult::BEFORE_START],

    [[1, 2, false, true],  [2, 3, true,  false], IntersectResult::BEFORE_START],
    [[1, 2, false, true],  [2, 3, false, false], IntersectResult::TOUCHES_START],
    [[1, 2, false, false], [2, 3, true,  false], IntersectResult::TOUCHES_START],
    [[1, 2, false, false], [2, 3, false, false], IntersectResult::INTERSECTS_START],

    [[1, 3, false, false], [2, 4, false, false], IntersectResult::INTERSECTS_START],

    [[1, 3, false, true],  [2, 3, false, false], IntersectResult::INTERSECTS_START],
    [[1, 3, false, false], [2, 3, false, false], IntersectResult::EXTENDS_START],
    [[1, 3, false, true],  [2, 3, false, true],  IntersectResult::EXTENDS_START],
    [[1, 3, false, false], [2, 3, false, true],  IntersectResult::CONTAINS],

    [[1, 4, false, false], [2, 3, false, false], IntersectResult::CONTAINS],

    [[1, 2, false, false], [1, 3, true,  false], IntersectResult::INTERSECTS_START],
    [[1, 2, false, false], [1, 3, false, false], IntersectResult::FITS_TO_START],
    [[1, 2, true,  false], [1, 3, true,  false], IntersectResult::FITS_TO_START],
    [[1, 2, true,  false], [1, 3, false, false], IntersectResult::IS_CONTAINED],

    [[1, 2, false, true],  [1, 2, true,  false], IntersectResult::INTERSECTS_START],
    [[1, 2, false, true],  [1, 2, true,  true],  IntersectResult::EXTENDS_START],
    [[1, 2, false, false], [1, 2, true,  false], IntersectResult::EXTENDS_START],
    [[1, 2, false, true],  [1, 2, false, false], IntersectResult::FITS_TO_START],
    [[1, 2, true,  true],  [1, 2, true,  false], IntersectResult::FITS_TO_START],
    [[1, 2, false, false], [1, 2, true,  true],  IntersectResult::CONTAINS],
    [[1, 2, false, false], [1, 2, false, false], IntersectResult::SAME],
    [[1, 2, true,  false], [1, 2, true,  false], IntersectResult::SAME],
    [[1, 2, false, true],  [1, 2, false, true],  IntersectResult::SAME],
    [[1, 2, true,  true],  [1, 2, true,  true],  IntersectResult::SAME],
    [[1, 2, true,  true],  [1, 2, false, false], IntersectResult::IS_CONTAINED],
    [[1, 2, true,  false], [1, 2, false, false], IntersectResult::FITS_TO_END],
    [[1, 2, true,  true],  [1, 2, false, true],  IntersectResult::FITS_TO_END],
    [[1, 2, false, false], [1, 2, false, true],  IntersectResult::EXTENDS_END],
    [[1, 2, true,  false], [1, 2, true,  true],  IntersectResult::EXTENDS_END],
    [[1, 2, true,  false], [1, 2, false, true],  IntersectResult::INTERSECTS_END],

    [[2, 3, false, false], [1, 3, false, true],  IntersectResult::INTERSECTS_END],
    [[2, 3, false, false], [1, 3, false, false], IntersectResult::FITS_TO_END],
    [[2, 3, false, true],  [1, 3, false, true],  IntersectResult::FITS_TO_END],
    [[2, 3, false, true],  [1, 3, false, false], IntersectResult::IS_CONTAINED],

    [[2, 3, false, false], [1, 4, false, false], IntersectResult::IS_CONTAINED],

    [[1, 3, false, false], [1, 2, true,  false], IntersectResult::CONTAINS],
    [[1, 3, false, false], [1, 2, false, false], IntersectResult::EXTENDS_END],
    [[1, 3, true,  false], [1, 2, true,  false], IntersectResult::EXTENDS_END],
    [[1, 3, true,  false], [1, 2, false, false], IntersectResult::INTERSECTS_END],

    [[2, 4, false, false], [1, 3, false, false], IntersectResult::INTERSECTS_END],

    [[2, 3, false, false], [1, 2, false, false], IntersectResult::INTERSECTS_END],
    [[2, 3, false, false], [1, 2, false, true],  IntersectResult::TOUCHES_END],
    [[2, 3, true,  false], [1, 2, false, false], IntersectResult::TOUCHES_END],
    [[2, 3, true,  false], [1, 2, false, true],  IntersectResult::AFTER_END],

    [[3, 4, false, false], [1, 2, false, false], IntersectResult::AFTER_END],
]);


containsValue:
Assert::true($closed->containsValue(3.0));
Assert::true($closed->containsValue(1.0));
Assert::true($closed->containsValue(5.0));
Assert::false($closed->containsValue(0.0));
Assert::false($closed->containsValue(6.0));

Assert::true($open->containsValue(3.0));
Assert::false($open->containsValue(1.0));
Assert::false($open->containsValue(5.0));
Assert::false($open->containsValue(0.0));
Assert::false($open->containsValue(6.0));


contains:
Assert::true($closed->contains($r(1, 5)));
Assert::true($closed->contains($r(1, 3)));
Assert::true($closed->contains($r(3, 5)));
Assert::false($closed->contains($r(0, 5)));
Assert::false($closed->contains($r(1, 6)));
Assert::false($closed->contains($r(-1, 0)));
Assert::false($closed->contains($empty));

Assert::false($open->contains($r(1, 5)));
Assert::false($open->contains($r(1, 3)));
Assert::false($open->contains($r(3, 5)));
Assert::false($open->contains($r(1, 5, true)));
Assert::false($open->contains($r(1, 5, false, true)));
Assert::true($open->contains($r(1, 5, true, true)));


intersects:
Assert::true($closed->intersects($r(-5, 10)));
Assert::true($closed->intersects($r(1, 5)));
Assert::true($closed->intersects($r(0, 1)));
Assert::true($closed->intersects($r(5, 6)));
Assert::false($closed->intersects($r(-1, 0)));
Assert::false($closed->intersects($empty));

Assert::true($open->intersects($r(-5, 10)));
Assert::true($open->intersects($r(1, 5)));
Assert::true($open->intersects($r(0, 1)));
Assert::true($open->intersects($r(5, 6)));
Assert::false($open->intersects($r(0, 1, false, true)));
Assert::false($open->intersects($r(5, 6, true)));
Assert::true($open->intersects(FloatInterval::open(1, 5)));


touches:
Assert::true($closed->touches($r(-10, 1)));
Assert::true($closed->touches($r(5, 10)));
Assert::false($closed->touches($r(-10, 2)));
Assert::false($closed->touches($r(6, 10)));

Assert::false($closed->touches($r(-10, 1), true));
Assert::false($closed->touches($r(5, 10), true));
Assert::true($closed->touches($r(-10, 1, false, true), true));
Assert::true($closed->touches($r(5, 10, true, false), true));
Assert::false($open->touches($r(-10, 1, false, true), true));
Assert::false($open->touches($r(5, 10, true, false), true));


split:
Assert::equal($closed->split(1), $s($closed));
Assert::equal($closed->split(2, FloatInterval::SPLIT_CLOSED), $s($r(1, 3), $r(3, 5)));
Assert::equal(
    $closed->split(2, FloatInterval::SPLIT_OPEN_STARTS),
    $s($r(1, 3), $r(3, 5, true))
);
Assert::equal(
    $closed->split(2, FloatInterval::SPLIT_OPEN_ENDS),
    $s($r(1, 3, false, true), $r(3, 5))
);
Assert::equal(
    $closed->split(4, FloatInterval::SPLIT_CLOSED),
    $s($r(1, 2), $r(2, 3), $r(3, 4), $r(4, 5))
);
Assert::equal(
    $closed->split(4, FloatInterval::SPLIT_OPEN_STARTS),
    $s($r(1, 2), $r(2, 3, true), $r(3, 4, true), $r(4, 5, true))
);
Assert::equal(
    $closed->split(4, FloatInterval::SPLIT_OPEN_ENDS),
    $s($r(1, 2, false, true), $r(2, 3, false, true), $r(3, 4, false, true), $r(4, 5))
);
Assert::equal($empty->split(5), $s($empty));


splitBy:
Assert::equal(
    $closed->splitBy([-10, 2, 4, 10], FloatInterval::SPLIT_CLOSED),
    $s($r(1, 2), $r(2, 4), $r(4, 5))
);
Assert::equal(
    $open->splitBy([-10, 2, 4, 10], FloatInterval::SPLIT_CLOSED),
    $s($r(1, 2, true), $r(2, 4), $r(4, 5, false, true))
);
Assert::equal(
    $closed->splitBy([-10, 2, 4, 10], FloatInterval::SPLIT_OPEN_STARTS),
    $s($r(1, 2), $r(2, 4, true), $r(4, 5, true))
);
Assert::equal(
    $closed->splitBy([-10, 2, 4, 10], FloatInterval::SPLIT_OPEN_ENDS),
    $s($r(1, 2, false, true), $r(2, 4, false, true), $r(4, 5))
);


envelope:
Assert::equal($closed->envelope($r(5, 6)), $r(1, 6));
Assert::equal($closed->envelope($r(0, 1)), $r(0, 5));
Assert::equal($closed->envelope($r(-10, -5)), $r(-10, 5));
Assert::equal($closed->envelope($r(-10, -5), $r(5, 10)), $r(-10, 10));
Assert::equal($closed->envelope($empty), $closed);

Assert::equal($open->envelope($r(5, 6)), $r(1, 6, true));
Assert::equal($open->envelope($r(0, 1)), $r(0, 5, false, true));
Assert::equal($open->envelope($r(-10, -5)), $r(-10, 5, false, true));
Assert::equal($open->envelope($r(-10, -5), $r(5, 10)), $r(-10, 10));
Assert::equal($open->envelope($empty), $open);


intersect:
Assert::equal($closed->intersect($r(3, 6)), $r(3, 5));
Assert::equal($closed->intersect($r(0, 1)), $r(1, 1));
Assert::equal($closed->intersect($r(3, 6), $r(0, 4)), $r(3, 4));
Assert::equal($closed->intersect($r(-10, -5)), $empty);
Assert::equal($closed->intersect($r(-10, -5), $r(5, 10)), $empty);
Assert::equal($closed->intersect($empty), $empty);

Assert::equal($open->intersect($r(3, 6)), $r(3, 5, false, true));
Assert::equal($open->intersect($r(0, 3)), $r(1, 3, true, false));
Assert::equal($open->intersect($r(0, 1)), $empty);
Assert::equal($open->intersect($r(5, 10)), $empty);
Assert::equal($open->intersect($r(3, 6), $r(0, 4)), $r(3, 4));
Assert::equal($open->intersect($r(-10, -5)), $empty);
Assert::equal($open->intersect($r(-10, -5), $r(5, 10)), $empty);
Assert::equal($open->intersect($empty), $empty);


union:
Assert::equal($closed->union($r(3, 6)), $s($r(1, 6)));
Assert::equal($closed->union($r(0, 1)), $s($r(0, 5)));
Assert::equal($closed->union($r(3, 6), $r(0, 4)), $s($r(0, 6)));
Assert::equal($closed->union($r(10, 20)), $s($closed, $r(10, 20)));
Assert::equal($closed->union($all), $s($all));
Assert::equal($closed->union($empty), $s($closed));

Assert::equal($open->union($r(3, 6)), $s($r(1, 6, true)));
Assert::equal($open->union($r(0, 3)), $s($r(0, 5, false, true)));
Assert::equal($open->union($r(0, 1)), $s($r(0, 5, false, true)));
Assert::equal($open->union($r(0, 1, false, true)), $s($r(0, 1, false, true), $open));
Assert::equal($open->union($r(5, 6)), $s($r(1, 6, true)));
Assert::equal($open->union($r(5, 6, true, false)), $s($open, $r(5, 6, true, false)));
Assert::equal($open->union($r(3, 6), $r(0, 4)), $s($r(0, 6)));
Assert::equal($open->union($r(10, 20)), $s($open, $r(10, 20)));
Assert::equal($open->union($all), $s($all));
Assert::equal($open->union($empty), $s($open));


difference:
Assert::equal($closed->difference($r(3, 6)), $s($r(1, 3, false, true), $r(5, 6, true, false)));
Assert::equal($closed->difference($r(0, 1)), $s($r(0, 1, false, true), $r(1, 5, true, false)));
Assert::equal($closed->difference($r(3, 6), $r(0, 4)), $s($r(0, 1, false, true), $r(5, 6, true, false)));
Assert::equal($closed->difference($r(10, 20)), $s($closed, $r(10, 20)));
Assert::equal($closed->difference($all), $s(new FloatInterval(FloatInterval::MIN, 1.0, false, true), new FloatInterval(5.0, FloatInterval::MAX, true, false)));
Assert::equal($closed->difference($empty), $s($closed));


subtract:
Assert::equal($closed->subtract($r(0, 2)), $s($r(2, 5, true, false)));
Assert::equal($closed->subtract($r(4, 6)), $s($r(1, 4, false, true)));
Assert::equal($closed->subtract($r(2, 3)), $s($r(1, 2, false, true), $r(3, 5, true, false)));
Assert::equal($closed->subtract($r(0, 1), $r(4, 6)), $s($r(1, 4, true, true)));
Assert::equal($closed->subtract($empty), $s($closed));
Assert::equal($closed->subtract($all), $s());
Assert::equal($all->subtract($empty), $s($all));
Assert::equal($empty->subtract($empty), $s($empty));


invert:
Assert::equal($closed->invert(), $s(
    new FloatInterval(FloatInterval::MIN, 1.0, false, true),
    new FloatInterval(5.0, FloatInterval::MAX, true, false)
));
Assert::equal($empty->invert(), $s($all));
Assert::equal($all->invert(), $s());


countOverlaps:
Assert::equal(FloatInterval::countOverlaps($empty), []);
Assert::equal(FloatInterval::countOverlaps($closed, $r(0, 1)), [
    [$r(0, 1, false, true), 1],
    [$r(1, 1, false, false), 2],
    [$r(1, 5, true, false), 1],
]);
Assert::equal(FloatInterval::countOverlaps($closed, $r(5, 6, false, true)), [
    [$r(1, 5, false, true), 1],
    [$r(5, 5, false, false), 2],
    [$r(5, 6, true, true), 1],
]);
Assert::equal(FloatInterval::countOverlaps($r(0, 5), $r(1, 6), $r(2, 7)), [
    [$r(0, 1, false, true), 1],
    [$r(1, 2, false, true), 2],
    [$r(2, 5, false, false), 3],
    [$r(5, 6, true, false), 2],
    [$r(6, 7, true, false), 1],
]);


explodeOverlaps:
Assert::equal(FloatInterval::explodeOverlaps($empty), []);
Assert::equal(FloatInterval::explodeOverlaps($closed, $r(0, 1)), [
    $r(0, 1, false, true),
    $r(1, 1, false, false),
    $r(1, 1, false, false),
    $r(1, 5, true, false),
]);
Assert::equal(FloatInterval::explodeOverlaps($r(0, 5), $r(1, 6), $r(2, 7)), [
    $r(0, 1, false, true),
    $r(1, 2, false, true),
    $r(1, 2, false, true),
    $r(2, 5, false, false),
    $r(2, 5, false, false),
    $r(2, 5, false, false),
    $r(5, 6, true, false),
    $r(5, 6, true, false),
    $r(6, 7, true, false),
]);
