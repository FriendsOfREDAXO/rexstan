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

    private Session $session;

    private Platform $platform;

    private ExpressionResolver $resolver;

    private CastingTypeChecker $typeChecker;

    public function __construct(Session $session, ExpressionResolver $resolver)
    {
        $this->session = $session;
        $this->platform = $session->getPlatform();
        $this->resolver = $resolver;
        $this->typeChecker = new CastingTypeChecker();
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getPlatform(): Platform
    {
        return $this->platform;
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
