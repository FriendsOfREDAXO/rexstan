<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\OptimizerHint;

use SqlFtw\Sql\SqlEnum;

/**
 * @phpstan-type JoinOrderHintType 'JOIN_ORDER'|'JOIN_PREFIX'|'JOIN_SUFFIX'
 * @phpstan-type TableLevelHintType 'BKA'|'NO_BKA'|'BNL'|'NO_BNL'|'DERIVED_CONDITION_PUSHDOWN'|'NO_DERIVED_CONDITION_PUSHDOWN'|'HASH_JOIN'|'NO_HASH_JOIN'|'MERGE'|'NO_MERGE'
 * @phpstan-type IndexLevelHintType 'GROUP_INDEX'|'NO_GROUP_INDEX'|'INDEX'|'NO_INDEX'|'INDEX_MERGE'|'NO_INDEX_MERGE'|'JOIN_INDEX'|'NO_JOIN_INDEX'|'MRR'|'NO_MRR'|'NO_ICP'|'NO_RANGE_OPTIMIZATION'|'ORDER_INDEX'|'NO_ORDER_INDEX'|'SKIP_SCAN'|'NO_SKIP_SCAN'
 * @phpstan-type SemijoinHintType 'SEMIJOIN'|'NO_SEMIJOIN'
 */
class OptimizerHintType extends SqlEnum
{

    public const BATCHED_KEY_ACCESS = 'BKA';
    public const NO_BATCHED_KEY_ACCESS = 'NO_BKA';
    public const BLOCK_NESTED_LOOPS = 'BNL';
    public const NO_BLOCK_NESTED_LOOPS = 'NO_BNL';
    public const DERIVED_CONDITION_PUSHDOWN = 'DERIVED_CONDITION_PUSHDOWN';
    public const NO_DERIVED_CONDITION_PUSHDOWN = 'NO_DERIVED_CONDITION_PUSHDOWN';
    public const GROUP_INDEX = 'GROUP_INDEX';
    public const NO_GROUP_INDEX = 'NO_GROUP_INDEX';
    public const HASH_JOIN = 'HASH_JOIN';
    public const NO_HASH_JOIN = 'NO_HASH_JOIN';
    public const INDEX = 'INDEX';
    public const NO_INDEX = 'NO_INDEX';
    public const INDEX_MERGE = 'INDEX_MERGE';
    public const NO_INDEX_MERGE = 'NO_INDEX_MERGE';
    public const JOIN_FIXED_ORDER = 'JOIN_FIXED_ORDER';
    public const JOIN_INDEX = 'JOIN_INDEX';
    public const NO_JOIN_INDEX = 'NO_JOIN_INDEX';
    public const JOIN_ORDER = 'JOIN_ORDER';
    public const JOIN_PREFIX = 'JOIN_PREFIX';
    public const JOIN_SUFFIX = 'JOIN_SUFFIX';
    public const MAX_EXECUTION_TIME = 'MAX_EXECUTION_TIME';
    public const MERGE = 'MERGE';
    public const NO_MERGE = 'NO_MERGE';
    public const MULTI_RANGE_READ = 'MRR';
    public const NO_MULTI_RANGE_READ = 'NO_MRR';
    public const NO_INDEX_CONDITION_PUSHDOWN = 'NO_ICP';
    public const NO_RANGE_OPTIMIZATION = 'NO_RANGE_OPTIMIZATION';
    public const ORDER_INDEX = 'ORDER_INDEX';
    public const NO_ORDER_INDEX = 'NO_ORDER_INDEX';
    public const QUERY_BLOCK_NAME = 'QB_NAME';
    public const RESOURCE_GROUP = 'RESOURCE_GROUP';
    public const SEMIJOIN = 'SEMIJOIN';
    public const NO_SEMIJOIN = 'NO_SEMIJOIN';
    public const SKIP_SCAN = 'SKIP_SCAN';
    public const NO_SKIP_SCAN = 'NO_SKIP_SCAN';
    public const SET_VAR = 'SET_VAR';
    public const SUBQUERY = 'SUBQUERY';

}
