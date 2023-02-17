<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Alter;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class AlterTableActionType extends SqlEnum
{

    public const ADD_COLUMN = Keyword::ADD . ' ' . Keyword::COLUMN;
    public const ADD_COLUMNS = Keyword::ADD . ' ' . Keyword::COLUMNS;
    public const ALTER_COLUMN = Keyword::ALTER . ' ' . Keyword::COLUMN;
    public const CHANGE_COLUMN = Keyword::CHANGE . ' ' . Keyword::COLUMN;
    public const DROP_COLUMN = Keyword::DROP . ' ' . Keyword::COLUMN;
    public const MODIFY_COLUMN = Keyword::MODIFY . ' ' . Keyword::COLUMN;

    public const ADD_INDEX = Keyword::ADD . ' ' . Keyword::INDEX;
    public const ALTER_INDEX = Keyword::ALTER . ' ' . Keyword::INDEX;
    public const DISABLE_KEYS = Keyword::DISABLE . ' ' . Keyword::KEYS;
    public const DROP_INDEX = Keyword::DROP . ' ' . Keyword::INDEX;
    public const ENABLE_KEYS = Keyword::ENABLE . ' ' . Keyword::KEYS;
    public const RENAME_INDEX = Keyword::RENAME . ' ' . Keyword::INDEX;

    public const ADD_FOREIGN_KEY = Keyword::ADD . ' ' . Keyword::FOREIGN . ' ' . Keyword::KEY;
    public const DROP_FOREIGN_KEY = Keyword::DROP . ' ' . Keyword::FOREIGN . ' ' . Keyword::KEY;
    public const DROP_PRIMARY_KEY = Keyword::DROP . ' ' . Keyword::PRIMARY . ' ' . Keyword::KEY;

    public const ADD_CONSTRAINT = Keyword::ADD . ' ' . Keyword::CONSTRAINT;
    public const ALTER_CONSTRAINT = Keyword::ALTER . ' ' . Keyword::CONSTRAINT;
    public const DROP_CONSTRAINT = Keyword::DROP . ' ' . Keyword::CONSTRAINT;

    public const ADD_CHECK = Keyword::ADD . ' ' . Keyword::CHECK;
    public const ALTER_CHECK = Keyword::ALTER . ' ' . Keyword::CHECK;
    public const DROP_CHECK = Keyword::DROP . ' ' . Keyword::CHECK;

    public const CONVERT_TO_CHARACTER_SET = Keyword::CONVERT . ' ' . Keyword::TO . ' ' . Keyword::CHARACTER . ' ' . Keyword::SET;

    public const ORDER_BY = Keyword::ORDER . ' ' . Keyword::BY;

    public const RENAME_TO = Keyword::RENAME . ' ' . Keyword::TO;

    public const DISCARD_TABLESPACE = Keyword::DISCARD . ' ' . Keyword::TABLESPACE;
    public const DISCARD_PARTITION_TABLESPACE = Keyword::DISCARD . ' ' . Keyword::PARTITION . ' ' . Keyword::TABLESPACE;
    public const IMPORT_TABLESPACE = Keyword::IMPORT . ' ' . Keyword::TABLESPACE;
    public const IMPORT_PARTITION_TABLESPACE = Keyword::IMPORT . ' ' . Keyword::PARTITION . ' ' . Keyword::TABLESPACE;

    public const ADD_PARTITION = Keyword::ADD . ' ' . Keyword::PARTITION;
    public const ANALYZE_PARTITION = Keyword::ANALYZE . ' ' . Keyword::PARTITION;
    public const CHECK_PARTITION = Keyword::CHECK . ' ' . Keyword::PARTITION;
    public const COALESCE_PARTITION = Keyword::COALESCE . ' ' . Keyword::PARTITION;
    public const DROP_PARTITION = Keyword::DROP . ' ' . Keyword::PARTITION;
    public const EXCHANGE_PARTITION = Keyword::EXCHANGE . ' ' . Keyword::PARTITION;
    public const OPTIMIZE_PARTITION = Keyword::OPTIMIZE . ' ' . Keyword::PARTITION;
    public const REBUILD_PARTITION = Keyword::REBUILD . ' ' . Keyword::PARTITION;
    public const REORGANIZE_PARTITION = Keyword::REORGANIZE . ' ' . Keyword::PARTITION;
    public const REPAIR_PARTITION = Keyword::REPAIR . ' ' . Keyword::PARTITION;
    public const REMOVE_PARTITIONING = Keyword::REMOVE . ' ' . Keyword::PARTITIONING;
    public const TRUNCATE_PARTITION = Keyword::TRUNCATE . ' ' . Keyword::PARTITION;
    public const UPGRADE_PARTITIONING = Keyword::UPGRADE . ' ' . Keyword::PARTITIONING;

}
