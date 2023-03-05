<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Index;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;
use function str_replace;
use function strtoupper;

class IndexType extends SqlEnum
{

    public const PRIMARY = Keyword::PRIMARY . ' ' . Keyword::KEY;
    public const UNIQUE = Keyword::UNIQUE . ' ' . Keyword::KEY; // or INDEX
    public const INDEX = Keyword::INDEX; // or KEY
    public const FULLTEXT = Keyword::FULLTEXT . ' ' . Keyword::INDEX; // or KEY
    public const SPATIAL = Keyword::SPATIAL . ' ' . Keyword::INDEX; // or KEY

    public static function validateValue(string &$value): bool
    {
        // normalize KEY vs INDEX
        $value = strtoupper($value);
        if ($value === Keyword::UNIQUE . ' ' . Keyword::INDEX) {
            $value = self::UNIQUE;
        } elseif ($value === Keyword::FULLTEXT . ' ' . Keyword::KEY) {
            $value = self::FULLTEXT;
        } elseif ($value === Keyword::SPATIAL . ' ' . Keyword::KEY) {
            $value = self::FULLTEXT;
        } elseif ($value === Keyword::KEY) {
            $value = self::INDEX;
        }

        return parent::validateValue($value);
    }

    public function serializeIndexAsKey(Formatter $formatter): string
    {
        return str_replace(Keyword::INDEX, Keyword::KEY, $this->getValue());
    }

}
