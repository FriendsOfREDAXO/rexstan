<?php declare(strict_types = 1);

$ignore = [];
// 8.0+
if (PHP_VERSION_ID >= 80000) {
    $ignore[] = '~Parameter #1 \$objectOrClass of class ReflectionClass constructor expects class-string<T of object>\|T of object, string given.~'; # in MethodTypeParser; temporary
    $ignore[] = '~Strict comparison using === between CurlMultiHandle and false will always evaluate to false.~'; # in HttpChannelManager; probably a reflection bug
}
// 7.1 - 8.0
if (PHP_VERSION_ID < 80000) {
    $ignore[] = '~Parameter #1 \$argument of class ReflectionClass constructor expects class-string<T of object>\|T of object, string given.~'; # you know nothing
    $ignore[] = '~Method Dogma\\\\Arr::combine\(\) should return array but returns array\|false.~'; # in Arr
    $ignore[] = '~Parameter #1 \$items of class Dogma\\\\ImmutableArray constructor expects array, array\|false given.~'; # in ImmutableArray
    $ignore[] = '~has unknown class Curl(Multi)?Handle as its type.~'; # PHP 7 -> 8
    $ignore[] = '~has invalid return type Curl(Multi)?Handle~'; # PHP 7 -> 8
}

$excludePaths = [
    '*/tests/*/data/*',
];
if (PHP_VERSION_ID < 70200) {
    // interface changes allowed in later versions, non-fatal, but not able to ignore in phpstan
    $excludePaths[] = '*/Time/DateTime.php';
}

return [
    'parameters' => [
        'ignoreErrors' => $ignore,
        'excludePaths' => [
            'analyse' => $excludePaths,
        ],
    ],
];
