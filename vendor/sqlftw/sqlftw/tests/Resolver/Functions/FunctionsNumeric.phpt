<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Platform\Platform;
use SqlFtw\Resolver\Cast;
use SqlFtw\Resolver\Functions\Functions;
use SqlFtw\Session\Session;
use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';

$platform = Platform::get(Platform::MYSQL, '8.0');
$session = new Session($platform);
$f = new Functions($session, new Cast());

Assert::true(true);

_multiply:
_divide:
_modulo:
_plus:
_minus:
_unary_minus:

abs:
acos:
asin:
atan:
atan2:
ceil:
ceiling:
conv:
cos:
cot:
crc32:
degrees:
div:
exp:
floor:
ln:
log:
log10:
log2:
mod:
pi:
pow:
power:
radians:
rand:
round:
sign:
sin:
sqrt:
tan:
truncate:
