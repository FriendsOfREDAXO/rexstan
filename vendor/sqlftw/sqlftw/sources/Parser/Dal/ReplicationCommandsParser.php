<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dal;

use Dogma\Math\PowersOfTwo;
use LogicException;
use SqlFtw\Parser\ExpressionParser;
use SqlFtw\Parser\InvalidValueException;
use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\TokenList;
use SqlFtw\Parser\TokenType;
use SqlFtw\Sql\Dal\Replication\ChangeMasterToCommand;
use SqlFtw\Sql\Dal\Replication\ChangeReplicationFilterCommand;
use SqlFtw\Sql\Dal\Replication\ChangeReplicationSourceToCommand;
use SqlFtw\Sql\Dal\Replication\PurgeBinaryLogsCommand;
use SqlFtw\Sql\Dal\Replication\ReplicaOption;
use SqlFtw\Sql\Dal\Replication\ReplicationCommand;
use SqlFtw\Sql\Dal\Replication\ReplicationFilter;
use SqlFtw\Sql\Dal\Replication\ReplicationFilterType;
use SqlFtw\Sql\Dal\Replication\ReplicationGtidAssignOption;
use SqlFtw\Sql\Dal\Replication\ReplicationPrimaryKeyCheckOption;
use SqlFtw\Sql\Dal\Replication\ReplicationThreadType;
use SqlFtw\Sql\Dal\Replication\ResetMasterCommand;
use SqlFtw\Sql\Dal\Replication\ResetReplicaCommand;
use SqlFtw\Sql\Dal\Replication\ResetSlaveCommand;
use SqlFtw\Sql\Dal\Replication\SlaveOption;
use SqlFtw\Sql\Dal\Replication\StartGroupReplicationCommand;
use SqlFtw\Sql\Dal\Replication\StartReplicaCommand;
use SqlFtw\Sql\Dal\Replication\StartSlaveCommand;
use SqlFtw\Sql\Dal\Replication\StopGroupReplicationCommand;
use SqlFtw\Sql\Dal\Replication\StopReplicaCommand;
use SqlFtw\Sql\Dal\Replication\StopSlaveCommand;
use SqlFtw\Sql\Dal\Replication\UuidSet;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\Expression\NullLiteral;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Expression\Subquery;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\UserName;
use function array_shift;
use function explode;
use function is_string;
use function trim;
use const PHP_INT_MAX;

class ReplicationCommandsParser
{

    private ExpressionParser $expressionParser;

    public function __construct(ExpressionParser $expressionParser)
    {
        $this->expressionParser = $expressionParser;
    }

