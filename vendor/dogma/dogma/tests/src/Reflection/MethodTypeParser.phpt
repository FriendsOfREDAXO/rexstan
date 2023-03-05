<?php declare(strict_types = 1);

namespace Dogma\Tests\Reflection;

use DateTime;
use Dogma\Reflection\InvalidMethodAnnotationException;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Reflection\UnprocessableParameterException;
use Dogma\Sign;
use Dogma\Tester\Assert;
use Dogma\Type;
use ReflectionClass;
use SplFixedArray;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/data/MethodTypeParserTestClass.php';


$parser = new MethodTypeParser();
$class = new ReflectionClass(MethodTypeParserTestClass::class);
$rawKeys = ['types', 'nullable', 'reference', 'variadic', 'optional'];

Assert::same(
    $parser->getTypes($class->getMethod('testReturnAnnotation')),
    ['@return' => Type::int()]
);
Assert::same(
    $parser->getTypes($class->getMethod('testReturnAnnotationWithSize')),
    ['@return' => Type::int(64)]
);
Assert::same(
    $parser->getTypes($class->getMethod('testReturnTypehintAndAnnotation')),
    ['@return' => Type::int()]
);
Assert::same(
    $parser->getTypes($class->getMethod('testReturnTypehintAndAnnotationWithSize')),
    ['@return' => Type::int(64)]
);

$test = static function ($methodName, $expectedRaw, $expectedType) use ($parser, $class, $rawKeys): void {
    if (is_string($expectedRaw)) {
        Assert::throws(static function () use ($parser, $class, $methodName): void {
            $parser->getTypesRaw($class->getMethod($methodName));
        }, $expectedRaw);
    } else {
        foreach ($expectedRaw as $name => &$val) {
            $val = array_combine($rawKeys, array_pad($val, 5, false));
        }
        $params = $parser->getTypesRaw($class->getMethod($methodName));
        Assert::same($params, $expectedRaw);
    }
    if (is_string($expectedType)) {
        Assert::throws(static function () use ($parser, $class, $methodName): void {
            $parser->getTypes($class->getMethod($methodName));
        }, $expectedType);
    } else {
        $params = $parser->getParameterTypes($class->getMethod($methodName));
        Assert::same($params, $expectedType);
    }
};

