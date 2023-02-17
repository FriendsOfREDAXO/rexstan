<?php declare(strict_types = 1);

namespace Dogma\Tests\Math\Interval;

use Dogma\Math\Interval\FloatInterval;
use Dogma\Math\Interval\FloatIntervalSet;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$interval = new FloatInterval(1, 5);
$emptyInterval = FloatInterval::empty();

$set = new FloatIntervalSet([$interval]);

$i = static function (int $start, int $end, bool $openStart = false, bool $openEnd = false) {
    return new FloatInterval((float) $start, (float) $end, $openStart, $openEnd);
};
$s = static function (FloatInterval ...$items) {
    return new FloatIntervalSet($items);
};


getIntervals:
getIterator:
Assert::same($set->getIntervals(), iterator_to_array($set->getIterator()));


isEmpty:
Assert::true((new FloatIntervalSet([]))->isEmpty());
Assert::true((new FloatIntervalSet([$emptyInterval]))->isEmpty());


equals:
Assert::true($set->equals($s($i(1, 5))));
Assert::false($set->equals($s($i(1, 6))));


containsValue:
Assert::true($set->containsValue(1));
Assert::true($set->containsValue(5));
Assert::false($set->containsValue(6));


envelope:
Assert::equal($s($i(1, 2), $i(4, 5))->envelope(), $interval);


normalize:
Assert::equal($s($i(1, 4), $i(2, 5))->normalize(), $set);


add:
Assert::equal($s($i(1, 2), $i(3, 4), $i(5, 6)), $s($i(1, 2))->add($s($i(3, 4), $i(5, 6))));


subtract:
Assert::equal($s($i(1, 10))->subtract($s($i(3, 4), $i(7, 8))), $s($i(1, 3, false, true), $i(4, 7, true, true), $i(8, 10, true, false)));


intersect:
Assert::equal($s($i(1, 5), $i(10, 15))->intersect($s($i(4, 12), $i(14, 20))), $s($i(4, 5), $i(10, 12), $i(14, 15)));


map:
Assert::equal($set->map(static function (FloatInterval $interval) {
    return $interval;
}), $set);
Assert::equal($set->map(static function (FloatInterval $interval) {
    return $interval->split(2, FloatInterval::SPLIT_CLOSED);
}), $s($i(1, 3), $i(3, 5)));
Assert::equal($set->map(static function (FloatInterval $interval) {
    return $interval->split(2, FloatInterval::SPLIT_CLOSED)->getIntervals();
}), $s($i(1, 3), $i(3, 5)));

$set = $s(FloatInterval::empty(), $i(1, 1), $i(1, 2), $i(1, 3));


filterByLength:
Assert::equal($set->filterByLength('>', 1), $s($i(1, 3)));
Assert::equal($set->filterByLength('>=', 1), $s($i(1, 2), $i(1, 3)));
Assert::equal($set->filterByLength('=', 1), $s($i(1, 2)));
Assert::equal($set->filterByLength('<>', 1), $s(FloatInterval::empty(), $i(1, 1), $i(1, 3)));
Assert::equal($set->filterByLength('<=', 1), $s(FloatInterval::empty(), $i(1, 1), $i(1, 2)));
Assert::equal($set->filterByLength('<', 1), $s(FloatInterval::empty(), $i(1, 1)));
