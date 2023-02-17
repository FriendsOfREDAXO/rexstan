<?php declare(strict_types = 1);

namespace Dogma\Tests\Enum;

use Dogma\Enum\IntEnum;
use Dogma\InvalidTypeException;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

// root
class A extends IntEnum
{

    public const ONE = 1;
    public const TWO = 2;
    public const THREE = 3;
    public const FOUR = 4;

}

class AB extends A
{

    public const ONE = 1;
    public const TWO = 2;
    public const THREE = 3;

}

class ABC extends AB
{

    public const ONE = 1;
    public const TWO = 2;

}

// negative
class ABD extends AB
{

    /** @removed @deprecated */
    public const THREE = 3;

}

// foreign
class E extends IntEnum
{

    public const ONE = 1;
    public const TWO = 2;

}

// skip abstract
abstract class F extends IntEnum
{

}

class G extends F
{

    public const ONE = 1;
    public const TWO = 2;

}

$aOne = A::get(1);
$aOne2 = A::get(1);
$aTwo = A::get(2);
$aThree = A::get(3);
$aFour = A::get(4);
$abOne = AB::get(1);
$abcOne = ABC::get(1);
$abcTwo = ABC::get(2);
$abdOne = ABD::get(1);
$eOne = E::get(1);
$gOne = G::get(1);


get:
Assert::type($aOne, A::class);
Assert::equal($aOne, $aOne2);
Assert::equal($gOne, G::get(1));


getValue:
Assert::same($aOne->getValue(), 1);


getConstantName:
Assert::same($aOne->getConstantName(), 'ONE');


equals:
Assert::exception(static function () use ($aOne, $eOne): void {
    $aOne->equals($eOne);
}, InvalidTypeException::class);
Assert::false($aOne->equals($aTwo));
Assert::true($aOne->equals($aOne2));
Assert::true($aOne->equals($abOne));
Assert::true($aOne->equals($abcOne));
Assert::true($abOne->equals($abcOne));
Assert::true($abOne->equals($abdOne));
Assert::true($abcOne->equals($abdOne));


equalsValue:
Assert::false($aOne->equalsValue(2));
Assert::true($aOne->equalsValue(1));


isValid:
Assert::false(A::isValid(5));
Assert::true(A::isValid(1));


getAllowedValues:
Assert::same(A::getAllowedValues(), [
    'ONE' => 1,
    'TWO' => 2,
    'THREE' => 3,
    'FOUR' => 4,
]);

Assert::same(AB::getAllowedValues(), [
    'ONE' => 1,
    'TWO' => 2,
    'THREE' => 3,
]);

Assert::same(ABC::getAllowedValues(), [
    'ONE' => 1,
    'TWO' => 2,
]);

Assert::same(ABD::getAllowedValues(), [
    'ONE' => 1,
    'TWO' => 2,
]);


getInstances:
Assert::equal(A::getInstances(), [
    'ONE' => $aOne,
    'TWO' => $aTwo,
    'THREE' => $aThree,
    'FOUR' => $aFour,
]);

Assert::equal(ABC::getInstances(), [
    'ONE' => $abcOne,
    'TWO' => $abcTwo,
]);
