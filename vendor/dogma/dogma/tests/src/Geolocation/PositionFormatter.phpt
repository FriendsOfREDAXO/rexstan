<?php declare(strict_types = 1);

namespace Dogma\Tests\Math;

use Dogma\Geolocation\Position;
use Dogma\Geolocation\PositionFormatter;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

$position = new Position(-15, -50);
$positionFormatter = new PositionFormatter();


Assert::same($positionFormatter->format($position, 'l'), '15');
Assert::same($positionFormatter->format($position, 'L'), '-15');
Assert::same($positionFormatter->format($position, 'n'), 'S');
Assert::same($positionFormatter->format($position, 'N'), 'south');
Assert::same($positionFormatter->format($position, 'o'), '50');
Assert::same($positionFormatter->format($position, 'O'), '-50');
Assert::same($positionFormatter->format($position, 'e'), 'W');
Assert::same($positionFormatter->format($position, 'E'), 'west');

Assert::same($positionFormatter->format($position, PositionFormatter::FORMAT_PRETTY), 'S15,W50');
Assert::same($positionFormatter->format($position, PositionFormatter::FORMAT_DEFAULT), '-15,-50');
