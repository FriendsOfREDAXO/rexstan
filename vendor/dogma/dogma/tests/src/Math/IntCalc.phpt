<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Tests\Math;

use Dogma\Math\IntCalc;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


roundTo:
Assert::same(IntCalc::roundTo(0, 3), 0);
Assert::same(IntCalc::roundTo(20, 3), 21);
Assert::same(IntCalc::roundTo(22, 3), 21);
Assert::same(IntCalc::roundTo(-20, 3), -21);
Assert::same(IntCalc::roundTo(-22, 3), -21);
Assert::same(IntCalc::roundTo(20, -3), 21);
Assert::same(IntCalc::roundTo(22, -3), 21);


roundUpTo:
Assert::same(IntCalc::roundUpTo(0, 3), 0);
Assert::same(IntCalc::roundUpTo(20, 3), 21);
Assert::same(IntCalc::roundUpTo(22, 3), 24);
Assert::same(IntCalc::roundUpTo(-20, 3), -18);
Assert::same(IntCalc::roundUpTo(-22, 3), -21);
Assert::same(IntCalc::roundUpTo(20, -3), 21);
Assert::same(IntCalc::roundUpTo(22, -3), 24);


roundDownTo:
Assert::same(IntCalc::roundDownTo(0, 3), 0);
Assert::same(IntCalc::roundDownTo(20, 3), 18);
Assert::same(IntCalc::roundDownTo(22, 3), 21);
Assert::same(IntCalc::roundDownTo(-20, 3), -21);
Assert::same(IntCalc::roundDownTo(-22, 3), -24);
Assert::same(IntCalc::roundDownTo(20, -3), 18);
Assert::same(IntCalc::roundDownTo(22, -3), 21);


factorial:
Assert::same(IntCalc::factorial(-4), -24);
Assert::same(IntCalc::factorial(-3), -6);
Assert::same(IntCalc::factorial(-2), -2);
Assert::same(IntCalc::factorial(-1), -1);
Assert::same(IntCalc::factorial(0), 1);
Assert::same(IntCalc::factorial(1), 1);
Assert::same(IntCalc::factorial(2), 2);
Assert::same(IntCalc::factorial(3), 6);
Assert::same(IntCalc::factorial(4), 24);


factorize:
Assert::same(IntCalc::factorize(1), [1]);
Assert::same(IntCalc::factorize(2), [2]);
Assert::same(IntCalc::factorize(3), [3]);
Assert::same(IntCalc::factorize(4), [2, 2]);
Assert::same(IntCalc::factorize(8), [2, 2, 2]);
Assert::same(IntCalc::factorize(60), [2, 2, 3, 5]);
Assert::same(IntCalc::factorize(720), [2, 2, 2, 2, 3, 3, 5]);


greatestCommonDivider:
Assert::same(IntCalc::greatestCommonDivider(1, 1), 1);
Assert::same(IntCalc::greatestCommonDivider(1, 2), 1);
Assert::same(IntCalc::greatestCommonDivider(2, 2), 2);
Assert::same(IntCalc::greatestCommonDivider(2, 3), 1);
Assert::same(IntCalc::greatestCommonDivider(4, 6), 2);
Assert::same(IntCalc::greatestCommonDivider(84, 140), 28);


leastCommonMultiple:
Assert::same(IntCalc::leastCommonMultiple(1, 1), 1);
Assert::same(IntCalc::leastCommonMultiple(1, 2), 2);
Assert::same(IntCalc::leastCommonMultiple(2, 2), 2);
Assert::same(IntCalc::leastCommonMultiple(2, 3), 6);
Assert::same(IntCalc::leastCommonMultiple(4, 6), 12);
Assert::same(IntCalc::leastCommonMultiple(14, 15), 210);


binaryComponents:
Assert::same(IntCalc::binaryComponents(23), [1, 2, 4, 16]);
Assert::same(IntCalc::binaryComponents(0), []);


copyBits:
Assert::same(IntCalc::copyBits(0b00001010, 0b00000101, 0b00001100), 0b00001001);


swapBits:
Assert::same(IntCalc::swapBits(0b00001011, 2, 3), 0b00000111);


countOnes:
Assert::same(IntCalc::countOnes(0b01001011), 4);
Assert::same(IntCalc::countOnes(-1), 64);
Assert::same(IntCalc::countOnes(0), 0);


countBlocks:
Assert::same(IntCalc::countBlocks(0b011100101100), 3);
Assert::same(IntCalc::countBlocks(0b010011101111), 3);
Assert::same(IntCalc::countBlocks(0), 0);
Assert::same(IntCalc::countBlocks(-4294967296), 1);


setTrailingZeros:
Assert::same(IntCalc::flipTrailingZeros(0b01001000), 0b01001111);
Assert::same(IntCalc::flipTrailingZeros(0), -1);
Assert::same(IntCalc::flipTrailingZeros(1), 1);


leastSignificantBit:
Assert::same(IntCalc::leastSignificantBit(0b01001000), 0b00001000);
Assert::same(IntCalc::leastSignificantBit(0b01000000), 0b01000000);
Assert::same(IntCalc::leastSignificantBit(0b00000000), 0b00000000);


leastSignificantBitIndex:
Assert::same(IntCalc::leastSignificantBitIndex(0b01001000), 3);
Assert::same(IntCalc::leastSignificantBitIndex(0b01000000), 6);
Assert::same(IntCalc::leastSignificantBitIndex(0b00000000), -1);


permutateBits:
Assert::same(IntCalc::permutateBits(0b00000111), 0b00001011);
Assert::same(IntCalc::permutateBits(0b00001011), 0b00001101);
Assert::same(IntCalc::permutateBits(0b00001101), 0b00001110);
Assert::same(IntCalc::permutateBits(0b00001110), 0b00010011);
Assert::same(IntCalc::permutateBits(0b00010011), 0b00010101);
Assert::same(IntCalc::permutateBits(0b00010101), 0b00010110);
Assert::same(IntCalc::permutateBits(0b00010110), 0b00011001);
Assert::same(IntCalc::permutateBits(0b00011001), 0b00011010);
