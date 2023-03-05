<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use Dogma\Call;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\Interval\DateInterval;
use Dogma\Time\Interval\DateIntervalSet;
use Dogma\Time\IntervalData\DateIntervalData;
use Dogma\Time\IntervalData\DateIntervalDataSet;
use function explode;

require_once __DIR__ . '/../../bootstrap.php';

$d = static function (int $day): Date {
    return Date::createFromComponents(2000, 1, $day);
};
$i = static function (string $days) use ($d): DateInterval {
    [$start, $end] = explode('-', $days);
    return new DateInterval($d((int) $start), $d((int) $end));
};
$s = static function (string $days) use ($i): DateIntervalSet {
    $intervals = [];
    foreach (explode(',', $days) as $startEnd) {
        [$startEnd, $data] = explode('/', $startEnd . '/');
        $intervals[] = $i($startEnd);
    }
    return new DateIntervalSet($intervals);
};
$di = static function (string $days, $data = null) use ($d): DateIntervalData {
    [$start, $end] = explode('-', $days);
    return new DateIntervalData($d((int) $start), $d((int) $end), $data ?? 1);
};
$ds = static function (string $days) use ($di): DateIntervalDataSet {
    $intervals = [];
    foreach (explode(',', $days) as $startEnd) {
        [$startEnd, $data] = explode('/', $startEnd . '/');
        $intervals[] = $di($startEnd, $data ? (int) $data : 1);
    }
    return new DateIntervalDataSet($intervals);
};

$interval = new DateIntervalData($d(1), $d(5), 1);
$emptyInterval = DateInterval::empty();
$set = new DateIntervalDataSet([$interval]);


toDateDataArray:
Assert::equal($emptyInterval->toDateArray(), []);
Assert::equal($interval->toDateDataArray(), [[$d(1), 1], [$d(2), 1], [$d(3), 1], [$d(4), 1], [$d(5), 1]]);
Assert::equal($ds('1-2, 4-5')->toDateDataArray(), [[$d(1), 1], [$d(2), 1], [$d(4), 1], [$d(5), 1]]);


getIntervals:
getIterator:
Assert::same($set->getIntervals(), iterator_to_array($set->getIterator()));


isEmpty:
Assert::true((new DateIntervalSet([]))->isEmpty());
Assert::true((new DateIntervalSet([$emptyInterval]))->isEmpty());


equals:
Assert::true($set->equals($ds('1-5')));
Assert::false($set->equals($ds('1-6')));


containsValue:
Assert::true($set->containsValue($d(1)));
Assert::true($set->containsValue($d(5)));
Assert::false($set->containsValue($d(6)));


normalize:
Assert::equal($ds('1-4, 2-5')->normalize(), $set);
Assert::equal($ds('10-13, 5-9, 18-21, 5-6, 15-19')->normalize(), $ds('5-13, 15-21'));


add:
Assert::equal($ds('1-2')->add($ds('3-4, 5-6')), $ds('1-2, 3-4, 5-6'));


subtract:
Assert::equal($ds('1-10')->subtract($s('3-4, 7-8')), $ds('1-2, 5-6, 9-10'));


intersect:
Assert::equal($ds('1-5, 10-15')->intersect($s('4-12, 14-20')), $ds('4-5, 10-12, 14-15'));


map:
Assert::equal($set->map(static function (DateIntervalData $interval) {
    return $interval;
}), $set);
Assert::equal($set->map(static function (DateIntervalData $interval) use ($i) {
    return $interval->subtract($i('3-3'));
}), $ds('1-2, 4-5'));
Assert::equal($set->map(static function (DateIntervalData $interval) use ($i) {
    return $interval->subtract($i('3-3'))->getIntervals();
}), $ds('1-2, 4-5'));


collect:
// todo


collectData:
// todo


