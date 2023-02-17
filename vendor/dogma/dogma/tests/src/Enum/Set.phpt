<?php declare(strict_types = 1);

namespace Dogma\Tests\Enum;

use Dogma\Enum\IntSet;
use Dogma\InvalidTypeException;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

// root
class J extends IntSet
{

    public const ONE = 1;
    public const TWO = 2;
    public const FOUR = 4;
    public const EIGHT = 8;

}

class JK extends J
{

    public const ONE = 1;
    public const TWO = 2;
    public const FOUR = 4;

}

class JKL extends JK
{

    public const ONE = 1;
    public const TWO = 2;

}

// negative
class JKM extends JK
{

    /** @removed @deprecated */
    public const FOUR = 4;

}

// foreign
class N extends IntSet
{

    public const ONE = 1;
    public const TWO = 2;

}

$jOne = J::get(1);
$jOne2 = J::get(1);
$jTwo = J::get(2);
$jThree = J::get(4);
$jFour = J::get(8);
$jkOne = JK::get(1);
$jklOne = JKL::get(1);
$jklTwo = JKL::get(2);
$jkmOne = JKM::get(1);
$nOne = N::get(1);

$jEmpty = J::get();
$jOneTwo = J::get(1, 2);
$jOneTwo2 = J::get(1, 2);
$jOneTwoFour = J::get(1, 2, 4);
$jOneFour = J::get(1, 4);
$jOneEight = J::get(1, 8);
$jAll = J::all();
$jTwoFour = J::get(2, 4);
$jkOneTwo = JK::get(1, 2);
$jklOneTwo = JKL::get(1, 2);
$jkmOneTwo = JKM::get(1, 2);
$nOneTwo = N::get(1, 2);


get:
Assert::type($jOne, J::class);
Assert::equal($jOne, $jOne2);


getValue:
Assert::same($jOne->getValue(), 1);
Assert::same($jOneTwo->getValue(), 3);


getValues:
Assert::same($jOne->getValues(), [1]);
Assert::same($jOneTwo->getValues(), [1, 2]);


getConstantNames:
Assert::same($jOne->getConstantNames(), [1 => 'ONE']);
Assert::same($jOneTwo->getConstantNames(), [1 => 'ONE', 2 => 'TWO']);


equals:
Assert::exception(static function () use ($jOne, $nOne): void {
    $jOne->equals($nOne);
}, InvalidTypeException::class);
Assert::false($jOne->equals($jTwo));
Assert::true($jOne->equals($jOne2));
Assert::true($jOne->equals($jkOne));
Assert::true($jOne->equals($jklOne));
Assert::true($jkOne->equals($jklOne));
Assert::true($jkOne->equals($jkmOne));
Assert::true($jklOne->equals($jkmOne));

Assert::exception(static function () use ($jOneTwo, $nOneTwo): void {
    $jOneTwo->equals($nOneTwo);
}, InvalidTypeException::class);
Assert::false($jOneTwo->equals($jTwoFour));
Assert::true($jOneTwo->equals($jOneTwo2));
Assert::true($jOneTwo->equals($jkOneTwo));
Assert::true($jOneTwo->equals($jklOneTwo));
Assert::true($jkOneTwo->equals($jklOneTwo));
Assert::true($jkOneTwo->equals($jkmOneTwo));
Assert::true($jklOneTwo->equals($jkmOneTwo));


equalsValue:
Assert::false($jOne->equalsValue(2));
Assert::true($jOne->equalsValue(1));
Assert::false($jOneTwo->equalsValue(6));
Assert::true($jOneTwo->equalsValue(3));


isValid:
Assert::false(J::isValid(5));
Assert::true(J::isValid(1));


getAllowedValues:
Assert::same(J::getAllowedValues(), [
    'ONE' => 1,
    'TWO' => 2,
    'FOUR' => 4,
    'EIGHT' => 8,
]);

Assert::same(JK::getAllowedValues(), [
    'ONE' => 1,
    'TWO' => 2,
    'FOUR' => 4,
]);

Assert::same(JKL::getAllowedValues(), [
    'ONE' => 1,
    'TWO' => 2,
]);

Assert::same(JKM::getAllowedValues(), [
    'ONE' => 1,
    'TWO' => 2,
]);


getInstances:
Assert::equal(J::getInstances(), [
    'ONE' => $jOne,
    'TWO' => $jTwo,
    'FOUR' => $jThree,
    'EIGHT' => $jFour,
]);

Assert::equal(JKL::getInstances(), [
    'ONE' => $jklOne,
    'TWO' => $jklTwo,
]);


invert:
Assert::equal($jTwoFour->invert(), $jOneEight);
Assert::equal($jEmpty->invert(), $jAll);
Assert::equal($jAll->invert(), $jEmpty);


contains:
Assert::false($jOneTwo->contains($jTwoFour));
Assert::true($jOneTwo->contains($jOneTwo2));
Assert::true($jOneTwo->contains($jOne));
Assert::true($jOneTwo->contains($jEmpty));


intersects:
Assert::false($jOne->intersects($jTwo));
Assert::false($jOne->intersects($jEmpty));
Assert::true($jOneTwo->intersects($jTwoFour));


intersect:
Assert::equal($jOneTwo->intersect($jTwoFour), $jTwo);
Assert::equal($jOneTwo->intersect($jEmpty), $jEmpty);


union:
Assert::equal($jEmpty->union($jOne), $jOne);
Assert::equal($jOne->union($jTwo), $jOneTwo);
Assert::equal($jOneTwo->union($jTwoFour), $jOneTwoFour);


subtract:
Assert::equal($jOneTwo->subtract($jOne), $jTwo);
Assert::equal($jOneTwo->subtract($jEmpty), $jOneTwo);
Assert::equal($jEmpty->subtract($jOne), $jEmpty);
Assert::equal($jOneTwo->subtract($jEmpty), $jOneTwo);


difference:
Assert::equal($jOneTwo->difference($jTwoFour), $jOneFour);
Assert::equal($jEmpty->difference($jEmpty), $jEmpty);
Assert::equal($jEmpty->difference($jAll), $jAll);
Assert::equal($jAll->difference($jAll), $jEmpty);
Assert::equal($jAll->difference($jEmpty), $jAll);


containsAll:
Assert::false($jOneTwo->containsAll(2, 4));
Assert::true($jOneTwo->containsAll(1, 2));
Assert::true($jAll->containsAll(1, 2, 4, 8));


containsAny:
Assert::false($jOneTwo->containsAny(4, 8));
Assert::true($jOneTwo->containsAny(2, 4));
Assert::false($jEmpty->containsAny(1, 2, 4, 8));


filter:
Assert::equal($jOneTwo->filter(1, 4), $jOne);
Assert::equal($jAll->filter(1, 2), $jOneTwo);
Assert::equal($jAll->filter(), $jEmpty);
Assert::equal($jEmpty->filter(1, 2), $jEmpty);


add:
Assert::equal($jOneTwo->add(2, 4), $jOneTwoFour);
Assert::equal($jOneTwo->add(4, 8), $jAll);


remove:
Assert::equal($jAll->remove(4, 8), $jOneTwo);
Assert::equal($jOneTwo->remove(2, 4), $jOne);


_xor:
Assert::equal($jOneTwo->xor(2, 4), $jOneFour);
Assert::equal($jEmpty->xor(), $jEmpty);
Assert::equal($jEmpty->xor(1, 2, 4, 8), $jAll);
Assert::equal($jAll->xor(1, 2, 4, 8), $jEmpty);
Assert::equal($jAll->xor(), $jAll);
