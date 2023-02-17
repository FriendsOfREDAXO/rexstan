<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Flush;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dal\DalCommand;
use SqlFtw\Sql\Statement;
use function array_map;
use function implode;

class FlushCommand extends Statement implements DalCommand
{

    /** @var non-empty-list<FlushOption> */
    private array $options;

    private ?string $channel;

    private bool $local;

    /**
     * @param non-empty-list<FlushOption> $options
     */
    public function __construct(array $options, ?string $channel = null, bool $local = false)
    {
        $this->options = $options;
        $this->channel = $channel;
        $this->local = $local;
    }

    /**
     * @return non-empty-list<FlushOption>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function isLocal(): bool
    {
        return $this->local;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'FLUSH ';
        if ($this->isLocal()) {
            $result .= 'LOCAL ';
        }
        $result .= implode(', ', array_map(function (FlushOption $option) use ($formatter) {
            if ($this->channel !== null && $option->equalsAnyValue(FlushOption::RELAY_LOGS)) {
                return $option->serialize($formatter) . ' FOR CHANNEL ' . $formatter->formatString($this->channel);
            } else {
                return $option->serialize($formatter);
            }
        }, $this->options));

        return $result;
    }

}
