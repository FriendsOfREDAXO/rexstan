<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Replication;

use Dogma\Arr;
use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Statement;
use function implode;
use function is_array;

/**
 * @phpstan-type UntilKeyword 'SQL_AFTER_MTS_GAPS'|'SQL_BEFORE_GTIDS'|'SQL_AFTER_GTIDS'|'MASTER_LOG_FILE'|'MASTER_LOG_POS'|'SOURCE_LOG_FILE'|'SOURCE_LOG_POS'|'RELAY_LOG_FILE'|'RELAY_LOG_POS'
 */
class StartSlaveCommand extends Statement implements ReplicationCommand
{

    private ?string $user;

    private ?string $password;

    private ?string $defaultAuth;

    private ?string $pluginDir;

    /** @var non-empty-array<UntilKeyword, string|int|bool|non-empty-list<UuidSet>>|null */
    private ?array $until;

    /** @var non-empty-list<ReplicationThreadType>|null */
    private ?array $threadTypes;

    private ?string $channel;

    /**
     * @param non-empty-array<UntilKeyword, string|int|bool|non-empty-list<UuidSet>>|null $until
     * @param non-empty-list<ReplicationThreadType>|null $threadTypes
     */
    public function __construct(
        ?string $user,
        ?string $password,
        ?string $defaultAuth = null,
        ?string $pluginDir = null,
        ?array $until = null,
        ?array $threadTypes = null,
        ?string $channel = null
    ) {
        $this->user = $user;
        $this->password = $password;
        $this->defaultAuth = $defaultAuth;
        $this->pluginDir = $pluginDir;
        $this->until = $until;
        $this->threadTypes = $threadTypes;
        $this->channel = $channel;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getDefaultAuth(): ?string
    {
        return $this->defaultAuth;
    }

    public function getPluginDir(): ?string
    {
        return $this->pluginDir;
    }

    /**
     * @return non-empty-array<UntilKeyword, string|int|bool|non-empty-list<UuidSet>>|null
     */
    public function getUntil(): ?array
    {
        return $this->until;
    }

    /**
     * @return non-empty-list<ReplicationThreadType>|null
     */
    public function getThreadTypes(): ?array
    {
        return $this->threadTypes;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'START SLAVE';

        if ($this->threadTypes !== null) {
            $result .= ' ' . $formatter->formatSerializablesList($this->threadTypes);
        }

        if ($this->until !== null) {
            $result .= ' UNTIL ' . implode(', ', Arr::mapPairs($this->until, static function (string $name, $value) use ($formatter) {
                if ($value === true) {
                    return $name;
                } elseif (is_array($value)) {
                    // phpcs:ignore SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.MissingVariable
                    /** @var non-empty-list<UuidSet> $value */
                    return $name . ' = ' . $formatter->formatSerializablesList($value);
                } else {
                    return $name . ' = ' . $formatter->formatValue($value);
                }
            }));
        }

        if ($this->user !== null) {
            $result .= ' USER=' . $formatter->formatString($this->user);
        }
        if ($this->password !== null) {
            $result .= ' PASSWORD=' . $formatter->formatString($this->password);
        }
        if ($this->defaultAuth !== null) {
            $result .= ' DEFAULT_AUTH=' . $formatter->formatString($this->defaultAuth);
        }
        if ($this->pluginDir !== null) {
            $result .= ' PLUGIN_DIR=' . $formatter->formatString($this->pluginDir);
        }

        if ($this->channel !== null) {
            $result .= ' FOR CHANNEL ' . $formatter->formatValue($this->channel);
        }

        return $result;
    }

}