    /**
     * CHANGE MASTER TO option [, option] ... [ channel_option ]
     *
     * option:
     *     MASTER_BIND = 'interface_name'
     *   | MASTER_HOST = 'host_name'
     *   | MASTER_USER = 'user_name'
     *   | MASTER_PASSWORD = 'password'
     *   | MASTER_PORT = port_num
     *   | PRIVILEGE_CHECKS_USER = {'account' | NULL}
     *   | REQUIRE_ROW_FORMAT = {0|1}
     *   | REQUIRE_TABLE_PRIMARY_KEY_CHECK = {STREAM | ON | OFF}
     *   | ASSIGN_GTIDS_TO_ANONYMOUS_TRANSACTIONS = {OFF | LOCAL | uuid}
     *   | MASTER_LOG_FILE = 'source_log_name'
     *   | MASTER_LOG_POS = source_log_pos
     *   | MASTER_AUTO_POSITION = {0|1}
     *   | RELAY_LOG_FILE = 'relay_log_name'
     *   | RELAY_LOG_POS = relay_log_pos
     *   | MASTER_HEARTBEAT_PERIOD = interval
     *   | MASTER_CONNECT_RETRY = interval
     *   | MASTER_RETRY_COUNT = count
     *   | SOURCE_CONNECTION_AUTO_FAILOVER = {0|1}
     *   | MASTER_DELAY = interval
     *   | MASTER_COMPRESSION_ALGORITHMS = 'algorithm[,algorithm][,algorithm]'
     *   | MASTER_ZSTD_COMPRESSION_LEVEL = level
     *   | MASTER_SSL = {0|1}
     *   | MASTER_SSL_CA = 'ca_file_name'
     *   | MASTER_SSL_CAPATH = 'ca_directory_name'
     *   | MASTER_SSL_CERT = 'cert_file_name'
     *   | MASTER_SSL_CRL = 'crl_file_name'
     *   | MASTER_SSL_CRLPATH = 'crl_directory_name'
     *   | MASTER_SSL_KEY = 'key_file_name'
     *   | MASTER_SSL_CIPHER = 'cipher_list'
     *   | MASTER_SSL_VERIFY_SERVER_CERT = {0|1}
     *   | MASTER_TLS_VERSION = 'protocol_list'
     *   | MASTER_TLS_CIPHERSUITES = 'ciphersuite_list'
     *   | MASTER_PUBLIC_KEY_PATH = 'key_file_name'
     *   | GET_MASTER_PUBLIC_KEY = {0|1}
     *   | NETWORK_NAMESPACE = 'namespace'
     *   | IGNORE_SERVER_IDS = (server_id_list),
     *   | GTID_ONLY = {0|1}
     *
     * channel_option:
     *     FOR CHANNEL channel
     *
     * server_id_list:
     *     [server_id [, server_id] ... ]
     */
    public function parseChangeMasterTo(TokenList $tokenList): ChangeMasterToCommand
    {
        $tokenList->expectKeywords(Keyword::CHANGE, Keyword::MASTER, Keyword::TO);
        $types = SlaveOption::getTypes();
        $options = [];
        do {
            /** @var SlaveOption::* $option */
            $option = $tokenList->expectKeywordEnum(SlaveOption::class)->getValue();
            $tokenList->expectOperator(Operator::EQUAL);
            $type = $types[$option];
            switch ($type) {
                case [0, 1]:
                    $value = (int) $tokenList->expectUnsignedInt();
                    if ($value > 1) {
                        throw new InvalidValueException('0 or 1', $tokenList);
                    }
                    break;
                case BaseType::CHAR:
                    $value = $tokenList->expectString();
                    break;
                case BaseType::CHAR . '|' . NullLiteral::class:
                    if ($tokenList->hasKeyword(Keyword::NULL)) {
                        $value = new NullLiteral();
                    } else {
                        $value = $tokenList->expectString();
                    }
                    break;
                case BaseType::UNSIGNED:
                    $value = (int) $tokenList->expectUnsignedInt();
                    if ($option === SlaveOption::MASTER_DELAY && $value >= PowersOfTwo::_2G) {
                        throw new InvalidValueException('0 to 2147483647', $tokenList);
                    }
                    break;
                case BaseType::NUMERIC:
                    $value = (float) $tokenList->expect(TokenType::NUMBER)->value;
                    break;
                case BaseType::BOOL:
                    $value = $tokenList->expectBool();
                    break;
                case UserName::class . '|' . NullLiteral::class:
                    if ($tokenList->hasKeyword(Keyword::NULL)) {
                        $value = new NullLiteral();
                    } else {
                        $value = $tokenList->expectUserName();
                    }
                    break;
                case BaseType::UNSIGNED . '[]':
                    $tokenList->expectSymbol('(');
                    $value = [];
                    if (!$tokenList->hasSymbol(')')) {
                        do {
                            $value[] = (int) $tokenList->expectUnsignedInt();
                            if ($tokenList->hasSymbol(')')) {
                                break;
                            } else {
                                $tokenList->expectSymbol(',');
                            }
                        } while (true);
                    }
                    break;
                case ReplicationGtidAssignOption::class:
                    $uuid = null;
                    $keyword = $tokenList->getAnyKeyword(Keyword::OFF, Keyword::LOCAL);
                    if ($keyword === null) {
                        $uuid = $tokenList->expectUuid();
                    }
                    $value = new ReplicationGtidAssignOption($keyword ?? ReplicationGtidAssignOption::UUID, $uuid);
                    break;
                case ReplicationPrimaryKeyCheckOption::class:
                    $value = $tokenList->expectKeywordEnum(ReplicationPrimaryKeyCheckOption::class);
                    break;
                default:
                    throw new LogicException(is_string($type) ? "Unknown type $type." : "Unknown type.");
            }
            $options[$option] = $value;
        } while ($tokenList->hasSymbol(','));

        $channel = null;
        if ($tokenList->hasKeywords(Keyword::FOR, Keyword::CHANNEL)) {
            $channel = $tokenList->expectNonReservedNameOrString();
            $tokenList->validateName(EntityType::CHANNEL, $channel);
        }

        return new ChangeMasterToCommand($options, $channel);
    }

