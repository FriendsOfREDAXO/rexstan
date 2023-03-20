<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Session;

use SqlFtw\Platform\Platform;
use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Collation;
use SqlFtw\Sql\Expression\UnresolvedExpression;
use SqlFtw\Sql\Expression\Value;
use SqlFtw\Sql\MysqlVariable;
use SqlFtw\Sql\SqlMode;
use function array_key_exists;
use function array_pop;
use function end;

/**
 * Initial settings and global parser state (information, that persists between statements and affects parsing)
 */
class Session
{

    private Platform $platform;

    private string $delimiter;

    private SqlMode $mode;

    private ?string $schema = null;

    private ?Charset $charset;

    private int $extensions;

    /** @var list<Collation> */
    private array $collation = [];

    /** @var array<string, UnresolvedExpression|scalar|Value|null> */
    private array $userVariables = [];

    /** @var array<string, UnresolvedExpression|scalar|Value|null> */
    private array $sessionVariables = [];

    /** @var array<string, UnresolvedExpression|scalar|Value|null> */
    private array $globalVariables = [];

    /** @var array<string, UnresolvedExpression|scalar|Value|null> */
    private array $localVariables = [];

    public function __construct(
        Platform $platform,
        int $clientSideExtensions = 0
    ) {
        $this->platform = $platform;
        $this->extensions = $clientSideExtensions;

        $this->reset();
    }

    /**
     * Clears internal state (variables) and resets delimiter, charset and mode to defaults
     */
    public function reset(?string $delimiter = null, ?Charset $charset = null, ?SqlMode $mode = null): void
    {
        $this->userVariables = [];
        $this->sessionVariables = [];
        $this->globalVariables = [];

        $this->delimiter = $delimiter ?? ';';
        $this->charset = $charset;

        $this->setGlobalVariable(MysqlVariable::VERSION, $this->platform->getVersion()->format());
        if ($mode !== null) {
            $this->setMode($mode);
        } else {
            /** @var string $defaultMode */
            $defaultMode = MysqlVariable::getDefault(MysqlVariable::SQL_MODE);
            $this->setMode(SqlMode::getFromString($defaultMode));
        }
    }

    public function getPlatform(): Platform
    {
        return $this->platform;
    }

    public function getClientSideExtensions(): int
    {
        return $this->extensions;
    }

    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    public function setDelimiter(string $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    public function getMode(): SqlMode
    {
        return $this->mode;
    }

    public function setMode(SqlMode $mode): void
    {
        $this->mode = $mode;
        $this->sessionVariables['sql_mode'] = $mode->getValue();
    }

    public function getSchema(): ?string
    {
        return $this->schema;
    }

    public function setSchema(string $schema): void
    {
        $this->schema = $schema;
    }

    public function getCharset(): ?Charset
    {
        return $this->charset;
    }

    public function setCharset(Charset $charset): void
    {
        $this->charset = $charset;
    }

    public function startCollation(Collation $collation): void
    {
        $this->collation[] = $collation;
    }

    public function endCollation(): void
    {
        array_pop($this->collation);
    }

    public function getCollation(): Collation
    {
        $collation = end($this->collation);
        if ($collation === false) {
            // todo: infer from Charset
            return new Collation(Collation::UTF8MB4_GENERAL_CI);
        } else {
            return $collation;
        }
    }

    /**
     * @param UnresolvedExpression|scalar|Value|null $value
     */
    public function setUserVariable(string $name, $value): void
    {
        $this->userVariables[$name] = $value;
    }

    /**
     * @return UnresolvedExpression|scalar|Value|null
     */
    public function getUserVariable(string $name)
    {
        return $this->userVariables[$name] ?? null;
    }

    /**
     * @param UnresolvedExpression|scalar|Value|null $value
     */
    public function setSessionVariable(string $name, $value): void
    {
        $this->sessionVariables[$name] = $value;
    }

    /**
     * @return UnresolvedExpression|scalar|Value|null
     */
    public function getSessionVariable(string $name)
    {
        return $this->sessionVariables[$name] ?? MysqlVariable::getDefault($name);
    }

    /**
     * @return UnresolvedExpression|scalar|Value|null
     */
    public function getSessionOrGlobalVariable(string $name)
    {
        return $this->sessionVariables[$name] ?? $this->globalVariables[$name] ?? MysqlVariable::getDefault($name);
    }

    /**
     * @param UnresolvedExpression|scalar|Value|null $value
     */
    public function setGlobalVariable(string $name, $value): void
    {
        $this->globalVariables[$name] = $value;
    }

    /**
     * @return UnresolvedExpression|scalar|Value|null
     */
    public function getGlobalVariable(string $name)
    {
        return $this->globalVariables[$name] ?? MysqlVariable::getDefault($name);
    }

    /**
     * @param UnresolvedExpression|scalar|Value|null $value
     */
    public function setLocalVariable(string $name, $value): void
    {
        $this->localVariables[$name] = $value;
    }

    /**
     * @return UnresolvedExpression|scalar|Value|null
     */
    public function getLocalVariable(string $name)
    {
        return $this->localVariables[$name] ?? null;
    }

    public function isLocalVariable(string $name): bool
    {
        return array_key_exists($name, $this->localVariables);
    }

    public function resetLocalVariables(): void
    {
        $this->localVariables = [];
    }

}
