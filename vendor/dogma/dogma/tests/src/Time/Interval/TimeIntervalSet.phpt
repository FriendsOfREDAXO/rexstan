<?php declare(strict_types = 1);

namespace Dogma\Tests\Math\Interval;

use Dogma\Tester\Assert;
use Dogma\Time\Interval\TimeInterval;
use Dogma\Time\Interval\TimeIntervalSet;
use Dogma\Time\Microseconds;
use Dogma\Time\Time;

require_once __DIR__ . '/../../bootstrap.php';

$t = static function (int $hours): Time {
    return Time::createFromComponents($hours);
};
$i = static function (int $start, int $end) use ($t): TimeInterval {
    return new TimeInterval($t($start), $t($end));
};
$s = static function (TimeInterval ...$items): TimeIntervalSet {
    return new TimeIntervalSet($items);
};

$interval = new TimeInterval($t(1), $t(5));
$emptyInterval = TimeInterval::empty();

$set = new TimeIntervalSet([$interval]);


getIntervals:
getIterator:
Assert::same($set->getIntervals(), iterator_to_array($set->getIterator()));


isEmpty:
Assert::true((new TimeIntervalSet([]))->isEmpty());
Assert::true((new TimeIntervalSet([$emptyInterval]))->isEmpty());


equals:
Assert::true($set->equals($s($i(1, 5))));
Assert::false($set->equals($s($i(1, 6))));


containsValue:
Assert::true($set->containsValue($t(1)));
Assert::true($set->containsValue($t(4)));
Assert::false($set->containsValue($t(5)));
Assert::false($set->containsValue($t(6)));


envelope:
Assert::equal($s($i(1, 2), $i(4, 5))->envelope(), $interval);


normalize:
Assert::equal($s($i(1, 4), $i(2, 5))->normalize(), $set);


add:
Assert::equal($s($i(1, 2), $i(3, 4), $i(5, 6)), $s($i(1, 2))->add($s($i(3, 4), $i(5, 6))));


subtract:
Assert::equal($s($i(1, 10))->subtract($s($i(3, 4), $i(7, 8))), $s($i(1, 3), $i(4, 7), $i(8, 10)));


intersect:
Assert::equal($s($i(1, 5), $i(10, 15))->intersect($s($i(4, 12), $i(14, 20))), $s($i(4, 5), $i(10, 12), $i(14, 15)));


map:
Assert::equal($set->map(static function (TimeInterval $interval) {
    return $interval;
}), $set);
Assert::equal($set->map(static function (TimeInterval $interval) {
    return $interval->split(2);
}), $s($i(1, 3), $i(3, 5)));
Assert::equal($set->map(static function (TimeInterval $interval) {
    return $interval->split(2)->getIntervals();
}), $s($i(1, 3), $i(3, 5)));

$set = $s(TimeInterval::empty(), $i(1, 1), $i(1, 2), $i(1, 3));


filterByLength:
Assert::equal($set->filterByLength('>', Microseconds::HOUR), $s($i(1, 3)));
Assert::equal($set->filterByLength('>=', Microseconds::HOUR), $s($i(1, 2), $i(1, 3)));
Assert::equal($set->filterByLength('=', Microseconds::HOUR), $s($i(1, 2)));
Assert::equal($set->filterByLength('<>', Microseconds::HOUR), $s(TimeInterval::empty(), $i(1, 1), $i(1, 3)));
Assert::equal($set->filterByLength('<=', Microseconds::HOUR), $s(TimeInterval::empty(), $i(1, 1), $i(1, 2)));
Assert::equal($set->filterByLength('<', Microseconds::HOUR), $s(TimeInterval::empty(), $i(1, 1)));
