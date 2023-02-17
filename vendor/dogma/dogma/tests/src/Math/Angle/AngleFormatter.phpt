<?php declare(strict_types = 1);

namespace Dogma\Tests\Math;

use Dogma\Math\Angle\AngleFormatter;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// 87:45:54.765432
$angle = 87 + 45 / 60 + 54.765432 / 3600;
$angleFormatter = new AngleFormatter();


Assert::same($angleFormatter->format($angle, 'd'), '87');
Assert::same($angleFormatter->format($angle, 'D'), '87.765213');
Assert::same($angleFormatter->format($angle, 'D', 4), '87.7652');
Assert::same($angleFormatter->format($angle, 'D', 4, ','), '87,7652');
Assert::same($angleFormatter->format($angle, 'm'), '45');
Assert::same($angleFormatter->format($angle, 'M'), '45.912757');
Assert::same($angleFormatter->format($angle, 'M', 4), '45.9128');
Assert::same($angleFormatter->format($angle, 'M', 4, ','), '45,9128');
Assert::same($angleFormatter->format($angle, 's'), '54');
Assert::same($angleFormatter->format($angle, 'S'), '54.765432');
Assert::same($angleFormatter->format($angle, 'S', 4), '54.7654');
Assert::same($angleFormatter->format($angle, 'S', 4, ','), '54,7654');

Assert::same($angleFormatter->format($angle, AngleFormatter::FORMAT_PRETTY, 2), '87˚45′54.77″');
Assert::same($angleFormatter->format($angle, AngleFormatter::FORMAT_DEFAULT, 2), '87:45:54.77');
Assert::same($angleFormatter->format(-$angle, AngleFormatter::FORMAT_DEFAULT, 2), '-87:45:54.77');
Assert::same($angleFormatter->format(0.0, AngleFormatter::FORMAT_DEFAULT, 2), '0:0:0');
