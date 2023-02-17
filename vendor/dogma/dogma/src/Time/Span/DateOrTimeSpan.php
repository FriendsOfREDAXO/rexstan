<?php declare(strict_types = 1);

namespace Dogma\Time\Span;

use DateInterval;
use Dogma\Comparable;
use Dogma\Equalable;

interface DateOrTimeSpan extends Equalable, Comparable
{

    public function toNative(): DateInterval;

    /**
     * @return DateInterval[]
     */
    public function toPositiveAndNegative(): array;

    public function format(string $format = '', ?DateTimeSpanFormatter $formatter = null): string;

    public function isZero(): bool;

    public function isMixed(): bool;

    //public function add(self ...$other): self;

    //public function subtract(self ...$other): self;

    /**
     * @return mixed
     */
    public function invert();//: self;

    /**
     * @return mixed
     */
    public function abs();//: self;

    /**
     * Normalizes values by summarizing smaller units into bigger. eg: '34 days' -> '1 month, 4 days'
     * @return self|mixed
     */
    public function normalize();//: self;

    /**
     * @return int[]
     */
    public function getValues(): array;

}
