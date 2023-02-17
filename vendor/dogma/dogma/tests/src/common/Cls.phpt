<?php declare(strict_types = 1);

namespace Dogma\Tests\Cls;

use Dogma\Cls;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


class A
{

}

class AB extends A
{

}

class AC extends A
{

}

class ABD extends AB
{

}

class ABE extends AB
{

}

class ACF extends AC
{

}

class G
{

}


commonRoot:
Assert::same(Cls::commonRoot(new A(), new AB()), A::class);
Assert::same(Cls::commonRoot(new A(), new ABD()), A::class);
Assert::same(Cls::commonRoot(new AB(), new AC()), A::class);
Assert::same(Cls::commonRoot(new AB(), new ABD()), A::class);
Assert::same(Cls::commonRoot(new ABD(), new ABE()), A::class);
Assert::same(Cls::commonRoot(new ABD(), new ACF()), A::class);
Assert::same(Cls::commonRoot(new ABD(), new G()), null);


commonBranch:
Assert::same(Cls::commonBranch(new A(), new AB()), A::class);
Assert::same(Cls::commonBranch(new A(), new ABD()), A::class);
Assert::same(Cls::commonBranch(new AB(), new AC()), A::class);
Assert::same(Cls::commonBranch(new AB(), new ABD()), AB::class);
Assert::same(Cls::commonBranch(new ABD(), new ABE()), AB::class);
Assert::same(Cls::commonBranch(new ABD(), new ACF()), A::class);
Assert::same(Cls::commonBranch(new ABD(), new G()), null);