    /**
     * CHANGE REPLICATION SOURCE TO option [, option] ... [ channel_option ]
     *
     * option: {
     *     SOURCE_BIND = 'interface_name'
     *   | SOURCE_HOST = 'host_name'
     *   | SOURCE_USER = 'user_name'
     *   | SOURCE_PASSWORD = 'password'
     *   | SOURCE_PORT = port_num
     *   | PRIVILEGE_CHECKS_USER = {NULL | 'account'}
     *   | REQUIRE_ROW_FORMAT = {0|1}
     *   | REQUIRE_TABLE_PRIMARY_KEY_CHECK = {STREAM | ON | OFF}
     *   | ASSIGN_GTIDS_TO_ANONYMOUS_TRANSACTIONS = {OFF | LOCAL | uuid}
     *   | SOURCE_LOG_FILE = 'source_log_name'
     *   | SOURCE_LOG_POS = source_log_pos
     *   | SOURCE_AUTO_POSITION = {0|1}
     *   | RELAY_LOG_FILE = 'relay_log_name'
     *   | RELAY_LOG_POS = relay_log_pos
     *   | SOURCE_HEARTBEAT_PERIOD = interval
     *   | SOURCE_CONNECT_RETRY = interval
     *   | SOURCE_RETRY_COUNT = count
     *   | SOURCE_CONNECTION_AUTO_FAILOVER = {0|1}
     *   | SOURCE_DELAY = interval
     *   | SOURCE_COMPRESSION_ALGORITHMS = 'algorithm[,algorithm][,algorithm]'
     *   | SOURCE_ZSTD_COMPRESSION_LEVEL = level
     *   | SOURCE_SSL = {0|1}
     *   | SOURCE_SSL_CA = 'ca_file_name'
     *   | SOURCE_SSL_CAPATH = 'ca_directory_name'
     *   | SOURCE_SSL_CERT = 'cert_file_name'
     *   | SOURCE_SSL_CRL = 'crl_file_name'
     *   | SOURCE_SSL_CRLPATH = 'crl_directory_name'
     *   | SOURCE_SSL_KEY = 'key_file_name'
     *   | SOURCE_SSL_CIPHER = 'cipher_list'
     *   | SOURCE_SSL_VERIFY_SERVER_CERT = {0|1}
     *   | SOURCE_TLS_VERSION = 'protocol_list'
     *   | SOURCE_TLS_CIPHERSUITES = 'ciphersuite_list'
     *   | SOURCE_PUBLIC_KEY_PATH = 'key_file_name'
     *   | GET_SOURCE_PUBLIC_KEY = {0|1}
     *   | NETWORK_NAMESPACE = 'namespace'
     *   | IGNORE_SERVER_IDS = (server_id_list),
     *   | GTID_ONLY = {0|1}
     * }
     *
     * channel_option:
     *     FOR CHANNEL channel
     *
     * server_id_list:
     *     [server_id [, server_id] ... ]
     */
    public function parseChangeReplicationSourceTo(TokenList $tokenList): ChangeReplicationSourceToCommand
    {
        $tokenList->expectKeywords(Keyword::CHANGE, Keyword::REPLICATION, Keyword::SOURCE, Keyword::TO);
        $types = ReplicaOption::getTypes();
        $options = [];
        do {
            /** @var ReplicaOption::* $option */
            $option = $tokenList->expectKeywordEnum(ReplicaOption::class)->getValue();
            $tokenList->expectOperator(Operator::EQUAL);
            $type = $types[$option];
            switch ($type) {
                case [0, 1]:
                    $value = (int) $tokenList->expectUnsignedInt();
                    if ($value > 1) {
                        throw new InvalidValueException('0 or 1', $tokenList);
                    }
                    break;
                case BaseType::CHAR:
                    $value = $tokenList->expectString();
                    break;
                case BaseType::CHAR . '|' . NullLiteral::class:
                    if ($tokenList->hasKeyword(Keyword::NULL)) {
                        $value = new NullLiteral();
                    } else {
                        $value = $tokenList->expectString();
                    }
                    break;
                case BaseType::UNSIGNED:
                    $value = (int) $tokenList->expectUnsignedInt();
                    if ($option === ReplicaOption::SOURCE_DELAY && $value >= PowersOfTwo::_2G) {
                        throw new InvalidValueException('0 to 2147483647', $tokenList);
                    }
                    break;
                case BaseType::NUMERIC:
                    $value = (float) $tokenList->expect(TokenType::NUMBER)->value;
                    break;
                case BaseType::BOOL:
                    $value = $tokenList->expectBool();
                    break;
                case UserName::class . '|' . NullLiteral::class:
                    if ($tokenList->hasKeyword(Keyword::NULL)) {
                        $value = new NullLiteral();
                    } else {
                        $value = $tokenList->expectUserName();
                    }
                    break;
                case BaseType::UNSIGNED . '[]':
                    $tokenList->expectSymbol('(');
                    $value = [];
                    if (!$tokenList->hasSymbol(')')) {
                        do {
                            $value[] = (int) $tokenList->expectUnsignedInt();
                            if ($tokenList->hasSymbol(')')) {
                                break;
                            } else {
                                $tokenList->expectSymbol(',');
                            }
                        } while (true);
                    }
                    break;
                case ReplicationGtidAssignOption::class:
                    $uuid = null;
                    $keyword = $tokenList->getAnyKeyword(Keyword::OFF, Keyword::LOCAL);
                    if ($keyword === null) {
                        $uuid = $tokenList->expectUuid();
                    }
                    $value = new ReplicationGtidAssignOption($keyword ?? ReplicationGtidAssignOption::UUID, $uuid);
                    break;
                case ReplicationPrimaryKeyCheckOption::class:
                    $value = $tokenList->expectKeywordEnum(ReplicationPrimaryKeyCheckOption::class);
                    break;
                default:
                    throw new LogicException(is_string($type) ? "Unknown type $type." : "Unknown type.");
            }
            $options[$option] = $value;
        } while ($tokenList->hasSymbol(','));

        $channel = null;
        if ($tokenList->hasKeywords(Keyword::FOR, Keyword::CHANNEL)) {
            $channel = $tokenList->expectNonReservedNameOrString();
            $tokenList->validateName(EntityType::CHANNEL, $channel);
        }

        return new ChangeReplicationSourceToCommand($options, $channel);
    }

