<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\Interval\DateInterval;
use Dogma\Time\Interval\DateIntervalSet;

require_once __DIR__ . '/../../bootstrap.php';

$d = static function (int $day): Date {
    return Date::createFromComponents(2000, 1, $day);
};
$i = static function (int $start, int $end) use ($d): DateInterval {
    return new DateInterval($d($start), $d($end));
};
$s = static function (DateInterval ...$items): DateIntervalSet {
    return new DateIntervalSet($items);
};

$interval = new DateInterval($d(1), $d(5));
$emptyInterval = DateInterval::empty();

$set = new DateIntervalSet([$interval]);


createFromDateArray:
Assert::equal(DateIntervalSet::createFromDateArray([]), $s());
Assert::equal(DateIntervalSet::createFromDateArray([$d(1), $d(2), $d(3), $d(4), $d(5)]), $s($interval));
Assert::equal(DateIntervalSet::createFromDateArray([$d(1), $d(2), $d(4), $d(5)]), $s($i(1, 2), $i(4, 5)));


toDateArray:
Assert::equal($emptyInterval->toDateArray(), []);
Assert::equal($interval->toDateArray(), [$d(1), $d(2), $d(3), $d(4), $d(5)]);
Assert::equal($s($i(1, 2), $i(4, 5))->toDateArray(), [$d(1), $d(2), $d(4), $d(5)]);


getIntervals:
getIterator:
Assert::same($set->getIntervals(), iterator_to_array($set->getIterator()));


isEmpty:
Assert::true((new DateIntervalSet([]))->isEmpty());
Assert::true((new DateIntervalSet([$emptyInterval]))->isEmpty());


equals:
Assert::true($set->equals($s($i(1, 5))));
Assert::false($set->equals($s($i(1, 6))));


containsValue:
Assert::true($set->containsValue($d(1)));
Assert::true($set->containsValue($d(5)));
Assert::false($set->containsValue($d(6)));


envelope:
Assert::equal($s($i(1, 2), $i(4, 5))->envelope(), $interval);


normalize:
Assert::equal($s($i(1, 4), $i(2, 5))->normalize(), $set);
Assert::equal($s($i(10, 13), $i(5, 9), $i(18, 21), $i(5, 6), $i(15, 19))->normalize(), $s($i(5, 13), $i(15, 21)));


add:
Assert::equal($s($i(1, 2), $i(3, 4), $i(5, 6)), $s($i(1, 2))->add($s($i(3, 4), $i(5, 6))));


subtract:
Assert::equal($s($i(1, 10))->subtract($s($i(3, 4), $i(7, 8))), $s($i(1, 2), $i(5, 6), $i(9, 10)));


intersect:
Assert::equal($s($i(1, 5), $i(10, 15))->intersect($s($i(4, 12), $i(14, 20))), $s($i(4, 5), $i(10, 12), $i(14, 15)));


map:
Assert::equal($set->map(static function (DateInterval $interval) {
    return $interval;
}), $set);
Assert::equal($set->map(static function (DateInterval $interval) {
    return $interval->split(2);
}), $s($i(1, 3), $i(4, 5)));
Assert::equal($set->map(static function (DateInterval $interval) {
    return $interval->split(2)->getIntervals();
}), $s($i(1, 3), $i(4, 5)));

$set = $s($emptyInterval, $i(1, 1), $i(1, 2), $i(1, 3));


filterByLength:
Assert::equal($set->filterByLength('>', 1), $s($i(1, 3)));
Assert::equal($set->filterByLength('>=', 1), $s($i(1, 2), $i(1, 3)));
Assert::equal($set->filterByLength('=', 1), $s($i(1, 2)));
Assert::equal($set->filterByLength('<>', 1), $s($emptyInterval, $i(1, 1), $i(1, 3)));
Assert::equal($set->filterByLength('<=', 1), $s($emptyInterval, $i(1, 1), $i(1, 2)));
Assert::equal($set->filterByLength('<', 1), $s($emptyInterval, $i(1, 1)));


filterByCount:
Assert::equal($set->filterByDayCount('>', 1), $s($i(1, 2), $i(1, 3)));
Assert::equal($set->filterByDayCount('>=', 1), $s($i(1, 1), $i(1, 2), $i(1, 3)));
Assert::equal($set->filterByDayCount('=', 1), $s($i(1, 1)));
Assert::equal($set->filterByDayCount('<>', 1), $s($emptyInterval, $i(1, 2), $i(1, 3)));
Assert::equal($set->filterByDayCount('<=', 1), $s($emptyInterval, $i(1, 1)));
Assert::equal($set->filterByDayCount('<', 1), $s($emptyInterval));
