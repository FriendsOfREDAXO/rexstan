<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use DateTimeImmutable;
use Dogma\Math\Vector\Vector3i;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\Interval\NightInterval;
use Dogma\Time\IntervalData\NightIntervalData;
use Dogma\Time\IntervalData\NightIntervalDataSet;
use Dogma\Time\InvalidIntervalStartEndOrderException;
use Dogma\Time\Span\DateSpan;
use Dogma\Time\Span\DateTimeSpan;

require_once __DIR__ . '/../../bootstrap.php';

$data = new Vector3i(1, 2, 3);
$startDate = new Date('2000-01-10');
$endDate = new Date('2000-01-21');
$interval = new NightIntervalData($startDate, $endDate, $data);
$emptyData = NightIntervalData::empty();
$emptyInterval = NightInterval::empty();
$allData = NightIntervalData::all($data);
$allInterval = NightInterval::all();

$d = static function (int $day): Date {
    return new Date('2000-01-' . $day);
};
$i = static function (int $start, int $end): NightInterval {
    return new NightInterval(new Date('2000-01-' . $start), new Date('2000-01-' . $end));
};
$di = static function (int $start, int $end) use ($data): NightIntervalData {
    return new NightIntervalData(new Date('2000-01-' . $start), new Date('2000-01-' . $end), $data);
};
$s = static function (NightIntervalData ...$items): NightIntervalDataSet {
    return new NightIntervalDataSet($items);
};


__construct:
Assert::exception(static function () use ($data): void {
    new NightIntervalData(new Date('today'), new Date('yesterday'), $data);
}, InvalidIntervalStartEndOrderException::class);



toNightInterval:
Assert::equal($interval->toNightInterval(), NightInterval::createFromString('2000-01-10 - 2000-01-21'));


toDateDataArray:
Assert::equal($di(1, 2)->toDateDataArray(), [[$d(1), $data]]);
Assert::equal($di(1, 3)->toDateDataArray(), [[$d(1), $data], [$d(2), $data]]);
Assert::equal($emptyData->toDateDataArray(), []);


shift:
Assert::equal($interval->shift('+1 day'), $di(11, 22));


getStart:
Assert::equal($interval->getStart(), new Date('2000-01-10'));


getEnd:
Assert::equal($interval->getEnd(), new Date('2000-01-21'));


getSpan:
Assert::equal($interval->getSpan(), new DateTimeSpan(0, 0, 11));


getDateSpan:
Assert::equal($interval->getDateSpan(), new DateSpan(0, 0, 11));


getLengthInDays:
Assert::same($i(1, 3)->getLengthInDays(), 2);
Assert::same($interval->getLengthInDays(), 11);
Assert::same($emptyData->getLengthInDays(), 0);


getNightsCount:
Assert::same($interval->getDayCount(), 11);
Assert::same($emptyData->getDayCount(), 0);


isEmpty:
Assert::false($interval->isEmpty());
Assert::false($allData->isEmpty());
Assert::true($emptyData->isEmpty());


equals:
Assert::true($interval->equals($di(10, 21)));
Assert::false($interval->equals($di(10, 15)));
Assert::false($interval->equals($di(15, 21)));


compare:
Assert::same($interval->compare($di(10, 20)), 1);
Assert::same($interval->compare($di(10, 22)), -1);
Assert::same($interval->compare($interval), 0);
Assert::same($interval->compare($di(9, 20)), 1);
Assert::same($interval->compare($di(11, 22)), -1);


containsValue:
Assert::true($interval->containsValue($d(10)));
Assert::true($interval->containsValue($d(15)));
Assert::true($interval->containsValue($d(20)));
Assert::false($interval->containsValue($d(21)));
Assert::false($interval->containsValue($d(5)));
Assert::false($interval->containsValue($d(25)));
Assert::true($interval->containsValue(new DateTimeImmutable('2000-01-15')));


contains:
Assert::true($interval->contains($i(10, 21)));
Assert::true($interval->contains($i(10, 15)));
Assert::true($interval->contains($i(15, 21)));
Assert::false($interval->contains($i(15, 22)));
Assert::false($interval->contains($i(5, 21)));
Assert::false($interval->contains($i(10, 25)));
Assert::false($interval->contains($i(1, 5)));
Assert::false($interval->contains($emptyInterval));


intersects:
Assert::true($interval->intersects($i(5, 25)));
Assert::true($interval->intersects($i(10, 21)));
Assert::true($interval->intersects($i(5, 15)));
Assert::true($interval->intersects($i(15, 25)));
Assert::true($interval->intersects($i(20, 25)));
Assert::false($interval->intersects($i(21, 25)));
Assert::false($interval->intersects($i(1, 5)));
Assert::false($interval->intersects($emptyInterval));


touches:
Assert::false($interval->touches($i(1, 9)));
Assert::true($interval->touches($i(21, 25)));
Assert::true($interval->touches($i(1, 10)));
Assert::false($interval->touches($i(20, 25)));
Assert::true($interval->touches($i(21, 25)));
Assert::false($interval->touches($i(22, 25)));
Assert::false($interval->touches($emptyInterval));


intersect:
Assert::equal($interval->intersect($i(1, 15)), $di(10, 15));
Assert::equal($interval->intersect($i(15, 30)), $di(15, 21));
Assert::equal($interval->intersect($i(1, 18), $i(14, 30)), $di(14, 18));
Assert::equal($interval->intersect($i(1, 5)), $emptyData);
Assert::equal($interval->intersect($i(1, 5), $i(5, 15)), $emptyData);
Assert::equal($interval->intersect($emptyInterval), $emptyData);


subtract:
Assert::equal($interval->subtract($i(5, 15)), $s($di(15, 21)));
Assert::equal($interval->subtract($i(15, 25)), $s($di(10, 15)));
Assert::equal($interval->subtract($i(13, 17)), $s($di(10, 13), $di(17, 21)));
Assert::equal($interval->subtract($i(5, 10), $i(20, 25)), $s($di(10, 20)));
Assert::equal($interval->subtract($emptyInterval), $s($interval));
Assert::equal($interval->subtract($allInterval), $s());
Assert::equal($allData->subtract($emptyInterval), $s($allData));
Assert::equal($emptyData->subtract($emptyInterval), $s());
