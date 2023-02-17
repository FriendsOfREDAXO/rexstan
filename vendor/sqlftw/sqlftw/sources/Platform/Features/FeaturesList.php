<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Platform\Features;

use SqlFtw\Sql\Command;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\Expression\BuiltInFunction;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\MysqlVariable;

abstract class FeaturesList
{

    protected const MIN = 10000;
    protected const MAX = 999999;

    /** @var list<array{Feature::*, int, int}> */
    public array $features = [];

    /** @var list<array{Keyword::*, int, int}> */
    public array $reserved = [];

    /** @var list<array{Keyword::*, int, int}> */
    public array $nonReserved = [];

    /** @var list<array{Operator::*, int, int}> */
    public array $operators = [];

    /** @var list<array{BaseType::*, int, int}> */
    public array $types = [];

    /** @var list<array{BuiltInFunction::*, int, int, 3?: int}> */
    public array $functions = [];

    /** @var list<array{MysqlVariable::*, int, int}> */
    public array $variables = [];

    /** @var list<array{class-string<Command>, int, int}> */
    public array $preparableCommands = [];

    /** @var array<EntityType::*, int> */
    public array $maxLengths = [];

}
