<?php declare(strict_types = 1);

namespace Dogma\Tests\Reflection;

use DateTime;

class MethodTypeParserTestClass
{

    // phpcs:disable

    public function testNoType($one): void
    {
        //
    }

    public function testNullable($one = null): void
    {
        //
    }

    public function testTwoParams($one, $two): void
    {
        //
    }

    public function testArray(array $one): void
    {
        //
    }

    public function testCallable(callable $one): void
    {
        //
    }

    public function testClass(DateTime $one): void
    {
        //
    }

    public function testSelf(self $one): void
    {
        //
    }

    public function testReference(&$one): void
    {
        //
    }

    public function testVariadic(...$one): void
    {
        //
    }

    public function testTypehint(int $one): void
    {
        //
    }

    /**
     * @param int $one
     */
    public function testAnnotation($one): void
    {
        //
    }

    /**
     * @param int $one
     */
    public function testTypehintAndAnnotation(int $one): void
    {
        //
    }

    /**
     * @param int(64) $one
     */
    public function testAnnotationWithSize($one): void
    {
        //
    }

    /**
     * @param int(64,unsigned) $one
     */
    public function testAnnotationWithSizeWithNote($one): void
    {
        //
    }

    /**
     * @param int(64u) $one
     */
    public function testAnnotationWithSizeWithNoteShort($one): void
    {
        //
    }

    /**
     * @param int(64) $one
     */
    public function testTypehintAndAnnotationWithSize(int $one): void
    {
        //
    }

    /**
     * @param int(64,unsigned) $one
     */
    public function testTypehintAndAnnotationWithSizeWithNote($one): void
    {
        //
    }

    /**
     * @param int(64u) $one
     */
    public function testTypehintAndAnnotationWithSizeWithNoteShort($one): void
    {
        //
    }

    /**
     * @param int $one
     */
    public function testAnnotationNullable($one = null): void
    {
        //
    }

    /**
     * @param int|null $one
     */
    public function testAnnotationWithNull($one): void
    {
        //
    }

    /**
     * @param int(64)|null $one
     */
    public function testAnnotationWithSizeWithNull($one): void
    {
        //
    }

    /**
     * @param int|null $one
     */
    public function testAnnotationWithNullNullable($one = null): void
    {
        //
    }

    /**
     * @param int(64)|null $one
     */
    public function testTypehintAndAnnotationWithSizeWithNullNullable(int $one = null): void
    {
        //
    }

    /**
     * @param \DateTime $one
     */
    public function testAnnotationClass($one): void
    {
        //
    }

    /**
     * @param self $one
     */
    public function testAnnotationSelf($one): void
    {
        //
    }

    /**
     * @param static $one
     */
    public function testAnnotationStatic($one): void
    {
        //
    }

    /**
     * @param \DateTime $one
     */
    public function testTypehintAndAnnotationClass(\DateTime $one): void
    {
        //
    }

    /**
     * @param DateTime $one
     */
    public function testAnnotationIncompleteClass($one): void
    {
        //
    }

    /**
     * @param \NonExistingClass $one
     */
    public function testAnnotationNonExistingClass($one): void
    {
        //
    }

    /**
     * @param int $one
     */
    public function testAnnotationNameMismatch($two): void
    {
        //
    }

    /**
     * @param int
     * @param string
     */
    public function testAnnotationWithoutName($one, $two): void
    {
        //
    }

    /**
     * @param int
     */
    public function testAnnotationCountMismatch($one, $two): void
    {
        //
    }

    /**
     * @param int
     * @param string
     * @param bool
     */
    public function testAnnotationCountMismatch2($one, $two): void
    {
        //
    }

    /**
     * @param int|string $one
     */
    public function testAnnotationMoreTypes($one): void
    {
        //
    }

    /**
     * @param \DateTime|int[] $one
     */
    public function testAnnotationDimensionMismatch($one): void
    {
        //
    }

    /**
     * @param int[]
     */
    public function testAnnotationArrayBrackets($one): void
    {
        //
    }

    /**
     * @param int(64)[]
     */
    public function testAnnotationWithSizeArrayBrackets($one): void
    {
        //
    }

    /**
     * @param int[] $one
     */
    public function testAnnotationArrayOfType(array $one): void
    {
        //
    }

    /**
     * @param int[]|string[] $one
     */
    public function testAnnotationArrayOfTypes(array $one): void
    {
        //
    }

    /**
     * @param \SplFixedArray|int[] $one
     */
    public function testAnnotationCollectionOfType($one): void
    {
        //
    }

    /**
     * @param \SplFixedArray|int[]|string[] $one
     */
    public function testAnnotationCollectionOfTypes($one): void
    {
        //
    }

    /**
     * @return int
     */
    public function testReturnAnnotation(): void
    {
        //
    }

    /**
     * @return int(64)
     */
    public function testReturnAnnotationWithSize(): void
    {
        //
    }

    /**
     * @return int
     */
    public function testReturnTypehintAndAnnotation(): int
    {
        return 1;
    }

    /**
     * @return int(64)
     */
    public function testReturnTypehintAndAnnotationWithSize(): int
    {
        return 1;
    }

    // phpcs:enable

}
