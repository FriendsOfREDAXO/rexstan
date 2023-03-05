<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Tests\Math;

use Dogma\InvalidArgumentException;
use Dogma\Math\ModuloCalc;
use Dogma\Overflow;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


differences:
Assert::same(ModuloCalc::differences([0], 60), [60]);
Assert::same(ModuloCalc::differences([10], 60), [60]);
Assert::same(ModuloCalc::differences([0, 10, 20, 30, 40, 50], 60), [10, 10, 10, 10, 10, 10]);
Assert::same(ModuloCalc::differences([0, 20, 30, 50], 60), [20, 10, 20, 10]);
Assert::throws(static function (): void {
    ModuloCalc::differences([], 60);
}, InvalidArgumentException::class);
Assert::throws(static function (): void {
    ModuloCalc::differences([0, 1, 60], 60);
}, InvalidArgumentException::class);
Assert::throws(static function (): void {
    ModuloCalc::differences([-1, 1, 2], 60);
}, InvalidArgumentException::class);


roundTo:
Assert::same(ModuloCalc::roundTo(3, [0, 10, 20, 30, 40, 50], 60), [0, Overflow::NONE]);
Assert::same(ModuloCalc::roundTo(23, [0, 10, 20, 30, 40, 50], 60), [20, Overflow::NONE]);
Assert::same(ModuloCalc::roundTo(27, [0, 10, 20, 30, 40, 50], 60), [30, Overflow::NONE]);
Assert::same(ModuloCalc::roundTo(53, [0, 10, 20, 30, 40, 50], 60), [50, Overflow::NONE]);
Assert::same(ModuloCalc::roundTo(57, [0, 10, 20, 30, 40, 50], 60), [0, Overflow::OVERFLOW]);
Assert::same(ModuloCalc::roundTo(10, [40, 50], 60), [50, Overflow::UNDERFLOW]);
Assert::same(ModuloCalc::roundTo(50, [10, 20], 60), [10, Overflow::OVERFLOW]);
Assert::throws(static function (): void {
    ModuloCalc::roundTo(3, [], 60);
}, InvalidArgumentException::class);
Assert::throws(static function (): void {
    ModuloCalc::roundTo(3, [0, 1, 60], 60);
}, InvalidArgumentException::class);
Assert::throws(static function (): void {
    ModuloCalc::roundTo(3, [-1, 1, 2], 60);
}, InvalidArgumentException::class);


roundUpTo:
Assert::same(ModuloCalc::roundUpTo(3, [0, 10, 20, 30, 40, 50], 60), [10, Overflow::NONE]);
Assert::same(ModuloCalc::roundUpTo(23, [0, 10, 20, 30, 40, 50], 60), [30, Overflow::NONE]);
Assert::same(ModuloCalc::roundUpTo(27, [0, 10, 20, 30, 40, 50], 60), [30, Overflow::NONE]);
Assert::same(ModuloCalc::roundUpTo(53, [0, 10, 20, 30, 40, 50], 60), [0, Overflow::OVERFLOW]);
Assert::same(ModuloCalc::roundUpTo(57, [0, 10, 20, 30, 40, 50], 60), [0, Overflow::OVERFLOW]);
Assert::same(ModuloCalc::roundUpTo(10, [40, 50], 60), [40, Overflow::NONE]);
Assert::same(ModuloCalc::roundUpTo(50, [10, 20], 60), [10, Overflow::OVERFLOW]);
Assert::throws(static function (): void {
    ModuloCalc::roundUpTo(3, [], 60);
}, InvalidArgumentException::class);
Assert::throws(static function (): void {
    ModuloCalc::roundUpTo(3, [0, 1, 60], 60);
}, InvalidArgumentException::class);
Assert::throws(static function (): void {
    ModuloCalc::roundUpTo(3, [-1, 1, 2], 60);
}, InvalidArgumentException::class);


roundDownTo:
Assert::same(ModuloCalc::roundDownTo(3, [0, 10, 20, 30, 40, 50], 60), [0, Overflow::NONE]);
Assert::same(ModuloCalc::roundDownTo(23, [0, 10, 20, 30, 40, 50], 60), [20, Overflow::NONE]);
Assert::same(ModuloCalc::roundDownTo(27, [0, 10, 20, 30, 40, 50], 60), [20, Overflow::NONE]);
Assert::same(ModuloCalc::roundDownTo(53, [0, 10, 20, 30, 40, 50], 60), [50, Overflow::NONE]);
Assert::same(ModuloCalc::roundDownTo(57, [0, 10, 20, 30, 40, 50], 60), [50, Overflow::NONE]);
Assert::same(ModuloCalc::roundDownTo(10, [40, 50], 60), [50, Overflow::UNDERFLOW]);
Assert::same(ModuloCalc::roundDownTo(50, [10, 20], 60), [20, Overflow::NONE]);
Assert::throws(static function (): void {
    ModuloCalc::roundDownTo(3, [], 60);
}, InvalidArgumentException::class);
Assert::throws(static function (): void {
    ModuloCalc::roundDownTo(3, [0, 1, 60], 60);
}, InvalidArgumentException::class);
Assert::throws(static function (): void {
    ModuloCalc::roundDownTo(3, [-1, 1, 2], 60);
}, InvalidArgumentException::class);
