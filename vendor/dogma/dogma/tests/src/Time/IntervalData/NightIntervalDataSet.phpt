<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use Dogma\Call;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\Interval\NightInterval;
use Dogma\Time\Interval\NightIntervalSet;
use Dogma\Time\IntervalData\NightIntervalData;
use Dogma\Time\IntervalData\NightIntervalDataSet;

require_once __DIR__ . '/../../bootstrap.php';

$d = static function (int $day): Date {
    return Date::createFromComponents(2000, 1, $day);
};
$i = static function (string $days) use ($d): NightInterval {
    [$start, $end] = explode('-', $days);
    return new NightInterval($d((int) $start), $d((int) $end));
};
$s = static function (string $days) use ($i): NightIntervalSet {
    $intervals = [];
    foreach (explode(',', $days) as $startEnd) {
        [$startEnd, $data] = explode('/', $startEnd . '/');
        $intervals[] = $i($startEnd);
    }
    return new NightIntervalSet($intervals);
};
$di = static function (string $days, $data = null) use ($d): NightIntervalData {
    [$start, $end] = explode('-', $days);
    return new NightIntervalData($d((int) $start), $d((int) $end), $data ?? 1);
};
$ds = static function (string $days) use ($di): NightIntervalDataSet {
    $intervals = [];
    foreach (explode(',', $days) as $startEnd) {
        [$startEnd, $data] = explode('/', $startEnd . '/');
        $intervals[] = $di($startEnd, $data ? (int) $data : 1);
    }
    return new NightIntervalDataSet($intervals);
};

$interval = new NightIntervalData($d(1), $d(5), 1);
$emptyInterval = NightInterval::empty();
$set = new NightIntervalDataSet([$interval]);


toDateDataArray:
Assert::equal($emptyInterval->toDateArray(), []);
Assert::equal($interval->toDateDataArray(), [[$d(1), 1], [$d(2), 1], [$d(3), 1], [$d(4), 1]]);
Assert::equal($ds('1-3, 4-6')->toDateDataArray(), [[$d(1), 1], [$d(2), 1], [$d(4), 1], [$d(5), 1]]);


isEmpty:
Assert::true((new NightIntervalSet([]))->isEmpty());
Assert::true((new NightIntervalSet([$emptyInterval]))->isEmpty());


equals:
Assert::true($set->equals($ds('1-5')));
Assert::false($set->equals($ds('1-6')));


containsValue:
Assert::true($set->containsValue($d(1)));
Assert::true($set->containsValue($d(4)));
Assert::false($set->containsValue($d(5)));


normalize:
Assert::equal($ds('1-4, 2-5')->normalize(), $set);
Assert::equal($ds('10-14, 5-10, 18-22, 5-7, 15-20')->normalize(), $ds('5-14, 15-22'));


add:
Assert::equal($ds('1-2, 3-4, 5-6'), $ds('1-2')->add($ds('3-4, 5-6')));


subtract:
Assert::equal($ds('1-11')->subtract($s('3-5, 7-9')), $ds('1-3, 5-7, 9-11'));


intersect:
Assert::equal($ds('1-5, 10-15')->intersect($s('4-12, 14-20')), $ds('4-5, 10-12, 14-15'));


map:
Assert::equal($set->map(static function (NightIntervalData $interval) {
    return $interval;
}), $set);
Assert::equal($set->map(static function (NightIntervalData $interval) use ($i) {
    return $interval->subtract($i('3-4'));
}), $ds('1-3, 4-5'));
Assert::equal($set->map(static function (NightIntervalData $interval) use ($i) {
    return $interval->subtract($i('3-4'))->getIntervals();
}), $ds('1-3, 4-5'));


collect:


collectData:

$reducer = static function (int $state, int $change): int {
    return $state + $change;
};


modifyData:
Call::withArgs(static function ($orig, $input, $output, $i) use ($ds, $reducer): void {
    $orig = $ds($orig);
    $input = $ds($input);
    $output = $ds($output);
    Assert::equal($orig->modifyData($input, $reducer)->normalize(), $output, (string) $i);
}, [
    ['10-16', '20-26', '10-16'], // no match
    ['10-16', '10-16', '10-16/2'], // same
    ['10-16', '10-13', '10-13/2, 13-16'], // same start
    ['10-16', '13-16', '10-13, 13-16/2'], // same end
    ['10-16', ' 5-13', '10-13/2, 13-16'], // overlaps start
    ['10-16', '13-21', '10-13, 13-16/2'], // overlaps end
    ['10-16', '12-14', '10-12, 12-14/2, 14-16'], // in middle
    ['10-16', ' 5-21', '10-16/2'], // overlaps whole

    ['10-16, 20-26', '10-26', '10-16/2, 20-26/2'], // envelope
    ['10-16, 20-26', '10-23', '10-16/2, 20-23/2, 23-26'], // same start
    ['10-16, 20-26', '13-26', '10-13, 13-16/2, 20-26/2'], // same end
    ['10-16, 20-26', ' 5-23', '10-16/2, 20-23/2, 23-26'], // overlaps start
    ['10-16, 20-26', '13-26', '10-13, 13-16/2, 20-26/2'], // overlaps end
    ['10-16, 20-26', '13-23', '10-13, 13-16/2, 20-23/2, 23-26'], // in middle
    ['10-16, 20-26', ' 5-31', '10-16/2, 20-26/2'], // overlaps whole
]);

$mapper = static function ($data): array {
    return $data[0]->getStartEnd();
};
$reducer = static function (int $state, $data): int {
    return $state + $data[1];
};


modifyDataByStream:
Call::withArgs(static function ($orig, $input, $output, $i) use ($ds, $di, $mapper, $reducer): void {
    $orig = $ds($orig);
    $input = $di($input);
    $output = $ds($output);
    Assert::equal($orig->modifyDataByStream([[$input, 1]], $mapper, $reducer)->normalize(), $output, (string) $i);
}, [
    ['10-16', '20-26', '10-16'], // no match
    ['10-16', '10-16', '10-16/2'], // same
    ['10-16', '10-13', '10-13/2, 13-16'], // same start
    ['10-16', '13-16', '10-13, 13-16/2'], // same end
    ['10-16', ' 5-13', '10-13/2, 13-16'], // overlaps start
    ['10-16', '13-21', '10-13, 13-16/2'], // overlaps end
    ['10-16', '12-14', '10-12, 12-14/2, 14-16'], // in middle
    ['10-16', ' 5-21', '10-16/2'], // overlaps whole

    ['10-16, 20-26', '10-26', '10-16/2, 20-26/2'], // envelope
    ['10-16, 20-26', '10-23', '10-16/2, 20-23/2, 23-26'], // same start
    ['10-16, 20-26', '13-26', '10-13, 13-16/2, 20-26/2'], // same end
    ['10-16, 20-26', ' 5-23', '10-16/2, 20-23/2, 23-26'], // overlaps start
    ['10-16, 20-26', '13-26', '10-13, 13-16/2, 20-26/2'], // overlaps end
    ['10-16, 20-26', '13-23', '10-13, 13-16/2, 20-23/2, 23-26'], // in middle
    ['10-16, 20-26', ' 5-31', '10-16/2, 20-26/2'], // overlaps whole
]);
