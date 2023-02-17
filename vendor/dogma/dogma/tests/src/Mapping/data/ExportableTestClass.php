<?php declare(strict_types = 1);

namespace Dogma\Tests\Mapping;

use Dogma\Mapping\Type\Exportable;

class ExportableTestClass implements Exportable
{

    /** @var int */
    private $one;

    /** @var float */
    private $two;

    /**
     * @param int $one
     * @param float $two
     */
    public function __construct(int $one, float $two)
    {
        $this->one = $one;
        $this->two = $two;
    }

    /**
     * @return mixed[]
     */
    public function export(): array
    {
        return [
            'one' => $this->one,
            'two' => $this->two,
        ];
    }

}