    /**
     * CHANGE REPLICATION FILTER filter[, filter][, ...]
     *     [FOR CHANNEL channel]
     *
     * filter:
     *     REPLICATE_DO_DB = (db_list)
     *   | REPLICATE_IGNORE_DB = (db_list)
     *   | REPLICATE_DO_TABLE = (tbl_list)
     *   | REPLICATE_IGNORE_TABLE = (tbl_list)
     *   | REPLICATE_WILD_DO_TABLE = (wild_tbl_list)
     *   | REPLICATE_WILD_IGNORE_TABLE = (wild_tbl_list)
     *   | REPLICATE_REWRITE_DB = (db_pair_list)
     *
     * db_list:
     *     db_name[, db_name][, ...]
     *
     * tbl_list:
     *     db_name.table_name[, db_table_name][, ...]
     *
     * wild_tbl_list:
     *     'db_pattern.table_pattern'[, 'db_pattern.table_pattern'][, ...]
     *
     * db_pair_list:
     *     (db_pair)[, (db_pair)][, ...]
     *
     * db_pair:
     *     from_db, to_db
     */
    public function parseChangeReplicationFilter(TokenList $tokenList): ChangeReplicationFilterCommand
    {
        $tokenList->expectKeywords(Keyword::CHANGE, Keyword::REPLICATION, Keyword::FILTER);

        $types = ReplicationFilterType::getItemTypes();
        $filters = [];
        do {
            /** @var ReplicationFilterType::* $filter */
            $filter = $tokenList->expectKeywordEnum(ReplicationFilterType::class)->getValue();
            $tokenList->expectOperator(Operator::EQUAL);
            $tokenList->expectSymbol('(');
            if ($tokenList->hasSymbol(')')) {
                $values = [];
            } else {
                switch ($types[$filter]) {
                    case BaseType::CHAR . '[]':
                        $values = [];
                        do {
                            if ($filter === ReplicationFilterType::REPLICATE_DO_DB || $filter === ReplicationFilterType::REPLICATE_IGNORE_DB) {
                                $values[] = $tokenList->expectName(EntityType::SCHEMA);
                            } else {
                                $values[] = $tokenList->expectString();
                            }
                        } while ($tokenList->hasSymbol(','));
                        break;
                    case ObjectIdentifier::class . '[]':
                        $values = [];
                        do {
                            $values[] = $tokenList->expectObjectIdentifier();
                        } while ($tokenList->hasSymbol(','));
                        break;
                    case BaseType::CHAR . '{}':
                        $values = [];
                        do {
                            $tokenList->expectSymbol('(');
                            $key = $tokenList->expectName(EntityType::SCHEMA);
                            $tokenList->expectSymbol(',');
                            $value = $tokenList->expectName(EntityType::SCHEMA);
                            $tokenList->expectSymbol(')');
                            $values[$key] = $value;
                        } while ($tokenList->hasSymbol(','));
                        break;
                    default:
                        $values = [];
                }
                $tokenList->expectSymbol(')');
            }
            $filters[] = new ReplicationFilter($filter, $values);
        } while ($tokenList->hasSymbol(','));

        $channel = null;
        if ($tokenList->hasKeywords(Keyword::FOR, Keyword::CHANNEL)) {
            $channel = $tokenList->expectNonReservedNameOrString();
            $tokenList->validateName(EntityType::CHANNEL, $channel);
        }

        return new ChangeReplicationFilterCommand($filters, $channel);
    }