$test(
    'testNoType',
    ['one' => [[]]],
    ['one' => Type::get(Type::MIXED)]
);
$test(
    'testNullable',
    ['one' => [[], true, false, false, true]],
    ['one' => Type::get(Type::MIXED, Type::NULLABLE)]
);
$test(
    'testTwoParams',
    ['one' => [[]], 'two' => [[]]],
    ['one' => Type::get(Type::MIXED), 'two' => Type::get(Type::MIXED)]
);
$test(
    'testArray',
    ['one' => [[Type::PHP_ARRAY]]],
    ['one' => Type::get(Type::PHP_ARRAY)]
);
$test(
    'testCallable',
    ['one' => [[Type::PHP_CALLABLE]]],
    ['one' => Type::get(Type::PHP_CALLABLE)]
);
$test(
    'testClass',
    ['one' => [[DateTime::class]]],
    ['one' => Type::get(DateTime::class)]
);
$test(
    'testSelf',
    ['one' => [[MethodTypeParserTestClass::class]]],
    ['one' => Type::get(MethodTypeParserTestClass::class)]
);
$test(
    'testReference',
    ['one' => [[], false, true]],
    UnprocessableParameterException::class
);
$test(
    'testVariadic',
    ['one' => [[], false, false, true, true]],
    UnprocessableParameterException::class
);
$test(
    'testAnnotationCountMismatch',
    InvalidMethodAnnotationException::class,
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationCountMismatch2',
    InvalidMethodAnnotationException::class,
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationNameMismatch',
    InvalidMethodAnnotationException::class,
    InvalidMethodAnnotationException::class
);
$test(
    'testTypehint',
    ['one' => [[Type::INT]]],
    ['one' => Type::int()]
);
$test(
    'testAnnotation',
    ['one' => [[Type::INT]]],
    ['one' => Type::int()]
);
$test(
    'testTypehintAndAnnotation',
    ['one' => [[Type::INT]]],
    ['one' => Type::int()]
);
$test(
    'testAnnotationWithSize',
    ['one' => [['int(64)']]],
    ['one' => Type::int(64)]
);
$test(
    'testAnnotationWithSizeWithNote',
    ['one' => [['int(64,unsigned)']]],
    ['one' => Type::int(64, Sign::UNSIGNED)]
);
$test(
    'testAnnotationWithSizeWithNoteShort',
    ['one' => [['int(64,unsigned)']]],
    ['one' => Type::int(64, Sign::UNSIGNED)]
);
$test(
    'testTypehintAndAnnotationWithSize',
    ['one' => [[Type::INT, 'int(64)']]],
    ['one' => Type::int(64)]
);
$test(
    'testTypehintAndAnnotationWithSizeWithNote',
    ['one' => [['int(64,unsigned)']]],
    ['one' => Type::int(64, Sign::UNSIGNED)]
);
$test(
    'testTypehintAndAnnotationWithSizeWithNoteShort',
    ['one' => [['int(64,unsigned)']]],
    ['one' => Type::int(64, Sign::UNSIGNED)]
);
$test(
    'testAnnotationNullable',
    ['one' => [[Type::INT], true, false, false, true]],
    ['one' => Type::int(Type::NULLABLE)]
);
$test(
    'testAnnotationWithNull',
    ['one' => [[Type::INT], true]],
    ['one' => Type::int(Type::NULLABLE)]
);
$test(
    'testAnnotationWithSizeWithNull',
    ['one' => [['int(64)'], true]],
    ['one' => Type::int(64, Type::NULLABLE)]
);
$test(
    'testAnnotationWithNullNullable',
    ['one' => [[Type::INT], true, false, false, true]],
    ['one' => Type::int(Type::NULLABLE)]
);
$test(
    'testTypehintAndAnnotationWithSizeWithNullNullable',
    ['one' => [[Type::INT, 'int(64)'], true, false, false, true]],
    ['one' => Type::int(64, Type::NULLABLE)]
);
$test(
    'testAnnotationIncompleteClass',
    InvalidMethodAnnotationException::class,
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationNonExistingClass',
    InvalidMethodAnnotationException::class,
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationClass',
    ['one' => [[DateTime::class]]],
    ['one' => Type::get(DateTime::class)]
);
$test(
    'testAnnotationSelf',
    ['one' => [[MethodTypeParserTestClass::class]]],
    ['one' => Type::get(MethodTypeParserTestClass::class)]
);
$test(
    'testAnnotationStatic',
    ['one' => [[MethodTypeParserTestClass::class]]],
    ['one' => Type::get(MethodTypeParserTestClass::class)]
);
$test(
    'testTypehintAndAnnotationClass',
    ['one' => [[DateTime::class]]],
    ['one' => Type::get(DateTime::class)]
);
$test(
    'testAnnotationWithoutName',
    ['one' => [[Type::INT]], 'two' => [[Type::STRING]]],
    ['one' => Type::int(), 'two' => Type::string()]
);
$test(
    'testAnnotationMoreTypes',
    ['one' => [[Type::INT, Type::STRING]]],
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationDimensionMismatch',
    ['one' => [[DateTime::class, 'int[]']]],
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationArrayBrackets',
    ['one' => [['int[]']]],
    ['one' => Type::arrayOf(Type::INT)]
);
$test(
    'testAnnotationArrayOfType',
    ['one' => [[Type::PHP_ARRAY, 'int[]']]],
    ['one' => Type::arrayOf(Type::INT)]
);
$test(
    'testAnnotationArrayOfTypes',
    ['one' => [[Type::PHP_ARRAY, 'int[]', 'string[]']]],
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationCollectionOfType',
    ['one' => [[SplFixedArray::class, 'int[]']]],
    ['one' => Type::collectionOf(SplFixedArray::class, Type::INT)]
);
$test(
    'testAnnotationCollectionOfTypes',
    ['one' => [[SplFixedArray::class, 'int[]', 'string[]']]],
    InvalidMethodAnnotationException::class
);
