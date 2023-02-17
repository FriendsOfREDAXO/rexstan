<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Tests\Math;

use Dogma\Math\FloatCalc;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


roundTo:
Assert::same(FloatCalc::roundTo(0.0, 0.25), 0.0);
Assert::same(FloatCalc::roundTo(2.1, 0.25), 2.0);
Assert::same(FloatCalc::roundTo(2.2, 0.25), 2.25);
Assert::same(FloatCalc::roundTo(-2.1, 0.25), -2.0);
Assert::same(FloatCalc::roundTo(-2.2, 0.25), -2.25);
Assert::same(FloatCalc::roundTo(2.1, -0.25), 2.0);
Assert::same(FloatCalc::roundTo(2.2, -0.25), 2.25);


roundUpTo:
Assert::same(FloatCalc::roundUpTo(0.0, 0.25), 0.0);
Assert::same(FloatCalc::roundUpTo(2.1, 0.25), 2.25);
Assert::same(FloatCalc::roundUpTo(2.2, 0.25), 2.25);
Assert::same(FloatCalc::roundUpTo(-2.1, 0.25), -2.0);
Assert::same(FloatCalc::roundUpTo(-2.2, 0.25), -2.0);
Assert::same(FloatCalc::roundUpTo(2.1, -0.25), 2.25);
Assert::same(FloatCalc::roundUpTo(2.2, -0.25), 2.25);


roundDownTo:
Assert::same(FloatCalc::roundDownTo(0.0, 0.25), 0.0);
Assert::same(FloatCalc::roundDownTo(2.1, 0.25), 2.0);
Assert::same(FloatCalc::roundDownTo(2.2, 0.25), 2.0);
Assert::same(FloatCalc::roundDownTo(-2.1, 0.25), -2.25);
Assert::same(FloatCalc::roundDownTo(-2.2, 0.25), -2.25);
Assert::same(FloatCalc::roundDownTo(2.1, -0.25), 2.0);
Assert::same(FloatCalc::roundDownTo(2.2, -0.25), 2.0);
