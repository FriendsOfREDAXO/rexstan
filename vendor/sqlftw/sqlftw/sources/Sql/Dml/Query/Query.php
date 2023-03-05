<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Query;

use SqlFtw\Sql\Dml\DmlCommand;
use SqlFtw\Sql\Expression\OrderByExpression;
use SqlFtw\Sql\Expression\Placeholder;
use SqlFtw\Sql\Expression\SimpleName;

/**
 * Interface for SELECT, TABLE and VALUES commands, QueryExpression (UNION|EXCEPT|INTERSECT) and ParenthesizedQueryExpression
 */
interface Query extends DmlCommand
{

    /**
     * @return non-empty-list<OrderByExpression>|null
     */
    public function getOrderBy(): ?array;

    /**
     * @return static
     */
    public function removeOrderBy(): self;

    /**
     * @return int|SimpleName|Placeholder|null
     */
    public function getLimit();

    /**
     * @return static
     */
    public function removeLimit(): self;

    /**
     * @return int|SimpleName|Placeholder|null
     */
    public function getOffset();

    /**
     * @return static
     */
    public function removeOffset(): self;

    public function getInto(): ?SelectInto;

    /**
     * @return static
     */
    public function removeInto(): self;

}