    /**
     * PURGE { BINARY | MASTER } LOGS
     *     { TO 'log_name' | BEFORE datetime_expr }
     */
    public function parsePurgeBinaryLogs(TokenList $tokenList): PurgeBinaryLogsCommand
    {
        $tokenList->expectKeyword(Keyword::PURGE);
        $tokenList->expectAnyKeyword(Keyword::BINARY, Keyword::MASTER);
        $tokenList->expectKeyword(Keyword::LOGS);
        $log = $before = null;
        if ($tokenList->hasKeyword(Keyword::TO)) {
            $log = $tokenList->expectString();
        } elseif ($tokenList->hasKeyword(Keyword::BEFORE)) {
            $before = $this->expressionParser->parseExpression($tokenList);
            if ($before instanceof Subquery) {
                throw new ParserException('Subquery is not allowed here.', $tokenList);
            }
        } else {
            $tokenList->missingAnyKeyword(Keyword::TO, Keyword::BEFORE);
        }

        return new PurgeBinaryLogsCommand($log, $before);
    }

    /**
     * RESET MASTER [TO binary_log_file_index_number]
     */
    public function parseResetMaster(TokenList $tokenList): ResetMasterCommand
    {
        $tokenList->expectKeywords(Keyword::RESET, Keyword::MASTER);
        $position = null;
        if ($tokenList->hasKeyword(Keyword::TO)) {
            $position = $tokenList->expectIntLike();
            if ((int) $position->getValue() === PHP_INT_MAX) {
                throw new ParserException('Log file index number is too large.', $tokenList);
            }
        }

        return new ResetMasterCommand($position);
    }

    /**
     * RESET SLAVE [ALL] [channel_option]
     *
     * channel_option:
     *     FOR CHANNEL channel
     */
    public function parseResetSlave(TokenList $tokenList): ResetSlaveCommand
    {
        $tokenList->expectKeywords(Keyword::RESET, Keyword::SLAVE);
        $all = $tokenList->hasKeyword(Keyword::ALL);
        $channel = null;
        if ($tokenList->hasKeywords(Keyword::FOR, Keyword::CHANNEL)) {
            $channel = $tokenList->expectNonReservedNameOrString();
            $tokenList->validateName(EntityType::CHANNEL, $channel);
        }

        return new ResetSlaveCommand($all, $channel);
    }

    /**
     * RESET REPLICA [ALL] [channel_option]
     *
     * channel_option:
     *     FOR CHANNEL channel
     */
    public function parseResetReplica(TokenList $tokenList): ResetReplicaCommand
    {
        $tokenList->expectKeywords(Keyword::RESET, Keyword::REPLICA);
        $all = $tokenList->hasKeyword(Keyword::ALL);
        $channel = null;
        if ($tokenList->hasKeywords(Keyword::FOR, Keyword::CHANNEL)) {
            $channel = $tokenList->expectNonReservedNameOrString();
            $tokenList->validateName(EntityType::CHANNEL, $channel);
        }

        return new ResetReplicaCommand($all, $channel);
    }

