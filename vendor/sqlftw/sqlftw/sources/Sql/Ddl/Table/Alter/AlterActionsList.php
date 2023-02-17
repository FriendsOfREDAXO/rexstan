<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Alter;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AlterTableAction;
use SqlFtw\Sql\SqlSerializable;
use function array_filter;
use function array_values;
use function rtrim;

class AlterActionsList implements SqlSerializable
{

    /** @var list<AlterTableAction> */
    private array $actions;

    /**
     * @param list<AlterTableAction> $actions
     */
    public function __construct(array $actions)
    {
        $this->actions = $actions;
    }

    /**
     * @return list<AlterTableAction>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @return list<AlterTableAction>
     */
    public function filter(string $class): array
    {
        return array_values(array_filter($this->actions, static function (AlterTableAction $action) use ($class) {
            return $action instanceof $class;
        }));
    }

    public function isEmpty(): bool
    {
        return $this->actions === [];
    }

    public function serialize(Formatter $formatter): string
    {
        $result = '';
        foreach ($this->actions as $action) {
            $result .= "\n" . $formatter->indent . $action->serialize($formatter) . ',';
        }

        return rtrim($result, ',');
    }

}