modifyData:
$reducer = static function (int $state, $data): int {
    return $state + $data;
};
Call::withArgs(static function ($orig, $input, $output, $i) use ($ds, $reducer): void {
    $orig = $ds($orig);
    $input = $ds($input);
    $output = $ds($output);
    Assert::equal($orig->modifyData($input, $reducer)->normalize(), $output, (string) $i);
}, [
    // data after "/", default 1
    ['10-15', '20-25', '10-15/1'], // no match
    ['10-15', '10-15', '10-15/2'], // same
    ['10-15', '10-12', '10-12/2, 13-15/1'], // same start
    ['10-15', '13-15', '10-12/1, 13-15/2'], // same end
    ['10-15', ' 5-12', '10-12/2, 13-15/1'], // overlaps start
    ['10-15', '13-20', '10-12/1, 13-15/2'], // overlaps end
    ['10-15', '12-13', '10-11/1, 12-13/2, 14-15/1'], // in middle
    ['10-15', ' 5-20', '10-15/2'], // overlaps whole

    ['10-15, 20-25', '10-25', '10-15/2, 20-25/2'], // envelope
    ['10-15, 20-25', '10-22', '10-15/2, 20-22/2, 23-25'], // same start
    ['10-15, 20-25', '13-25', '10-12, 13-15/2, 20-25/2'], // same end
    ['10-15, 20-25', ' 5-22', '10-15/2, 20-22/2, 23-25'], // overlaps start
    ['10-15, 20-25', '13-25', '10-12, 13-15/2, 20-25/2'], // overlaps end
    ['10-15, 20-25', '13-22', '10-12, 13-15/2, 20-22/2, 23-25'], // in middle
    ['10-15, 20-25', ' 5-30', '10-15/2, 20-25/2'], // overlaps whole
]);


modifyDataByStream:
$data = static function (string $days) use ($i): array {
    $intervals = [];
    foreach (explode(',', $days) as $startEnd) {
        [$startEnd, $data] = explode('/', $startEnd . '/');
        $intervals[] = [$i($startEnd), $data ? (int) $data : 1];
    }
    return $intervals;
};
$mapper = static function ($data): array {
    return $data[0]->getStartEnd();
};
$reducer = static function (int $state, $data): int {
    return $state + $data[1];
};

Call::withArgs(static function ($orig, $input, $output, $i) use ($ds, $data, $mapper, $reducer): void {
    $orig = $ds($orig);
    $input = $data($input);
    $output = $ds($output);
    Assert::equal($orig->modifyDataByStream($input, $mapper, $reducer)->normalize(), $output, (string) $i);
}, [
    // data after "/", default 1
    ['10-15', '20-25', '10-15/1'], // no match
    ['10-15', '10-15', '10-15/2'], // same
    ['10-15', '10-12', '10-12/2, 13-15/1'], // same start
    ['10-15', '13-15', '10-12/1, 13-15/2'], // same end
    ['10-15', ' 5-12', '10-12/2, 13-15/1'], // overlaps start
    ['10-15', '13-20', '10-12/1, 13-15/2'], // overlaps end
    ['10-15', '12-13', '10-11/1, 12-13/2, 14-15/1'], // in middle
    ['10-15', ' 5-20', '10-15/2'], // overlaps whole

    ['10-15, 20-25', '10-25', '10-15/2, 20-25/2'], // envelope
    ['10-15, 20-25', '10-22', '10-15/2, 20-22/2, 23-25/1'], // same start
    ['10-15, 20-25', '13-25', '10-12/1, 13-15/2, 20-25/2'], // same end
    ['10-15, 20-25', ' 5-22', '10-15/2, 20-22/2, 23-25/1'], // overlaps start
    ['10-15, 20-25', '13-25', '10-12/1, 13-15/2, 20-25/2'], // overlaps end
    ['10-15, 20-25', '13-22', '10-12/1, 13-15/2, 20-22/2, 23-25/1'], // in middle
    ['10-15, 20-25', ' 5-30', '10-15/2, 20-25/2'], // overlaps whole

    // overlaps in inputs
    ['1-30', '5-25, 10-20', '1-4/1, 5-9/2, 10-20/3, 21-25/2, 26-30/1'],
    ['1-30', '5-20, 10-25', '1-4/1, 5-9/2, 10-20/3, 21-25/2, 26-30/1'],
    ['1-30', '1-30, 4-6, 5-7, 14-15, 20-26, 27-30', '1-3/2, 4-4/3, 5-6/4, 7-7/3, 8-13/2, 14-15/3, 16-19/2, 20-30/3'],
]);
