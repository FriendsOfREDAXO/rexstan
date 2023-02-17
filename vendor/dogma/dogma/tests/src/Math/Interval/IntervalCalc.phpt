<?php declare(strict_types = 1);

namespace Dogma\Tests\Math\Interval;

use Dogma\Call;
use Dogma\IntersectResult;
use Dogma\Math\Interval\IntervalCalc;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


compareIntersects:
Call::withArgs(static function (array $boundaries, int $expected, int $case): void {
    Assert::same(IntervalCalc::compareIntersects(...$boundaries), $expected, (string) $case);
}, [
    [[1, 2, 4, 5], IntersectResult::BEFORE_START],
    [[1, 2, 3, 4], IntersectResult::TOUCHES_START],
    [[1, 3, 2, 4], IntersectResult::INTERSECTS_START],
    [[1, 3, 2, 3], IntersectResult::EXTENDS_START],
    [[1, 4, 2, 3], IntersectResult::CONTAINS],
    [[1, 2, 1, 3], IntersectResult::FITS_TO_START],
    [[1, 2, 1, 2], IntersectResult::SAME],
    [[2, 3, 1, 3], IntersectResult::FITS_TO_END],
    [[2, 3, 1, 4], IntersectResult::IS_CONTAINED],
    [[1, 3, 1, 2], IntersectResult::EXTENDS_END],
    [[2, 4, 1, 3], IntersectResult::INTERSECTS_END],
    [[3, 4, 1, 2], IntersectResult::TOUCHES_END],
    [[4, 5, 1, 2], IntersectResult::AFTER_END],
]);
