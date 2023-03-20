<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Platform;

/**
 * Client-specific SQL syntax extensions resolved by DBAL layer (mostly parameter placeholders)
 *
 * Doctrine: https://www.doctrine-project.org/projects/doctrine-orm/en/2.14/reference/dql-doctrine-query-language.html#named-and-positional-parameters
 * ?, ?123, :var, []
 *
 * Laravel: https://laravel.com/docs/10.x/queries
 * ?, :var
 *
 * Nette Database: https://doc.nette.org/cs/database/core
 * ?, [?], ?values, ?set, ?and, ?or, ?order, ?name
 *
 * Dibi: https://dibiphp.com/en/documentation#toc-modifiers
 * %s, %sN, %bin, %~like~ ...
 *
 * Nextras DBAL: https://nextras.org/dbal/docs/main/param-modifiers
 * %s, %?s, %s[], %...s[] ...
 */
class ClientSideExtension
{

    // "DELIMITER ;;" (mysql client syntax to tell him how to chop input into batches before sending it to server)
    public const ALLOW_DELIMITER_DEFINITION = 1;

    // "UPDATE tbl1 SET a = ? WHERE b = ?" (Doctrine, Laravel, NDB, Dibi)
    public const ALLOW_QUESTION_MARK_PLACEHOLDERS_OUTSIDE_PREPARED_STATEMENTS = 2;

    // "UPDATE tbl1 SET a = ?1 WHERE b = ?2" (Doctrine)
    public const ALLOW_NUMBERED_QUESTION_MARK_PLACEHOLDERS = 4;

    // "UPDATE tbl2 SET a = :value1 WHERE b = :value2" (Doctrine, Laravel)
    public const ALLOW_NAMED_DOUBLE_COLON_PLACEHOLDERS = 8;

    // "UPDATE [tbl2] SET [a] = 1 WHERE [b] = 2" (Doctrine, Dibi)
    //public const CONVERT_SQUARE_BRACKET_IDENTIFIERS = 16;

}
