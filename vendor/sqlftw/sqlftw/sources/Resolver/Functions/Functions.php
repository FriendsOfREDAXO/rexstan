<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable SlevomatCodingStandard.Classes.TraitUseSpacing.IncorrectLinesCountBetweenUses
// phpcs:disable SlevomatCodingStandard.Classes.TraitUseSpacing.IncorrectLinesCountAfterLastUse

namespace SqlFtw\Resolver\Functions;

use SqlFtw\Platform\Platform;
use SqlFtw\Resolver\Cast;
use SqlFtw\Session\Session;

class Functions
{
    use FunctionsBitwise; // todo: bit-shifts on strings
    use FunctionsBool;
    use FunctionsCast; // todo
    use FunctionsComparison; // todo
    use FunctionsDateTime; // todo
    use FunctionsEncryption; // todo: aes..(), statement_digest..()
    use FunctionsFlowControl; // todo
    use FunctionsGtid; // todo
    use FunctionsInfo; // mocks
    use FunctionsJson; // todo
    use FunctionsLocking; // todo
    use FunctionsMatching; // todo: regexp_..()
    use FunctionsMisc; // todo
    use FunctionsNumeric;
    use FunctionsPerfSchema; // todo
    use FunctionsSpatial; // todo
    use FunctionsString;
    use FunctionsSys; // todo
    use FunctionsXml; // todo

    private Platform $platform;

    private Session $session;

    private Cast $cast;

    public function __construct(Platform $platform, Session $session, Cast $cast)
    {
        $this->platform = $platform;
        $this->session = $session;
        $this->cast = $cast;
    }

}