    /**
     * START GROUP_REPLICATION
     *     [USER='user_name']
     *     [, PASSWORD='user_pass']
     *     [, DEFAULT_AUTH='plugin_name']
     */
    public function parseStartGroupReplication(TokenList $tokenList): StartGroupReplicationCommand
    {
        $tokenList->expectKeywords(Keyword::START, Keyword::GROUP_REPLICATION);

        $user = $password = $defaultAuth = null;
        $keywords = [Keyword::USER, Keyword::PASSWORD, Keyword::DEFAULT_AUTH];
        $keyword = $tokenList->getAnyKeyword(...$keywords);
        while ($keyword !== null) {
            $tokenList->check('group replication credentials', 80021);
            $tokenList->passSymbol('=');
            if ($keyword === Keyword::USER) {
                $user = $tokenList->expectString();
            } elseif ($keyword === Keyword::PASSWORD) {
                $password = $tokenList->expectString();
            } else {
                $defaultAuth = $tokenList->expectString();
            }
            if ($tokenList->hasSymbol(',')) {
                $keyword = $tokenList->expectAnyKeyword(...$keywords);
            } else {
                $keyword = null;
            }
        }
        if ($password !== null && $user === null) {
            throw new ParserException('User is needed, when password is used.', $tokenList);
        }

        return new StartGroupReplicationCommand($user, $password, $defaultAuth);
    }

