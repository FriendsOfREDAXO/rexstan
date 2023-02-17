<?php declare(strict_types = 1);

namespace Dogma\Tests\DogmaLoader;

use Dogma\Comparable;
use Dogma\DogmaLoader;
use Dogma\Tester\Assert;
use Dogma\Type;
use function dirname;

require_once __DIR__ . '/../bootstrap.php';

$loader = DogmaLoader::getInstance();
$classMap = $loader->getClassMap();

Assert::same(
    $classMap[DogmaLoader::class],
    sprintf(
        '%s%ssrc%scommon%sDogmaLoader.php',
        dirname(__DIR__, 3),
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR
    )
);

Assert::same(
    $classMap[Type::class],
    sprintf(
        '%s%ssrc%scommon%sType.php',
        dirname(__DIR__, 3),
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR
    )
);

Assert::same(
    $classMap[Comparable::class],
    sprintf(
        '%s%ssrc%scommon%sinterfaces%sComparable.php',
        dirname(__DIR__, 3),
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR
    )
);
