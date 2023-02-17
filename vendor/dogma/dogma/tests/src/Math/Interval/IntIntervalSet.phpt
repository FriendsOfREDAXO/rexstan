<?php declare(strict_types = 1);

namespace Dogma\Tests\Math\Interval;

use Dogma\Math\Interval\IntInterval;
use Dogma\Math\Interval\IntIntervalSet;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$interval = new IntInterval(1, 5);
$emptyInterval = IntInterval::empty();

$set = new IntIntervalSet([$interval]);

$i = static function (int $start, int $end) {
    return new IntInterval($start, $end);
};
$s = static function (IntInterval ...$items) {
    return new IntIntervalSet($items);
};


getIntervals:
getIterator:
Assert::same($set->getIntervals(), iterator_to_array($set->getIterator()));


isEmpty:
Assert::true((new IntIntervalSet([]))->isEmpty());
Assert::true((new IntIntervalSet([$emptyInterval]))->isEmpty());


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
Assert::equal($s($i(1, 10))->subtract($s($i(3, 4), $i(7, 8))), $s($i(1, 2), $i(5, 6), $i(9, 10)));


intersect:
Assert::equal($s($i(1, 5), $i(10, 15))->intersect($s($i(4, 12), $i(14, 20))), $s($i(4, 5), $i(10, 12), $i(14, 15)));


map:
Assert::equal($set->map(static function (IntInterval $interval) {
    return $interval;
}), $set);
Assert::equal($set->map(static function (IntInterval $interval) {
    return $interval->split(2);
}), $s($i(1, 3), $i(4, 5)));
Assert::equal($set->map(static function (IntInterval $interval) {
    return $interval->split(2)->getIntervals();
}), $s($i(1, 3), $i(4, 5)));

$set = $s(IntInterval::empty(), $i(1, 1), $i(1, 2), $i(1, 3));


filterByLength:
Assert::equal($set->filterByLength('>', 1), $s($i(1, 3)));
Assert::equal($set->filterByLength('>=', 1), $s($i(1, 2), $i(1, 3)));
Assert::equal($set->filterByLength('=', 1), $s($i(1, 2)));
Assert::equal($set->filterByLength('<>', 1), $s(IntInterval::empty(), $i(1, 1), $i(1, 3)));
Assert::equal($set->filterByLength('<=', 1), $s(IntInterval::empty(), $i(1, 1), $i(1, 2)));
Assert::equal($set->filterByLength('<', 1), $s(IntInterval::empty(), $i(1, 1)));


filterByCount:
Assert::equal($set->filterByCount('>', 1), $s($i(1, 2), $i(1, 3)));
Assert::equal($set->filterByCount('>=', 1), $s($i(1, 1), $i(1, 2), $i(1, 3)));
Assert::equal($set->filterByCount('=', 1), $s($i(1, 1)));
Assert::equal($set->filterByCount('<>', 1), $s(IntInterval::empty(), $i(1, 2), $i(1, 3)));
Assert::equal($set->filterByCount('<=', 1), $s(IntInterval::empty(), $i(1, 1)));
Assert::equal($set->filterByCount('<', 1), $s(IntInterval::empty()));