    /**
     * START SLAVE [thread_types] [until_option] [connection_options] [channel_option]
     *
     * thread_types:
     *     [thread_type [, thread_type] ... ]
     *
     * thread_type:
     *     IO_THREAD | SQL_THREAD
     *
     * until_option:
     *     UNTIL {
     *         {SQL_BEFORE_GTIDS | SQL_AFTER_GTIDS} = gtid_set
     *       | MASTER_LOG_FILE = 'log_name', MASTER_LOG_POS = log_pos
     *       | SOURCE_LOG_FILE = 'log_name', SOURCE_LOG_POS = log_pos
     *       | RELAY_LOG_FILE = 'log_name', RELAY_LOG_POS = log_pos
     *       | SQL_AFTER_MTS_GAPS
     *     }
     *
     * connection_options:
     *     [USER='user_name'] [PASSWORD='user_pass'] [DEFAULT_AUTH='plugin_name'] [PLUGIN_DIR='plugin_dir']
     *
     * channel_option:
     *     FOR CHANNEL channel
     *
     * @return StartReplicaCommand|StartSlaveCommand
     */
    public function parseStartReplicaOrSlave(TokenList $tokenList): ReplicationCommand
    {
        $tokenList->expectKeyword(Keyword::START);
        $which = $tokenList->expectAnyKeyword(Keyword::SLAVE, Keyword::REPLICA);

        $threadTypes = null;
        $threadType = $tokenList->getKeywordEnum(ReplicationThreadType::class);
        if ($threadType !== null) {
            /** @var non-empty-list<ReplicationThreadType> $threadTypes */
            $threadTypes = [$threadType];
            while ($tokenList->hasSymbol(',')) {
                $threadTypes[] = $tokenList->expectKeywordEnum(ReplicationThreadType::class);
            }
        }

        $until = null;
        if ($tokenList->hasKeyword(Keyword::UNTIL)) {
            $until = [];
            if ($tokenList->hasKeyword(Keyword::SQL_AFTER_MTS_GAPS)) {
                $until[Keyword::SQL_AFTER_MTS_GAPS] = true;
            } elseif ($tokenList->hasKeyword(Keyword::SQL_BEFORE_GTIDS)) {
                $tokenList->expectOperator(Operator::EQUAL);
                $until[Keyword::SQL_BEFORE_GTIDS] = $this->parseGtidSet($tokenList);
            } elseif ($tokenList->hasKeyword(Keyword::SQL_AFTER_GTIDS)) {
                $tokenList->expectOperator(Operator::EQUAL);
                $until[Keyword::SQL_AFTER_GTIDS] = $this->parseGtidSet($tokenList);
            } elseif ($tokenList->hasKeyword(Keyword::MASTER_LOG_FILE)) {
                $tokenList->expectOperator(Operator::EQUAL);
                $until[Keyword::MASTER_LOG_FILE] = $tokenList->expectString();
                $tokenList->expectSymbol(',');
                $tokenList->expectKeyword(Keyword::MASTER_LOG_POS);
                $tokenList->expectOperator(Operator::EQUAL);
                $until[Keyword::MASTER_LOG_POS] = (int) $tokenList->expectUnsignedInt();
            } elseif ($tokenList->hasKeyword(Keyword::SOURCE_LOG_FILE)) {
                $tokenList->expectOperator(Operator::EQUAL);
                $until[Keyword::SOURCE_LOG_FILE] = $tokenList->expectString();
                $tokenList->expectSymbol(',');
                $tokenList->expectKeyword(Keyword::SOURCE_LOG_POS);
                $tokenList->expectOperator(Operator::EQUAL);
                $until[Keyword::SOURCE_LOG_POS] = (int) $tokenList->expectUnsignedInt();
            } elseif ($tokenList->hasKeyword(Keyword::RELAY_LOG_FILE)) {
                $tokenList->expectOperator(Operator::EQUAL);
                $until[Keyword::RELAY_LOG_FILE] = $tokenList->expectString();
                $tokenList->expectSymbol(',');
                $tokenList->expectKeyword(Keyword::RELAY_LOG_POS);
                $tokenList->expectOperator(Operator::EQUAL);
                $until[Keyword::RELAY_LOG_POS] = (int) $tokenList->expectUnsignedInt();
            } else {
                $tokenList->missingAnyKeyword(Keyword::SQL_AFTER_MTS_GAPS, Keyword::SQL_BEFORE_GTIDS, Keyword::SQL_AFTER_GTIDS, Keyword::MASTER_LOG_FILE, Keyword::RELAY_LOG_FILE);
            }
        }

        $user = $password = $defaultAuth = $pluginDir = null;
        if ($tokenList->hasKeyword(Keyword::USER)) {
            $tokenList->expectOperator(Operator::EQUAL);
            $user = $tokenList->expectString();
        }
        if ($tokenList->hasKeyword(Keyword::PASSWORD)) {
            $tokenList->expectOperator(Operator::EQUAL);
            $password = $tokenList->expectString();
        }
        if ($tokenList->hasKeyword(Keyword::DEFAULT_AUTH)) {
            $tokenList->expectOperator(Operator::EQUAL);
            $defaultAuth = $tokenList->expectString();
        }
        if ($tokenList->hasKeyword(Keyword::PLUGIN_DIR)) {
            $tokenList->expectOperator(Operator::EQUAL);
            $pluginDir = $tokenList->expectString();
        }

        $channel = null;
        if ($tokenList->hasKeywords(Keyword::FOR, Keyword::CHANNEL)) {
            $channel = $tokenList->expectNonReservedNameOrString();
            $tokenList->validateName(EntityType::CHANNEL, $channel);
        }

        if ($which === Keyword::SLAVE) {
            return new StartSlaveCommand($user, $password, $defaultAuth, $pluginDir, $until, $threadTypes, $channel);
        } else {
            return new StartReplicaCommand($user, $password, $defaultAuth, $pluginDir, $until, $threadTypes, $channel);
        }
    }

