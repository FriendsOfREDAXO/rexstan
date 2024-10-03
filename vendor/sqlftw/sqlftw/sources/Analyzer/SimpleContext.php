<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Analyzer;

use SqlFtw\Analyzer\Types\CastingTypeChecker;
use SqlFtw\Platform\Platform;
use SqlFtw\Resolver\ExpressionResolver;
use SqlFtw\Session\Session;

/**
 * Basic tool set for rules which do not need knowledge of database schema or previous statements history
 */
class SimpleContext
{

    private Platform $platform;

    private Session $session;

    private ExpressionResolver $resolver;

    private CastingTypeChecker $typeChecker;

    public function __construct(Platform $platform, Session $session, ExpressionResolver $resolver)
    {
        $this->platform = $platform;
        $this->session = $session;
        $this->resolver = $resolver;
        $this->typeChecker = new CastingTypeChecker();
    }

    public function getPlatform(): Platform
    {
        return $this->platform;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getResolver(): ExpressionResolver
    {
        return $this->resolver;
    }

    public function getTypeChecker(): CastingTypeChecker
    {
        return $this->typeChecker;
    }

}
