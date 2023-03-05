<?php declare(strict_types = 1);

namespace Dogma\Tests\Math\Interval;

use Dogma\Str;
use Dogma\Tester\Assert;
use Dogma\Time\DayOfYear;
use Dogma\Time\Interval\DayOfYearInterval;
use Dogma\Time\Interval\DayOfYearIntervalSet;
use function strval;

require_once __DIR__ . '/../../bootstrap.php';

$d = static function (int $day): DayOfYear {
    return new DayOfYear('01-' . Str::padLeft(strval($day), 2, '0'));
};
$i = static function (int $start, int $end): DayOfYearInterval {
    return new DayOfYearInterval(
        new DayOfYear('01-' . Str::padLeft(strval($start), 2, '0')),
        new DayOfYear('01-' . Str::padLeft(strval($end), 2, '0'))
    );
};
$s = static function (DayOfYearInterval ...$items): DayOfYearIntervalSet {
    return new DayOfYearIntervalSet($items);
};

$interval = new DayOfYearInterval($d(1), $d(5));
$emptyInterval = DayOfYearInterval::empty();

$set = new DayOfYearIntervalSet([$interval]);


getIntervals:
getIterator:
Assert::same($set->getIntervals(), iterator_to_array($set->getIterator()));


isEmpty:
Assert::true((new DayOfYearIntervalSet([]))->isEmpty());
Assert::true((new DayOfYearIntervalSet([$emptyInterval]))->isEmpty());


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


add:
Assert::equal($s($i(1, 2), $i(3, 4), $i(5, 6)), $s($i(1, 2))->add($s($i(3, 4), $i(5, 6))));


subtract:
Assert::equal($s($i(1, 10))->subtract($s($i(3, 4), $i(7, 8))), $s($i(1, 3), $i(4, 7), $i(8, 10)));


intersect:
Assert::equal($s($i(1, 5), $i(10, 15))->intersect($s($i(4, 12), $i(14, 20))), $s($i(4, 5), $i(10, 12), $i(14, 15)));


map:
Assert::equal($set->map(static function (DayOfYearInterval $interval) {
    return $interval;
}), $set);
Assert::equal($set->map(static function (DayOfYearInterval $interval) {
    return $interval->split(2);
}), $s($i(1, 3), $i(4, 5)));
Assert::equal($set->map(static function (DayOfYearInterval $interval) {
    return $interval->split(2)->getIntervals();
}), $s($i(1, 3), $i(4, 5)));