    /**
     * gtid_set:
     *     uuid_set [, uuid_set] ...
     *   | ''
     *
     * uuid_set:
     *     uuid:interval[:interval]...
     *
     * uuid:
     *     hhhhhhhh-hhhh-hhhh-hhhh-hhhhhhhhhhhh
     *
     * h:
     *     [0-9,A-F]
     *
     * interval:
     *     n[-n]
     *
     *     (n >= 1)
     *
     * @return non-empty-list<UuidSet>|string
     */
    private function parseGtidSet(TokenList $tokenList)
    {
        $string = $tokenList->getString();
        if ($string !== null) {
            if ($string === '') {
                return '';
            }

            $gtids = [];
            $sets = explode(',', $string);
            foreach ($sets as $set) {
                $parts = explode(':', trim($set));
                $uuid = array_shift($parts);
                $intervals = [];
                foreach ($parts as $part) {
                    $startEnd = explode('-', $part);
                    $start = (int) $startEnd[0];
                    $end = isset($startEnd[1]) ? (int) $startEnd[1] : null;
                    $intervals[] = [$start, $end];
                }

                $gtids[] = new UuidSet($uuid, $intervals);
            }

            return $gtids;
        }

        $gtids = [];
        do {
            $uuid = $tokenList->expectUuid();
            $intervals = [];
            $tokenList->expectSymbol(':');
            do {
                $start = (int) $tokenList->expectUnsignedInt();
                $end = null;
                if ($tokenList->hasOperator(Operator::MINUS)) {
                    $end = (int) $tokenList->expectUnsignedInt();
                }
                $intervals[] = [$start, $end];
                if (!$tokenList->hasSymbol(':')) {
                    break;
                }
            } while (true);
            $gtids[] = new UuidSet($uuid, $intervals);
        } while ($tokenList->hasSymbol(','));

        return $gtids;
    }

    /**
     * STOP GROUP_REPLICATION
     */
    public function parseStopGroupReplication(TokenList $tokenList): StopGroupReplicationCommand
    {
        $tokenList->expectKeywords(Keyword::STOP, Keyword::GROUP_REPLICATION);

        return new StopGroupReplicationCommand();
    }

    /**
     * STOP SLAVE [thread_types] [channel_option]
     *
     * thread_types:
     *     [thread_type [, thread_type] ... ]
     *
     * thread_type: IO_THREAD | SQL_THREAD
     *
     * channel_option:
     *     FOR CHANNEL channel
     */
    public function parseStopSlave(TokenList $tokenList): StopSlaveCommand
    {
        $tokenList->expectKeywords(Keyword::STOP, Keyword::SLAVE);
        $ioThread = $sqlThread = false;
        $thread = $tokenList->getAnyKeyword(Keyword::IO_THREAD, Keyword::SQL_THREAD);
        if ($thread !== null) {
            if ($thread === Keyword::IO_THREAD) {
                $ioThread = true;
            } else {
                $sqlThread = true;
            }
        }
        if ($tokenList->hasSymbol(',')) {
            $thread = $tokenList->expectAnyKeyword(Keyword::IO_THREAD, Keyword::SQL_THREAD);
            if ($thread === Keyword::IO_THREAD) {
                $ioThread = true;
            } else {
                $sqlThread = true;
            }
        }
        $channel = null;
        if ($tokenList->hasKeywords(Keyword::FOR, Keyword::CHANNEL)) {
            $channel = $tokenList->expectNonReservedNameOrString();
            $tokenList->validateName(EntityType::CHANNEL, $channel);
        }

        return new StopSlaveCommand($ioThread, $sqlThread, $channel);
    }

    /**
     * STOP REPLICA [thread_types] [channel_option]
     *
     * thread_types:
     *     [thread_type [, thread_type] ... ]
     *
     * thread_type: IO_THREAD | SQL_THREAD
     *
     * channel_option:
     *     FOR CHANNEL channel
     */
    public function parseStopReplica(TokenList $tokenList): StopReplicaCommand
    {
        $tokenList->expectKeywords(Keyword::STOP, Keyword::REPLICA);
        $ioThread = $sqlThread = false;
        $thread = $tokenList->getAnyKeyword(Keyword::IO_THREAD, Keyword::SQL_THREAD);
        if ($thread !== null) {
            if ($thread === Keyword::IO_THREAD) {
                $ioThread = true;
            } else {
                $sqlThread = true;
            }
        }
        if ($tokenList->hasSymbol(',')) {
            $thread = $tokenList->expectAnyKeyword(Keyword::IO_THREAD, Keyword::SQL_THREAD);
            if ($thread === Keyword::IO_THREAD) {
                $ioThread = true;
            } else {
                $sqlThread = true;
            }
        }
        $channel = null;
        if ($tokenList->hasKeywords(Keyword::FOR, Keyword::CHANNEL)) {
            $channel = $tokenList->expectNonReservedNameOrString();
            $tokenList->validateName(EntityType::CHANNEL, $channel);
        }

        return new StopReplicaCommand($ioThread, $sqlThread, $channel);
    }

}
