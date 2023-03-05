<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use DateTimeImmutable;
use Dogma\Math\Vector\Vector3i;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\Interval\DateInterval;
use Dogma\Time\IntervalData\DateIntervalData;
use Dogma\Time\IntervalData\DateIntervalDataSet;
use Dogma\Time\InvalidIntervalStartEndOrderException;
use Dogma\Time\Span\DateSpan;
use Dogma\Time\Span\DateTimeSpan;

require_once __DIR__ . '/../../bootstrap.php';

$data = new Vector3i(1, 2, 3);
$startDate = new Date('2000-01-10');
$endDate = new Date('2000-01-20');
$interval = new DateIntervalData($startDate, $endDate, $data);
$emptyData = DateIntervalData::empty();
$emptyInterval = DateInterval::empty();
$allData = DateIntervalData::all($data);
$allInterval = DateInterval::all();

$d = static function (int $day): Date {
    return new Date('2000-01-' . $day);
};
$i = static function (int $start, int $end): DateInterval {
    return new DateInterval(new Date('2000-01-' . $start), new Date('2000-01-' . $end));
};
$di = static function (int $start, int $end) use ($data): DateIntervalData {
    return new DateIntervalData(new Date('2000-01-' . $start), new Date('2000-01-' . $end), $data);
};
$s = static function (DateIntervalData ...$items): DateIntervalDataSet {
    return new DateIntervalDataSet($items);
};


__construct:
Assert::exception(static function () use ($data): void {
    new DateIntervalData(new Date('today'), new Date('yesterday'), $data);
}, InvalidIntervalStartEndOrderException::class);



toDateInterval:
Assert::equal($interval->toDateInterval(), DateInterval::createFromString('2000-01-10 - 2000-01-20'));


toDateDataArray:
Assert::equal($di(1, 1)->toDateDataArray(), [[$d(1), $data]]);
Assert::equal($di(1, 2)->toDateDataArray(), [[$d(1), $data], [$d(2), $data]]);
Assert::equal($emptyData->toDateDataArray(), []);


shift:
Assert::equal($interval->shift('+1 day'), $di(11, 21));


getStart:
Assert::equal($interval->getStart(), new Date('2000-01-10'));


getEnd:
Assert::equal($interval->getEnd(), new Date('2000-01-20'));


getSpan:
Assert::equal($interval->getSpan(), new DateTimeSpan(0, 0, 10));


getDateSpan:
Assert::equal($interval->getDateSpan(), new DateSpan(0, 0, 10));


getLengthInDays:
Assert::same($interval->getLengthInDays(), 10);
Assert::same($emptyData->getLengthInDays(), 0);


getDayCount:
Assert::same($interval->getDayCount(), 11);
Assert::same($emptyData->getDayCount(), 0);


isEmpty:
Assert::false($interval->isEmpty());
Assert::false($allData->isEmpty());
Assert::true($emptyData->isEmpty());


equals:
Assert::true($interval->equals($di(10, 20)));
Assert::false($interval->equals($di(10, 15)));
Assert::false($interval->equals($di(15, 20)));


compare:
Assert::same($interval->compare($di(10, 19)), 1);
Assert::same($interval->compare($di(10, 21)), -1);
Assert::same($interval->compare($interval), 0);
Assert::same($interval->compare($di(9, 19)), 1);
Assert::same($interval->compare($di(11, 21)), -1);


containsValue:
Assert::true($interval->containsValue($d(10)));
Assert::true($interval->containsValue($d(15)));
Assert::true($interval->containsValue($d(20)));
Assert::false($interval->containsValue($d(5)));
Assert::false($interval->containsValue($d(25)));
Assert::true($interval->containsValue(new DateTimeImmutable('2000-01-15')));


contains:
Assert::true($interval->contains($i(10, 20)));
Assert::true($interval->contains($i(10, 15)));
Assert::true($interval->contains($i(15, 20)));
Assert::false($interval->contains($i(5, 20)));
Assert::false($interval->contains($i(10, 25)));
Assert::false($interval->contains($i(1, 5)));
Assert::false($interval->contains($emptyInterval));


intersects:
Assert::true($interval->intersects($i(5, 25)));
Assert::true($interval->intersects($i(10, 20)));
Assert::true($interval->intersects($i(5, 15)));
Assert::true($interval->intersects($i(15, 25)));
Assert::false($interval->intersects($i(1, 5)));
Assert::false($interval->intersects($emptyInterval));


touches:
Assert::true($interval->touches($i(1, 9)));
Assert::true($interval->touches($i(21, 25)));
Assert::false($interval->touches($i(1, 10)));
Assert::false($interval->touches($i(20, 25)));
Assert::false($interval->touches($emptyInterval));


intersect:
Assert::equal($interval->intersect($i(1, 15)), $di(10, 15));
Assert::equal($interval->intersect($i(15, 30)), $di(15, 20));
Assert::equal($interval->intersect($i(1, 18), $i(14, 30)), $di(14, 18));
Assert::equal($interval->intersect($i(1, 5)), $emptyData);
Assert::equal($interval->intersect($i(1, 5), $i(5, 15)), $emptyData);
Assert::equal($interval->intersect($emptyInterval), $emptyData);


subtract:
Assert::equal($interval->subtract($i(5, 15)), $s($di(16, 20)));
Assert::equal($interval->subtract($i(15, 25)), $s($di(10, 14)));
Assert::equal($interval->subtract($i(13, 17)), $s($di(10, 12), $di(18, 20)));
Assert::equal($interval->subtract($i(5, 10), $i(20, 25)), $s($di(11, 19)));
Assert::equal($interval->subtract($emptyInterval), $s($interval));
Assert::equal($interval->subtract($allInterval), $s());
Assert::equal($allData->subtract($emptyInterval), $s($allData));
Assert::equal($emptyData->subtract($emptyInterval), $s());
