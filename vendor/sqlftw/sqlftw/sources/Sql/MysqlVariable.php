<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: DML DSYNC GCS GPL KEYHASH LINHASH LITTLESYNC NOBLOB NOSYNC WRITESET XCOM approle binlogging checkon cmk ib ibdata1 ibtmp1 kickup lowpct mecab mysqld nodeids okv pem prio rc recv
// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingBefore

namespace SqlFtw\Sql;

use Dogma\IntBounds as I;
use Dogma\Math\PowersOfTwo;
use Dogma\Time\Seconds;
use SqlFtw\Sql\Ddl\Table\Option\StorageEngine;
use SqlFtw\Sql\Expression\BaseType as T;
use SqlFtw\Sql\Expression\Scope as S;
use SqlFtw\Sql\VariableFlags as F;
use const PHP_INT_MAX as MAX;

class MysqlVariable extends SqlEnum
{

    public const ACTIVATE_ALL_ROLES_ON_LOGIN = 'activate_all_roles_on_login';

    public const ADMIN_ADDRESS = 'admin_address';
    public const ADMIN_PORT = 'admin_port';
    public const ADMIN_SSL_CA = 'admin_ssl_ca';
    public const ADMIN_SSL_CAPATH = 'admin_ssl_capath';
    public const ADMIN_SSL_CERT = 'admin_ssl_cert';
    public const ADMIN_SSL_CIPHER = 'admin_ssl_cipher';
    public const ADMIN_TLS_CIPHERSUITES = 'admin_tls_ciphersuites';
    public const ADMIN_SSL_CRL = 'admin_ssl_crl';
    public const ADMIN_SSL_CRLPATH = 'admin_ssl_crlpath';
    public const ADMIN_SSL_KEY = 'admin_ssl_key';
    public const ADMIN_TLS_VERSION = 'admin_tls_version';

    public const AUDIT_LOG_BUFFER_SIZE = 'audit_log_buffer_size';
    public const AUDIT_LOG_COMPRESSION = 'audit_log_compression';
    public const AUDIT_LOG_CONNECTION_POLICY = 'audit_log_connection_policy';
    public const AUDIT_LOG_CURRENT_SESSION = 'audit_log_current_session';
    public const AUDIT_LOG_DATABASE = 'audit_log_database';
    public const AUDIT_LOG_DISABLE = 'audit_log_disable';
    public const AUDIT_LOG_ENCRYPTION = 'audit_log_encryption';
    public const AUDIT_LOG_EXCLUDE_ACCOUNTS = 'audit_log_exclude_accounts';
    public const AUDIT_LOG_FILE = 'audit_log_file';
    public const AUDIT_LOG_FILTER_ID = 'audit_log_filter_id';
    public const AUDIT_LOG_FLUSH = 'audit_log_flush';
    public const AUDIT_LOG_FORMAT = 'audit_log_format';
    public const AUDIT_LOG_FORMAT_UNIX_TIMESTAMP = 'audit_log_format_unix_timestamp';
    public const AUDIT_LOG_INCLUDE_ACCOUNTS = 'audit_log_include_accounts';
    public const AUDIT_LOG_MAX_SIZE = 'audit_log_max_size';
    public const AUDIT_LOG_PASSWORD_HISTORY_KEEP_DAYS = 'audit_log_password_history_keep_days';
    public const AUDIT_LOG_POLICY = 'audit_log_policy';
    public const AUDIT_LOG_PRUNE_SECONDS = 'audit_log_prune_seconds';
    public const AUDIT_LOG_READ_BUFFER_SIZE = 'audit_log_read_buffer_size';
    public const AUDIT_LOG_ROTATE_ON_SIZE = 'audit_log_rotate_on_size';
    public const AUDIT_LOG_STATEMENT_POLICY = 'audit_log_statement_policy';
    public const AUDIT_LOG_STRATEGY = 'audit_log_strategy';

    public const AUTHENTICATION_POLICY = 'authentication_policy';
    public const AUTHENTICATION_WINDOWS_LOG_LEVEL = 'authentication_windows_log_level';
    public const AUTHENTICATION_WINDOWS_USE_PRINCIPAL_NAME = 'authentication_windows_use_principal_name';

    public const AUTO_GENERATE_CERTS = 'auto_generate_certs';
    public const AUTO_INCREMENT_INCREMENT = 'auto_increment_increment';
    public const AUTO_INCREMENT_OFFSET = 'auto_increment_offset';
    public const AUTOCOMMIT = 'autocommit';
    public const AUTOMATIC_SP_PRIVILEGES = 'automatic_sp_privileges';
    public const AVOID_TEMPORAL_UPGRADE = 'avoid_temporal_upgrade';
    public const BACK_LOG = 'back_log';
    public const BASEDIR = 'basedir';
    public const BIG_TABLES = 'big_tables';
    public const BIND_ADDRESS = 'bind_address';

    public const BINLOG_CACHE_SIZE = 'binlog_cache_size';
    public const BINLOG_CHECKSUM = 'binlog_checksum';
    public const BINLOG_DIRECT_NON_TRANSACTIONAL_UPDATES = 'binlog_direct_non_transactional_updates';
    public const BINLOG_ENCRYPTION = 'binlog_encryption';
    public const BINLOG_ERROR_ACTION = 'binlog_error_action';
    public const BINLOG_EXPIRE_LOGS_AUTO_PURGE = 'binlog_expire_logs_auto_purge';
    public const BINLOG_EXPIRE_LOGS_SECONDS = 'binlog_expire_logs_seconds';
    public const BINLOG_FORMAT = 'binlog_format'; // deprecated since 8.0.34
    public const BINLOG_GROUP_COMMIT_SYNC_DELAY = 'binlog_group_commit_sync_delay';
    public const BINLOG_GROUP_COMMIT_SYNC_NO_DELAY_COUNT = 'binlog_group_commit_sync_no_delay_count';
    public const BINLOG_GTID_SIMPLE_RECOVERY = 'binlog_gtid_simple_recovery';
    public const BINLOG_MAX_FLUSH_QUEUE_TIME = 'binlog_max_flush_queue_time';
    public const BINLOG_ORDER_COMMITS = 'binlog_order_commits';
    public const BINLOG_ROTATE_ENCRYPTION_MASTER_KEY_AT_STARTUP = 'binlog_rotate_encryption_master_key_at_startup';
    public const BINLOG_ROWS_QUERY_LOG_EVENTS = 'binlog_rows_query_log_events';
    public const BINLOG_ROW_EVENT_MAX_SIZE = 'binlog_row_event_max_size';
    public const BINLOG_ROW_IMAGE = 'binlog_row_image';
    public const BINLOG_ROW_METADATA = 'binlog_row_metadata';
    public const BINLOG_ROW_VALUE_OPTIONS = 'binlog_row_value_options';
    public const BINLOG_STMT_CACHE_SIZE = 'binlog_stmt_cache_size';
    public const BINLOG_TRANSACTION_COMPRESSION = 'binlog_transaction_compression';
    public const BINLOG_TRANSACTION_COMPRESSION_LEVEL_ZSTD = 'binlog_transaction_compression_level_zstd';
    public const BINLOG_TRANSACTION_DEPENDENCY_HISTORY_SIZE = 'binlog_transaction_dependency_history_size';
    public const BINLOG_TRANSACTION_DEPENDENCY_TRACKING = 'binlog_transaction_dependency_tracking';
    public const BINLOGGING_IMPOSSIBLE_MODE = 'binlogging_impossible_mode';
    public const SIMPLIFIED_BINLOG_GTID_RECOVERY = 'simplified_binlog_gtid_recovery';

    public const BLOCK_ENCRYPTION_MODE = 'block_encryption_mode';
    public const BUILD_ID = 'build_id';
    public const BULK_INSERT_BUFFER_SIZE = 'bulk_insert_buffer_size';

    public const CACHING_SHA2_PASSWORD_AUTO_GENERATE_RSA_KEYS = 'caching_sha2_password_auto_generate_rsa_keys';
    public const CACHING_SHA2_PASSWORD_DIGEST_ROUNDS = 'caching_sha2_password_digest_rounds';
    public const CACHING_SHA2_PASSWORD_PRIVATE_KEY_PATH = 'caching_sha2_password_private_key_path';
    public const CACHING_SHA2_PASSWORD_PUBLIC_KEY_PATH = 'caching_sha2_password_public_key_path';

    public const CHARACTER_SET_CLIENT = 'character_set_client';
    public const CHARACTER_SET_CONNECTION = 'character_set_connection';
    public const CHARACTER_SET_DATABASE = 'character_set_database';
    public const CHARACTER_SET_FILESYSTEM = 'character_set_filesystem';
    public const CHARACTER_SET_RESULTS = 'character_set_results';
    public const CHARACTER_SET_SERVER = 'character_set_server';
    public const CHARACTER_SET_SYSTEM = 'character_set_system';

    public const CHARACTER_SETS_DIR = 'character_sets_dir';
    public const CHECK_PROXY_USERS = 'check_proxy_users';

    public const CLONE_AUTOTUNE_CONCURRENCY = 'clone_autotune_concurrency';
    public const CLONE_BLOCK_DDL = 'clone_block_ddl';
    public const CLONE_BUFFER_SIZE = 'clone_buffer_size';
    public const CLONE_DDL_TIMEOUT = 'clone_ddl_timeout';
    public const CLONE_DELAY_AFTER_DATA_DROP = 'clone_delay_after_data_drop';
    public const CLONE_DONOR_TIMEOUT_AFTER_NETWORK_FAILURE = 'clone_donor_timeout_after_network_failure';
    public const CLONE_ENABLE_COMPRESSION = 'clone_enable_compression';
    public const CLONE_MAX_CONCURRENCY = 'clone_max_concurrency';
    public const CLONE_MAX_DATA_BANDWIDTH = 'clone_max_data_bandwidth';
    public const CLONE_MAX_NETWORK_BANDWIDTH = 'clone_max_network_bandwidth';
    public const CLONE_SSL_CA = 'clone_ssl_ca';
    public const CLONE_SSL_CERT = 'clone_ssl_cert';
    public const CLONE_SSL_KEY = 'clone_ssl_key';
    public const CLONE_VALID_DONOR_LIST = 'clone_valid_donor_list';

    public const COLLATION_CONNECTION = 'collation_connection';
    public const COLLATION_DATABASE = 'collation_database';
    public const COLLATION_SERVER = 'collation_server';

    public const COMPLETION_TYPE = 'completion_type';
    public const CONCURRENT_INSERT = 'concurrent_insert';
    public const CONNECT_TIMEOUT = 'connect_timeout';

    public const CONNECTION_CONTROL_FAILED_CONNECTIONS_THRESHOLD = 'connection_control_failed_connections_threshold';
    public const CONNECTION_CONTROL_MAX_CONNECTION_DELAY = 'connection_control_max_connection_delay';
    public const CONNECTION_CONTROL_MIN_CONNECTION_DELAY = 'connection_control_min_connection_delay';

    public const CONNECTION_MEMORY_CHUNK_SIZE = 'connection_memory_chunk_size';
    public const CONNECTION_MEMORY_LIMIT = 'connection_memory_limit';

    public const CORE_FILE = 'core_file';
    public const CREATE_ADMIN_LISTENER_THREAD = 'create_admin_listener_thread';
    public const CREATE_OLD_TEMPORALS = 'create_old_temporals';
    public const CTE_MAX_RECURSION_DEPTH = 'cte_max_recursion_depth';

    public const DAEMON_MEMCACHED_ENABLE_BINLOG = 'daemon_memcached_enable_binlog';
    public const DAEMON_MEMCACHED_ENGINE_LIB_NAME = 'daemon_memcached_engine_lib_name';
    public const DAEMON_MEMCACHED_ENGINE_LIB_PATH = 'daemon_memcached_engine_lib_path';
    public const DAEMON_MEMCACHED_OPTION = 'daemon_memcached_option';
    public const DAEMON_MEMCACHED_R_BATCH_SIZE = 'daemon_memcached_r_batch_size';
    public const DAEMON_MEMCACHED_W_BATCH_SIZE = 'daemon_memcached_w_batch_size';

    public const DATADIR = 'datadir';
    public const DATE_FORMAT = 'date_format'; // removed in 8.0
    public const DATETIME_FORMAT = 'datetime_format'; // removed in 8.0
    public const DEBUG = 'debug';
    public const DEBUG_SYNC = 'debug_sync';
    public const DEFAULT_AUTHENTICATION_PLUGIN = 'default_authentication_plugin';
    public const DEFAULT_PASSWORD_LIFETIME = 'default_password_lifetime';
    public const DEBUG_SENSITIVE_SESSION_STRING = 'debug_sensitive_session_string';
    public const DEFAULT_COLLATION_FOR_UTF8MB4 = 'default_collation_for_utf8mb4';
    public const DEFAULT_STORAGE_ENGINE = 'default_storage_engine';
    public const DEFAULT_TABLE_ENCRYPTION = 'default_table_encryption';
    public const DEFAULT_TMP_STORAGE_ENGINE = 'default_tmp_storage_engine';
    public const DEFAULT_WEEK_FORMAT = 'default_week_format';
    public const DELAY_KEY_WRITE = 'delay_key_write';
    public const DELAYED_INSERT_LIMIT = 'delayed_insert_limit';
    public const DELAYED_INSERT_TIMEOUT = 'delayed_insert_timeout';
    public const DELAYED_QUEUE_SIZE = 'delayed_queue_size';
    public const DISABLED_STORAGE_ENGINES = 'disabled_storage_engines';
    public const DISCONNECT_ON_EXPIRED_PASSWORD = 'disconnect_on_expired_password';
    public const DIV_PRECISION_INCREMENT = 'div_precision_increment';

    public const ENTERPRISE_ENCRYPTION__MAXIMUM_RSA_KEY_SIZE = 'enterprise_encryption.maximum_rsa_key_size';
    public const ENTERPRISE_ENCRYPTION__RSA_SUPPORT_LEGACY_PADDING = 'enterprise_encryption.rsa_support_legacy_padding';

    public const END_MARKERS_IN_JSON = 'end_markers_in_json';
    public const ENFORCE_GTID_CONSISTENCY = 'enforce_gtid_consistency';
    public const ENGINE_CONDITION_PUSHDOWN = 'engine_condition_pushdown'; // part of OPTIMIZER_SWITCH
    public const EQ_RANGE_INDEX_DIVE_LIMIT = 'eq_range_index_dive_limit';
    public const ERROR_COUNT = 'error_count';
    public const EVENT_SCHEDULER = 'event_scheduler';
    public const EXPIRE_LOGS_DAYS = 'expire_logs_days';
    public const EXPLAIN_FORMAT = 'explain_format';
    public const EXPLICIT_DEFAULTS_FOR_TIMESTAMP = 'explicit_defaults_for_timestamp';
    public const EXTERNAL_USER = 'external_user';
    public const FLUSH = 'flush';
    public const FLUSH_TIME = 'flush_time';
    public const FOREIGN_KEY_CHECKS = 'foreign_key_checks';

    public const FT_BOOLEAN_SYNTAX = 'ft_boolean_syntax';
    public const FT_MAX_WORD_LEN = 'ft_max_word_len';
    public const FT_MIN_WORD_LEN = 'ft_min_word_len';
    public const FT_QUERY_EXPANSION_LIMIT = 'ft_query_expansion_limit';
    public const FT_STOPWORD_FILE = 'ft_stopword_file';

    public const GENERAL_LOG = 'general_log';
    public const GENERAL_LOG_FILE = 'general_log_file';

    public const GENERATED_RANDOM_PASSWORD_LENGTH = 'generated_random_password_length';

    public const GLOBAL_CONNECTION_MEMORY_LIMIT = 'global_connection_memory_limit';
    public const GLOBAL_CONNECTION_MEMORY_TRACKING = 'global_connection_memory_tracking';

    public const GROUP_CONCAT_MAX_LEN = 'group_concat_max_len';

    public const GROUP_REPLICATION_ADVERTISE_RECOVERY_ENDPOINTS = 'group_replication_advertise_recovery_endpoints';
    public const GROUP_REPLICATION_ALLOW_LOCAL_DISJOINT_GTIDS_JOIN = 'group_replication_allow_local_disjoint_gtids_join';
    public const GROUP_REPLICATION_ALLOW_LOCAL_LOWER_VERSION_JOIN = 'group_replication_allow_local_lower_version_join';
    public const GROUP_REPLICATION_AUTO_INCREMENT_INCREMENT = 'group_replication_auto_increment_increment';
    public const GROUP_REPLICATION_AUTOREJOIN_TRIES = 'group_replication_autorejoin_tries';
    public const GROUP_REPLICATION_BOOTSTRAP_GROUP = 'group_replication_bootstrap_group';
    public const GROUP_REPLICATION_CLONE_THRESHOLD = 'group_replication_clone_threshold';
    public const GROUP_REPLICATION_COMMUNICATION_DEBUG_OPTIONS = 'group_replication_communication_debug_options';
    public const GROUP_REPLICATION_COMMUNICATION_STACK = 'group_replication_communication_stack';
    public const GROUP_REPLICATION_COMPONENTS_STOP_TIMEOUT = 'group_replication_components_stop_timeout';
    public const GROUP_REPLICATION_COMPRESSION_THRESHOLD = 'group_replication_compression_threshold';
    public const GROUP_REPLICATION_CONSISTENCY = 'group_replication_consistency';
    public const GROUP_REPLICATION_ENFORCE_UPDATE_EVERYWHERE_CHECKS = 'group_replication_enforce_update_everywhere_checks';
    public const GROUP_REPLICATION_EXIT_STATE_ACTION = 'group_replication_exit_state_action';
    public const GROUP_REPLICATION_FLOW_CONTROL_APPLIER_THRESHOLD = 'group_replication_flow_control_applier_threshold';
    public const GROUP_REPLICATION_FLOW_CONTROL_CERTIFIER_THRESHOLD = 'group_replication_flow_control_certifier_threshold';
    public const GROUP_REPLICATION_FLOW_CONTROL_HOLD_PERCENT = 'group_replication_flow_control_hold_percent';
    public const GROUP_REPLICATION_FLOW_CONTROL_MAX_QUOTA = 'group_replication_flow_control_max_quota';
    public const GROUP_REPLICATION_FLOW_CONTROL_MEMBER_QUOTA_PERCENT = 'group_replication_flow_control_member_quota_percent';
    public const GROUP_REPLICATION_FLOW_CONTROL_MIN_QUOTA = 'group_replication_flow_control_min_quota';
    public const GROUP_REPLICATION_FLOW_CONTROL_MIN_RECOVERY_QUOTA = 'group_replication_flow_control_min_recovery_quota';
    public const GROUP_REPLICATION_FLOW_CONTROL_MODE = 'group_replication_flow_control_mode';
    public const GROUP_REPLICATION_FLOW_CONTROL_PERIOD = 'group_replication_flow_control_period';
    public const GROUP_REPLICATION_FLOW_CONTROL_RELEASE_PERCENT = 'group_replication_flow_control_release_percent';
    public const GROUP_REPLICATION_FORCE_MEMBERS = 'group_replication_force_members';
    public const GROUP_REPLICATION_GROUP_NAME = 'group_replication_group_name';
    public const GROUP_REPLICATION_GROUP_SEEDS = 'group_replication_group_seeds';
    public const GROUP_REPLICATION_GTID_ASSIGNMENT_BLOCK_SIZE = 'group_replication_gtid_assignment_block_size';
    public const GROUP_REPLICATION_IP_ALLOWLIST = 'group_replication_ip_allowlist';
    public const GROUP_REPLICATION_IP_WHITELIST = 'group_replication_ip_whitelist';
    public const GROUP_REPLICATION_LOCAL_ADDRESS = 'group_replication_local_address';
    public const GROUP_REPLICATION_COMMUNICATION_MAX_MESSAGE_SIZE = 'group_replication_communication_max_message_size';
    public const GROUP_REPLICATION_MEMBER_EXPEL_TIMEOUT = 'group_replication_member_expel_timeout';
    public const GROUP_REPLICATION_MEMBER_WEIGHT = 'group_replication_member_weight';
    public const GROUP_REPLICATION_MESSAGE_CACHE_SIZE = 'group_replication_message_cache_size';
    public const GROUP_REPLICATION_PAXOS_SINGLE_LEADER = 'group_replication_paxos_single_leader';
    public const GROUP_REPLICATION_POLL_SPIN_LOOPS = 'group_replication_poll_spin_loops';
    public const GROUP_REPLICATION_RECOVERY_COMPLETE_AT = 'group_replication_recovery_complete_at'; // deprecated since 8.0.34
    public const GROUP_REPLICATION_RECOVERY_COMPRESSION_ALGORITHMS = 'group_replication_recovery_compression_algorithms';
    public const GROUP_REPLICATION_RECOVERY_GET_PUBLIC_KEY = 'group_replication_recovery_get_public_key';
    public const GROUP_REPLICATION_RECOVERY_PUBLIC_KEY_PATH = 'group_replication_recovery_public_key_path';
    public const GROUP_REPLICATION_RECOVERY_RECONNECT_INTERVAL = 'group_replication_recovery_reconnect_interval';
    public const GROUP_REPLICATION_RECOVERY_RETRY_COUNT = 'group_replication_recovery_retry_count';
    public const GROUP_REPLICATION_RECOVERY_SSL_CA = 'group_replication_recovery_ssl_ca';
    public const GROUP_REPLICATION_RECOVERY_SSL_CAPATH = 'group_replication_recovery_ssl_capath';
    public const GROUP_REPLICATION_RECOVERY_SSL_CERT = 'group_replication_recovery_ssl_cert';
    public const GROUP_REPLICATION_RECOVERY_SSL_CIPHER = 'group_replication_recovery_ssl_cipher';
    public const GROUP_REPLICATION_RECOVERY_SSL_CRL = 'group_replication_recovery_ssl_crl';
    public const GROUP_REPLICATION_RECOVERY_SSL_CRLPATH = 'group_replication_recovery_ssl_crlpath';
    public const GROUP_REPLICATION_RECOVERY_SSL_KEY = 'group_replication_recovery_ssl_key';
    public const GROUP_REPLICATION_RECOVERY_SSL_VERIFY_SERVER_CERT = 'group_replication_recovery_ssl_verify_server_cert';
    public const GROUP_REPLICATION_RECOVERY_TLS_CIPHERSUITES = 'group_replication_recovery_tls_ciphersuites';
    public const GROUP_REPLICATION_RECOVERY_TLS_VERSION = 'group_replication_recovery_tls_version';
    public const GROUP_REPLICATION_RECOVERY_USE_SSL = 'group_replication_recovery_use_ssl';
    public const GROUP_REPLICATION_RECOVERY_ZSTD_COMPRESSION_LEVEL = 'group_replication_recovery_zstd_compression_level';
    public const GROUP_REPLICATION_SINGLE_PRIMARY_MODE = 'group_replication_single_primary_mode';
    public const GROUP_REPLICATION_SSL_MODE = 'group_replication_ssl_mode';
    public const GROUP_REPLICATION_START_ON_BOOT = 'group_replication_start_on_boot';
    public const GROUP_REPLICATION_TLS_SOURCE = 'group_replication_tls_source';
    public const GROUP_REPLICATION_TRANSACTION_SIZE_LIMIT = 'group_replication_transaction_size_limit';
    public const GROUP_REPLICATION_UNREACHABLE_MAJORITY_TIMEOUT = 'group_replication_unreachable_majority_timeout';
    public const GROUP_REPLICATION_VIEW_CHANGE_UUID = 'group_replication_view_change_uuid';

    public const GTID_EXECUTED = 'gtid_executed';
    public const GTID_EXECUTED_COMPRESSION_PERIOD = 'gtid_executed_compression_period';
    public const GTID_MODE = 'gtid_mode';
    public const GTID_NEXT = 'gtid_next';
    public const GTID_OWNED = 'gtid_owned';
    public const GTID_PURGED = 'gtid_purged';

    public const HAVE_COMPRESS = 'have_compress';
    public const HAVE_CRYPT = 'have_crypt';
    public const HAVE_DYNAMIC_LOADING = 'have_dynamic_loading';
    public const HAVE_GEOMETRY = 'have_geometry';
    public const HAVE_OPENSSL = 'have_openssl';
    public const HAVE_PROFILING = 'have_profiling';
    public const HAVE_QUERY_CACHE = 'have_query_cache';
    public const HAVE_RTREE_KEYS = 'have_rtree_keys';
    public const HAVE_SSL = 'have_ssl';
    public const HAVE_STATEMENT_TIMEOUT = 'have_statement_timeout';
    public const HAVE_SYMLINK = 'have_symlink';

    public const HISTOGRAM_GENERATION_MAX_MEM_SIZE = 'histogram_generation_max_mem_size';
    public const HOST_CACHE_SIZE = 'host_cache_size';
    public const HOSTNAME = 'hostname';
    public const IDENTITY = 'identity';
    public const IGNORE_BUILTIN_INNODB = 'ignore_builtin_innodb';
    public const IGNORE_DB_DIRS = 'ignore_db_dirs'; // removed in 8.0
    public const IMMEDIATE_SERVER_VERSION = 'immediate_server_version';

    public const INFORMATION_SCHEMA_STATS = 'information_schema_stats'; // removed in 8.0
    public const INFORMATION_SCHEMA_STATS_EXPIRY = 'information_schema_stats_expiry';

    public const INIT_CONNECT = 'init_connect';
    public const INIT_FILE = 'init_file';
    public const INIT_SLAVE = 'init_slave';
    public const INIT_REPLICA = 'init_replica';

    public const INNODB_ADAPTIVE_FLUSHING = 'innodb_adaptive_flushing';
    public const INNODB_ADAPTIVE_FLUSHING_LWM = 'innodb_adaptive_flushing_lwm';
    public const INNODB_ADAPTIVE_HASH_INDEX = 'innodb_adaptive_hash_index';
    public const INNODB_ADAPTIVE_HASH_INDEX_PARTS = 'innodb_adaptive_hash_index_parts';
    public const INNODB_ADAPTIVE_MAX_SLEEP_DELAY = 'innodb_adaptive_max_sleep_delay';
    public const INNODB_API_BK_COMMIT_INTERVAL = 'innodb_api_bk_commit_interval';
    public const INNODB_API_DISABLE_ROWLOCK = 'innodb_api_disable_rowlock';
    public const INNODB_API_ENABLE_BINLOG = 'innodb_api_enable_binlog';
    public const INNODB_API_ENABLE_MDL = 'innodb_api_enable_mdl';
    public const INNODB_API_TRX_LEVEL = 'innodb_api_trx_level';
    public const INNODB_AUTOEXTEND_INCREMENT = 'innodb_autoextend_increment';
    public const INNODB_AUTOINC_LOCK_MODE = 'innodb_autoinc_lock_mode';
    public const INNODB_BACKGROUND_DROP_LIST_EMPTY = 'innodb_background_drop_list_empty';
    public const INNODB_BUF_FLUSH_LIST_NOW = 'innodb_buf_flush_list_now';
    public const INNODB_BUFFER_POOL_CHUNK_SIZE = 'innodb_buffer_pool_chunk_size';
    public const INNODB_BUFFER_POOL_DEBUG = 'innodb_buffer_pool_debug';
    public const INNODB_BUFFER_POOL_DUMP_AT_SHUTDOWN = 'innodb_buffer_pool_dump_at_shutdown';
    public const INNODB_BUFFER_POOL_DUMP_NOW = 'innodb_buffer_pool_dump_now';
    public const INNODB_BUFFER_POOL_DUMP_PCT = 'innodb_buffer_pool_dump_pct'; // added in 5.7
    public const INNODB_BUFFER_POOL_EVICT = 'innodb_buffer_pool_evict';
    public const INNODB_BUFFER_POOL_FILENAME = 'innodb_buffer_pool_filename';
    public const INNODB_BUFFER_POOL_IN_CORE_FILE = 'innodb_buffer_pool_in_core_file';
    public const INNODB_BUFFER_POOL_INSTANCES = 'innodb_buffer_pool_instances';
    public const INNODB_BUFFER_POOL_LOAD_ABORT = 'innodb_buffer_pool_load_abort';
    public const INNODB_BUFFER_POOL_LOAD_AT_STARTUP = 'innodb_buffer_pool_load_at_startup';
    public const INNODB_BUFFER_POOL_LOAD_NOW = 'innodb_buffer_pool_load_now';
    public const INNODB_BUFFER_POOL_SIZE = 'innodb_buffer_pool_size';
    public const INNODB_CHANGE_BUFFER_MAX_SIZE = 'innodb_change_buffer_max_size';
    public const INNODB_CHANGE_BUFFERING = 'innodb_change_buffering';
    public const INNODB_CHANGE_BUFFERING_DEBUG = 'innodb_change_buffering_debug';
    public const INNODB_CHECKPOINT_DISABLED = 'innodb_checkpoint_disabled';
    public const INNODB_CHECKSUM_ALGORITHM = 'innodb_checksum_algorithm';
    public const INNODB_CHECKSUMS = 'innodb_checksums';
    public const INNODB_CMP_PER_INDEX_ENABLED = 'innodb_cmp_per_index_enabled';
    public const INNODB_COMMIT_CONCURRENCY = 'innodb_commit_concurrency';
    public const INNODB_COMPRESSION_FAILURE_THRESHOLD_PCT = 'innodb_compression_failure_threshold_pct';
    public const INNODB_COMPRESSION_LEVEL = 'innodb_compression_level';
    public const INNODB_COMPRESSION_PAD_PCT_MAX = 'innodb_compression_pad_pct_max';
    public const INNODB_COMPRESS_DEBUG = 'innodb_compress_debug';
    public const INNODB_CONCURRENCY_TICKETS = 'innodb_concurrency_tickets';
    public const INNODB_DATA_FILE_PATH = 'innodb_data_file_path';
    public const INNODB_DATA_HOME_DIR = 'innodb_data_home_dir';
    public const INNODB_DEADLOCK_DETECT = 'innodb_deadlock_detect';
    public const INNODB_DDL_BUFFER_SIZE = 'innodb_ddl_buffer_size';
    public const INNODB_DDL_LOG_CRASH_RESET_DEBUG = 'innodb_ddl_log_crash_reset_debug';
    public const INNODB_DDL_THREADS = 'innodb_ddl_threads';
    public const INNODB_DEDICATED_SERVER = 'innodb_dedicated_server';
    public const INNODB_DEFAULT_ROW_FORMAT = 'innodb_default_row_format'; // added in 5.7
    public const INNODB_DICT_STATS_DISABLED_DEBUG = 'innodb_dict_stats_disabled_debug';
    public const INNODB_DIRECTORIES = 'innodb_directories';
    public const INNODB_DISABLE_BACKGROUND_MERGE = 'innodb_disable_background_merge';
    public const INNODB_DISABLE_SORT_FILE_CACHE = 'innodb_disable_sort_file_cache';
    public const INNODB_DOUBLEWRITE = 'innodb_doublewrite';
    public const INNODB_DOUBLEWRITE_BATCH_SIZE = 'innodb_doublewrite_batch_size';
    public const INNODB_DOUBLEWRITE_DIR = 'innodb_doublewrite_dir';
    public const INNODB_DOUBLEWRITE_FILES = 'innodb_doublewrite_files';
    public const INNODB_DOUBLEWRITE_PAGES = 'innodb_doublewrite_pages';
    public const INNODB_EXTEND_AND_INITIALIZE = 'innodb_extend_and_initialize';
    public const INNODB_FAST_SHUTDOWN = 'innodb_fast_shutdown';
    public const INNODB_FILE_FORMAT = 'innodb_file_format';
    public const INNODB_FILE_FORMAT_CHECK = 'innodb_file_format_check';
    public const INNODB_FILE_FORMAT_MAX = 'innodb_file_format_max';
    public const INNODB_FILE_PER_TABLE = 'innodb_file_per_table';
    public const INNODB_FILL_FACTOR = 'innodb_fill_factor'; // added in 5.7
    public const INNODB_FIL_MAKE_PAGE_DIRTY_DEBUG = 'innodb_fil_make_page_dirty_debug';
    public const INNODB_FLUSH_LOG_AT_TIMEOUT = 'innodb_flush_log_at_timeout';
    public const INNODB_FLUSH_LOG_AT_TRX_COMMIT = 'innodb_flush_log_at_trx_commit';
    public const INNODB_FLUSH_METHOD = 'innodb_flush_method';
    public const INNODB_FLUSH_NEIGHBORS = 'innodb_flush_neighbors';
    public const INNODB_FLUSH_SYNC = 'innodb_flush_sync';
    public const INNODB_FLUSHING_AVG_LOOPS = 'innodb_flushing_avg_loops';
    public const INNODB_FORCE_LOAD_CORRUPTED = 'innodb_force_load_corrupted';
    public const INNODB_FORCE_RECOVERY = 'innodb_force_recovery';
    public const INNODB_FORCE_RECOVERY_CRASH = 'innodb_force_recovery_crash';
    public const INNODB_FSYNC_THRESHOLD = 'innodb_fsync_threshold';
    public const INNODB_FT_AUX_TABLE = 'innodb_ft_aux_table';
    public const INNODB_FT_CACHE_SIZE = 'innodb_ft_cache_size';
    public const INNODB_FT_ENABLE_DIAG_PRINT = 'innodb_ft_enable_diag_print';
    public const INNODB_FT_ENABLE_STOPWORD = 'innodb_ft_enable_stopword';
    public const INNODB_FT_MAX_TOKEN_SIZE = 'innodb_ft_max_token_size';
    public const INNODB_FT_MIN_TOKEN_SIZE = 'innodb_ft_min_token_size';
    public const INNODB_FT_NUM_WORD_OPTIMIZE = 'innodb_ft_num_word_optimize';
    public const INNODB_FT_RESULT_CACHE_LIMIT = 'innodb_ft_result_cache_limit';
    public const INNODB_FT_SERVER_STOPWORD_TABLE = 'innodb_ft_server_stopword_table';
    public const INNODB_FT_SORT_PLL_DEGREE = 'innodb_ft_sort_pll_degree';
    public const INNODB_FT_TOTAL_CACHE_SIZE = 'innodb_ft_total_cache_size';
    public const INNODB_FT_USER_STOPWORD_TABLE = 'innodb_ft_user_stopword_table';
    public const INNODB_IDLE_FLUSH_PCT = 'innodb_idle_flush_pct';
    public const INNODB_INTERPRETER = 'innodb_interpreter';
    public const INNODB_INTERPRETER_OUTPUT = 'innodb_interpreter_output';
    public const INNODB_IO_CAPACITY = 'innodb_io_capacity';
    public const INNODB_IO_CAPACITY_MAX = 'innodb_io_capacity_max';
    public const INNODB_LARGE_PREFIX = 'innodb_large_prefix';
    public const INNODB_LIMIT_OPTIMISTIC_INSERT_DEBUG = 'innodb_limit_optimistic_insert_debug';
    public const INNODB_LOCK_WAIT_TIMEOUT = 'innodb_lock_wait_timeout';
    public const INNODB_LOCKS_UNSAFE_FOR_BINLOG = 'innodb_locks_unsafe_for_binlog';
    public const INNODB_LOG_BUFFER_SIZE = 'innodb_log_buffer_size';
    public const INNODB_LOG_CHECKPOINT_FUZZY_NOW = 'innodb_log_checkpoint_fuzzy_now';
    public const INNODB_LOG_CHECKPOINT_NOW = 'innodb_log_checkpoint_now';
    public const INNODB_LOG_CHECKSUMS = 'innodb_log_checksums';
    public const INNODB_LOG_COMPRESSED_PAGES = 'innodb_log_compressed_pages';
    public const INNODB_LOG_FILE_SIZE = 'innodb_log_file_size';
    public const INNODB_LOG_FILES_IN_GROUP = 'innodb_log_files_in_group';
    public const INNODB_LOG_FLUSH_NOW = 'innodb_log_flush_now';
    public const INNODB_LOG_GROUP_HOME_DIR = 'innodb_log_group_home_dir';
    public const INNODB_LOG_SPIN_CPU_ABS_LWM = 'innodb_log_spin_cpu_abs_lwm';
    public const INNODB_LOG_SPIN_CPU_PCT_HWM = 'innodb_log_spin_cpu_pct_hwm';
    public const INNODB_LOG_WAIT_FOR_FLUSH_SPIN_HWM = 'innodb_log_wait_for_flush_spin_hwm';
    public const INNODB_LOG_WRITE_AHEAD_SIZE = 'innodb_log_write_ahead_size';
    public const INNODB_LOG_WRITER_THREADS = 'innodb_log_writer_threads';
    public const INNODB_LRU_SCAN_DEPTH = 'innodb_lru_scan_depth';
    public const INNODB_MASTER_THREAD_DISABLED_DEBUG = 'innodb_master_thread_disabled_debug';
    public const INNODB_MAX_DIRTY_PAGES_PCT = 'innodb_max_dirty_pages_pct';
    public const INNODB_MAX_DIRTY_PAGES_PCT_LWM = 'innodb_max_dirty_pages_pct_lwm';
    public const INNODB_MAX_PURGE_LAG = 'innodb_max_purge_lag';
    public const INNODB_MAX_PURGE_LAG_DELAY = 'innodb_max_purge_lag_delay';
    public const INNODB_MAX_UNDO_LOG_SIZE = 'innodb_max_undo_log_size';
    public const INNODB_MERGE_THRESHOLD_SET_ALL_DEBUG = 'innodb_merge_threshold_set_all_debug';
    public const INNODB_MONITOR_DISABLE = 'innodb_monitor_disable';
    public const INNODB_MONITOR_ENABLE = 'innodb_monitor_enable';
    public const INNODB_MONITOR_RESET = 'innodb_monitor_reset';
    public const INNODB_MONITOR_RESET_ALL = 'innodb_monitor_reset_all';
    public const INNODB_NUMA_INTERLEAVE = 'innodb_numa_interleave';
    public const INNODB_OLD_BLOCKS_PCT = 'innodb_old_blocks_pct';
    public const INNODB_OLD_BLOCKS_TIME = 'innodb_old_blocks_time';
    public const INNODB_ONLINE_ALTER_LOG_MAX_SIZE = 'innodb_online_alter_log_max_size';
    public const INNODB_OPEN_FILES = 'innodb_open_files';
    public const INNODB_OPTIMIZE_FULLTEXT_ONLY = 'innodb_optimize_fulltext_only';
    public const INNODB_PAGE_CLEANERS = 'innodb_page_cleaners'; // added in 5.7
    public const INNODB_PAGE_CLEANER_DISABLED_DEBUG = 'innodb_page_cleaner_disabled_debug';
    public const INNODB_PAGE_HASH_LOCKS = 'innodb_page_hash_locks';
    public const INNODB_PAGE_SIZE = 'innodb_page_size';
    public const INNODB_PARALLEL_READ_THREADS = 'innodb_parallel_read_threads';
    public const INNODB_PRINT_ALL_DEADLOCKS = 'innodb_print_all_deadlocks';
    public const INNODB_PRINT_DDL_LOGS = 'innodb_print_ddl_logs';
    public const INNODB_PURGE_BATCH_SIZE = 'innodb_purge_batch_size';
    public const INNODB_PURGE_RSEG_TRUNCATE_FREQUENCY = 'innodb_purge_rseg_truncate_frequency';
    public const INNODB_PURGE_RUN_NOW = 'innodb_purge_run_now';
    public const INNODB_PURGE_STOP_NOW = 'innodb_purge_stop_now';
    public const INNODB_PURGE_THREADS = 'innodb_purge_threads';
    public const INNODB_RANDOM_READ_AHEAD = 'innodb_random_read_ahead';
    public const INNODB_READ_AHEAD_THRESHOLD = 'innodb_read_ahead_threshold';
    public const INNODB_READ_IO_THREADS = 'innodb_read_io_threads';
    public const INNODB_READ_ONLY = 'innodb_read_only';
    public const INNODB_REDO_LOG_ARCHIVE_DIRS = 'innodb_redo_log_archive_dirs';
    public const INNODB_REDO_LOG_CAPACITY = 'innodb_redo_log_capacity';
    public const INNODB_REDO_LOG_ENCRYPT = 'innodb_redo_log_encrypt';
    public const INNODB_REPLICATION_DELAY = 'innodb_replication_delay';
    public const INNODB_ROLLBACK_ON_TIMEOUT = 'innodb_rollback_on_timeout';
    public const INNODB_ROLLBACK_SEGMENTS = 'innodb_rollback_segments';
    public const INNODB_SAVED_PAGE_NUMBER_DEBUG = 'innodb_saved_page_number_debug';
    public const INNODB_SCAN_DIRECTORIES = 'innodb_scan_directories';
    public const INNODB_SEGMENT_RESERVE_FACTOR = 'innodb_segment_reserve_factor';
    public const INNODB_SEMAPHORE_WAIT_TIMEOUT_DEBUG = 'innodb_semaphore_wait_timeout_debug';
    public const INNODB_SORT_BUFFER_SIZE = 'innodb_sort_buffer_size';
    public const INNODB_SPIN_WAIT_DELAY = 'innodb_spin_wait_delay';
    public const INNODB_SPIN_WAIT_PAUSE_MULTIPLIER = 'innodb_spin_wait_pause_multiplier';
    public const INNODB_STATS_AUTO_RECALC = 'innodb_stats_auto_recalc';
    public const INNODB_STATS_INCLUDE_DELETE_MARKED = 'innodb_stats_include_delete_marked';
    public const INNODB_STATS_METHOD = 'innodb_stats_method';
    public const INNODB_STATS_ON_METADATA = 'innodb_stats_on_metadata';
    public const INNODB_STATS_PERSISTENT = 'innodb_stats_persistent';
    public const INNODB_STATS_PERSISTENT_SAMPLE_PAGES = 'innodb_stats_persistent_sample_pages';
    public const INNODB_STATS_SAMPLE_PAGES = 'innodb_stats_sample_pages';
    public const INNODB_STATS_TRANSIENT_SAMPLE_PAGES = 'innodb_stats_transient_sample_pages';
    public const INNODB_STATUS_OUTPUT = 'innodb_status_output';
    public const INNODB_STATUS_OUTPUT_LOCKS = 'innodb_status_output_locks';
    public const INNODB_STRICT_MODE = 'innodb_strict_mode';
    public const INNODB_SUPPORT_XA = 'innodb_support_xa';
    public const INNODB_SYNC_ARRAY_SIZE = 'innodb_sync_array_size';
    public const INNODB_SYNC_DEBUG = 'innodb_sync_debug';
    public const INNODB_SYNC_SPIN_LOOPS = 'innodb_sync_spin_loops';
    public const INNODB_TABLE_LOCKS = 'innodb_table_locks';
    public const INNODB_TEMP_DATA_FILE_PATH = 'innodb_temp_data_file_path';
    public const INNODB_TEMP_TABLESPACES_DIR = 'innodb_temp_tablespaces_dir';
    public const INNODB_THREAD_CONCURRENCY = 'innodb_thread_concurrency';
    public const INNODB_THREAD_SLEEP_DELAY = 'innodb_thread_sleep_delay';
    public const INNODB_TMPDIR = 'innodb_tmpdir';
    public const INNODB_TRX_PURGE_VIEW_UPDATE_ONLY_DEBUG = 'innodb_trx_purge_view_update_only_debug';
    public const INNODB_TRX_RSEG_N_SLOTS_DEBUG = 'innodb_trx_rseg_n_slots_debug';
    public const INNODB_UNDO_DIRECTORY = 'innodb_undo_directory';
    public const INNODB_UNDO_LOG_ENCRYPT = 'innodb_undo_log_encrypt';
    public const INNODB_UNDO_LOG_TRUNCATE = 'innodb_undo_log_truncate';
    public const INNODB_UNDO_LOGS = 'innodb_undo_logs';
    public const INNODB_UNDO_TABLESPACES = 'innodb_undo_tablespaces';
    public const INNODB_USE_FDATASYNC = 'innodb_use_fdatasync';
    public const INNODB_USE_NATIVE_AIO = 'innodb_use_native_aio';
    public const INNODB_VALIDATE_TABLESPACE_PATHS = 'innodb_validate_tablespace_paths';
    public const INNODB_VERSION = 'innodb_version';
    public const INNODB_WRITE_IO_THREADS = 'innodb_write_io_threads';

    public const INSERT_ID = 'insert_id';
    public const INTERACTIVE_TIMEOUT = 'interactive_timeout';
    public const INTERNAL_TMP_DISK_STORAGE_ENGINE = 'internal_tmp_disk_storage_engine';
    public const INTERNAL_TMP_MEM_STORAGE_ENGINE = 'internal_tmp_mem_storage_engine';
    public const JOIN_BUFFER_SIZE = 'join_buffer_size';
    public const KEEP_FILES_ON_CREATE = 'keep_files_on_create';

    public const KEY_BUFFER_SIZE = 'key_buffer_size';
    public const KEY_CACHE_AGE_THRESHOLD = 'key_cache_age_threshold';
    public const KEY_CACHE_BLOCK_SIZE = 'key_cache_block_size';
    public const KEY_CACHE_DIVISION_LIMIT = 'key_cache_division_limit';

    public const KEYRING_AWS_CMK_ID = 'keyring_aws_cmk_id';
    public const KEYRING_AWS_CONF_FILE = 'keyring_aws_conf_file';
    public const KEYRING_AWS_DATA_FILE = 'keyring_aws_data_file';
    public const KEYRING_AWS_REGION = 'keyring_aws_region';
    public const KEYRING_ENCRYPTED_FILE_DATA = 'keyring_encrypted_file_data';
    public const KEYRING_ENCRYPTED_FILE_PASSWORD = 'keyring_encrypted_file_password';
    public const KEYRING_FILE_DATA = 'keyring_file_data';
    public const KEYRING_HASHICORP_AUTH_PATH = 'keyring_hashicorp_auth_path';
    public const KEYRING_HASHICORP_CA_PATH = 'keyring_hashicorp_ca_path';
    public const KEYRING_HASHICORP_CACHING = 'keyring_hashicorp_caching';
    public const KEYRING_HASHICORP_COMMIT_AUTH_PATH = 'keyring_hashicorp_commit_auth_path';
    public const KEYRING_HASHICORP_COMMIT_CA_PATH = 'keyring_hashicorp_commit_ca_path';
    public const KEYRING_HASHICORP_COMMIT_CACHING = 'keyring_hashicorp_commit_caching';
    public const KEYRING_HASHICORP_COMMIT_ROLE_ID = 'keyring_hashicorp_commit_role_id';
    public const KEYRING_HASHICORP_COMMIT_SERVER_URL = 'keyring_hashicorp_commit_server_url';
    public const KEYRING_HASHICORP_COMMIT_STORE_PATH = 'keyring_hashicorp_commit_store_path';
    public const KEYRING_HASHICORP_ROLE_ID = 'keyring_hashicorp_role_id';
    public const KEYRING_HASHICORP_SECRET_ID = 'keyring_hashicorp_secret_id';
    public const KEYRING_HASHICORP_SERVER_URL = 'keyring_hashicorp_server_url';
    public const KEYRING_HASHICORP_STORE_PATH = 'keyring_hashicorp_store_path';
    public const KEYRING_OCI_CA_CERTIFICATE = 'keyring_oci_ca_certificate';
    public const KEYRING_OCI_COMPARTMENT = 'keyring_oci_compartment';
    public const KEYRING_OCI_ENCRYPTION_ENDPOINT = 'keyring_oci_encryption_endpoint';
    public const KEYRING_OCI_KEY_FILE = 'keyring_oci_key_file';
    public const KEYRING_OCI_KEY_FINGERPRINT = 'keyring_oci_key_fingerprint';
    public const KEYRING_OCI_MANAGEMENT_ENDPOINT = 'keyring_oci_management_endpoint';
    public const KEYRING_OCI_MASTER_KEY = 'keyring_oci_master_key';
    public const KEYRING_OCI_SECRETS_ENDPOINT = 'keyring_oci_secrets_endpoint';
    public const KEYRING_OCI_TENANCY = 'keyring_oci_tenancy';
    public const KEYRING_OCI_USER = 'keyring_oci_user';
    public const KEYRING_OCI_VAULTS_ENDPOINT = 'keyring_oci_vaults_endpoint';
    public const KEYRING_OCI_VIRTUAL_VAULT = 'keyring_oci_virtual_vault';
    public const KEYRING_OKV_CONF_DIR = 'keyring_okv_conf_dir';
    public const KEYRING_OPERATIONS = 'keyring_operations';

    public const LARGE_FILES_SUPPORT = 'large_files_support';
    public const LARGE_PAGE_SIZE = 'large_page_size';
    public const LARGE_PAGES = 'large_pages';

    public const LAST_INSERT_ID = 'last_insert_id';

    public const LC_MESSAGES = 'lc_messages';
    public const LC_MESSAGES_DIR = 'lc_messages_dir';
    public const LC_TIME_NAMES = 'lc_time_names';

    public const LICENSE = 'license';
    public const LOCAL_INFILE = 'local_infile';

    public const LOCK_ORDER = 'lock_order';
    public const LOCK_ORDER_DEBUG_LOOP = 'lock_order_debug_loop';
    public const LOCK_ORDER_DEBUG_MISSING_ARC = 'lock_order_debug_missing_arc';
    public const LOCK_ORDER_DEBUG_MISSING_KEY = 'lock_order_debug_missing_key';
    public const LOCK_ORDER_DEBUG_MISSING_UNLOCK = 'lock_order_debug_missing_unlock';
    public const LOCK_ORDER_DEPENDENCIES = 'lock_order_dependencies';
    public const LOCK_ORDER_EXTRA_DEPENDENCIES = 'lock_order_extra_dependencies';
    public const LOCK_ORDER_OUTPUT_DIRECTORY = 'lock_order_output_directory';
    public const LOCK_ORDER_PRINT_TXT = 'lock_order_print_txt';
    public const LOCK_ORDER_TRACE_LOOP = 'lock_order_trace_loop';
    public const LOCK_ORDER_TRACE_MISSING_ARC = 'lock_order_trace_missing_arc';
    public const LOCK_ORDER_TRACE_MISSING_KEY = 'lock_order_trace_missing_key';
    public const LOCK_ORDER_TRACE_MISSING_UNLOCK = 'lock_order_trace_missing_unlock';

    public const LOCK_WAIT_TIMEOUT = 'lock_wait_timeout';
    public const LOCKED_IN_MEMORY = 'locked_in_memory';

    public const LOG_BIN = 'log_bin';
    public const LOG_BIN_BASENAME = 'log_bin_basename';
    public const LOG_BIN_INDEX = 'log_bin_index';
    public const LOG_BIN_TRUST_FUNCTION_CREATORS = 'log_bin_trust_function_creators';
    public const LOG_BIN_USE_V1_ROW_EVENTS = 'log_bin_use_v1_row_events';
    public const LOG_BUILTIN_AS_IDENTIFIED_BY_PASSWORD = 'log_builtin_as_identified_by_password';
    public const LOG_ERROR = 'log_error';
    public const LOG_ERROR_SERVICES = 'log_error_services';
    public const LOG_ERROR_SUPPRESSION_LIST = 'log_error_suppression_list';
    public const LOG_ERROR_VERBOSITY = 'log_error_verbosity';
    public const LOG_OUTPUT = 'log_output';
    public const LOG_QUERIES_NOT_USING_INDEXES = 'log_queries_not_using_indexes';
    public const LOG_RAW = 'log_raw';
    public const LOG_REPLICA_UPDATES = 'log_replica_updates';
    public const LOG_SLAVE_UPDATES = 'log_slave_updates';
    public const LOG_SLOW_ADMIN_STATEMENTS = 'log_slow_admin_statements';
    public const LOG_SLOW_EXTRA = 'log_slow_extra';
    public const LOG_SLOW_SLAVE_STATEMENTS = 'log_slow_slave_statements';
    public const LOG_SLOW_REPLICA_STATEMENTS = 'log_slow_replica_statements';
    public const LOG_STATEMENTS_UNSAFE_FOR_BINLOG = 'log_statements_unsafe_for_binlog';
    public const LOG_SYSLOG = 'log_syslog';
    public const LOG_SYSLOG_FACILITY = 'log_syslog_facility';
    public const LOG_SYSLOG_INCLUDE_PID = 'log_syslog_include_pid';
    public const LOG_SYSLOG_TAG = 'log_syslog_tag';
    public const LOG_THROTTLE_QUERIES_NOT_USING_INDEXES = 'log_throttle_queries_not_using_indexes';
    public const LOG_TIMESTAMPS = 'log_timestamps';
    public const LOG_WARNINGS = 'log_warnings'; // removed in 8.0

    public const LONG_QUERY_TIME = 'long_query_time';
    public const LOW_PRIORITY_UPDATES = 'low_priority_updates';
    public const LOWER_CASE_FILE_SYSTEM = 'lower_case_file_system';
    public const LOWER_CASE_TABLE_NAMES = 'lower_case_table_names';
    public const MANDATORY_ROLES = 'mandatory_roles';
    public const MASTER_INFO_REPOSITORY = 'master_info_repository';
    public const MASTER_VERIFY_CHECKSUM = 'master_verify_checksum';

    public const MAX_ALLOWED_PACKET = 'max_allowed_packet';
    public const MAX_BINLOG_CACHE_SIZE = 'max_binlog_cache_size';
    public const MAX_BINLOG_SIZE = 'max_binlog_size';
    public const MAX_BINLOG_STMT_CACHE_SIZE = 'max_binlog_stmt_cache_size';
    public const MAX_CONNECT_ERRORS = 'max_connect_errors';
    public const MAX_CONNECTIONS = 'max_connections';
    public const MAX_DELAYED_THREADS = 'max_delayed_threads';
    public const MAX_DIGEST_LENGTH = 'max_digest_length';
    public const MAX_ERROR_COUNT = 'max_error_count';
    public const MAX_EXECUTION_TIME = 'max_execution_time';
    public const MAX_HEAP_TABLE_SIZE = 'max_heap_table_size';
    public const MAX_INSERT_DELAYED_THREADS = 'max_insert_delayed_threads';
    public const MAX_JOIN_SIZE = 'max_join_size';
    public const MAX_LENGTH_FOR_SORT_DATA = 'max_length_for_sort_data';
    public const MAX_POINTS_IN_GEOMETRY = 'max_points_in_geometry';
    public const MAX_PREPARED_STMT_COUNT = 'max_prepared_stmt_count';
    public const MAX_RELAY_LOG_SIZE = 'max_relay_log_size';
    public const MAX_SEEKS_FOR_KEY = 'max_seeks_for_key';
    public const MAX_SORT_LENGTH = 'max_sort_length';
    public const MAX_SP_RECURSION_DEPTH = 'max_sp_recursion_depth';
    public const MAX_TMP_TABLES = 'max_tmp_tables'; // removed in 8.0
    public const MAX_USER_CONNECTIONS = 'max_user_connections';
    public const MAX_WRITE_LOCK_COUNT = 'max_write_lock_count';

    public const MECAB_RC_FILE = 'mecab_rc_file';

    public const METADATA_LOCKS_CACHE_SIZE = 'metadata_locks_cache_size';
    public const METADATA_LOCKS_HASH_INSTANCES = 'metadata_locks_hash_instances';

    public const MIN_EXAMINED_ROW_LIMIT = 'min_examined_row_limit';
    public const MULTI_RANGE_COUNT = 'multi_range_count'; // removed in 8.0

    public const MYISAM_DATA_POINTER_SIZE = 'myisam_data_pointer_size';
    public const MYISAM_MAX_SORT_FILE_SIZE = 'myisam_max_sort_file_size';
    public const MYISAM_MMAP_SIZE = 'myisam_mmap_size';
    public const MYISAM_RECOVER_OPTIONS = 'myisam_recover_options';
    public const MYISAM_REPAIR_THREADS = 'myisam_repair_threads';
    public const MYISAM_SORT_BUFFER_SIZE = 'myisam_sort_buffer_size';
    public const MYISAM_STATS_METHOD = 'myisam_stats_method';
    public const MYISAM_USE_MMAP = 'myisam_use_mmap';

    public const MYSQL_NATIVE_PASSWORD_PROXY_USERS = 'mysql_native_password_proxy_users';

    public const MYSQLX_BIND_ADDRESS = 'mysqlx_bind_address';
    public const MYSQLX_COMPRESSION_ALGORITHMS = 'mysqlx_compression_algorithms';
    public const MYSQLX_CONNECT_TIMEOUT = 'mysqlx_connect_timeout';
    public const MYSQLX_DEFLATE_DEFAULT_COMPRESSION_LEVEL = 'mysqlx_deflate_default_compression_level';
    public const MYSQLX_DEFLATE_MAX_CLIENT_COMPRESSION_LEVEL = 'mysqlx_deflate_max_client_compression_level';
    public const MYSQLX_DOCUMENT_ID_UNIQUE_PREFIX = 'mysqlx_document_id_unique_prefix';
    public const MYSQLX_ENABLE_HELLO_NOTICE = 'mysqlx_enable_hello_notice';
    public const MYSQLX_IDLE_WORKER_THREAD_TIMEOUT = 'mysqlx_idle_worker_thread_timeout';
    public const MYSQLX_INTERACTIVE_TIMEOUT = 'mysqlx_interactive_timeout';
    public const MYSQLX_LZ4_DEFAULT_COMPRESSION_LEVEL = 'mysqlx_lz4_default_compression_level';
    public const MYSQLX_LZ4_MAX_CLIENT_COMPRESSION_LEVEL = 'mysqlx_lz4_max_client_compression_level';
    public const MYSQLX_MAX_ALLOWED_PACKET = 'mysqlx_max_allowed_packet';
    public const MYSQLX_MAX_CONNECTIONS = 'mysqlx_max_connections';
    public const MYSQLX_MIN_WORKER_THREADS = 'mysqlx_min_worker_threads';
    public const MYSQLX_PORT = 'mysqlx_port';
    public const MYSQLX_PORT_OPEN_TIMEOUT = 'mysqlx_port_open_timeout';
    public const MYSQLX_READ_TIMEOUT = 'mysqlx_read_timeout';
    public const MYSQLX_SOCKET = 'mysqlx_socket';
    public const MYSQLX_SSL_CAPATH = 'mysqlx_ssl_capath';
    public const MYSQLX_SSL_CA = 'mysqlx_ssl_ca';
    public const MYSQLX_SSL_CERT = 'mysqlx_ssl_cert';
    public const MYSQLX_SSL_CIPHER = 'mysqlx_ssl_cipher';
    public const MYSQLX_SSL_CRLPATH = 'mysqlx_ssl_crlpath';
    public const MYSQLX_SSL_CRL = 'mysqlx_ssl_crl';
    public const MYSQLX_SSL_KEY = 'mysqlx_ssl_key';
    public const MYSQLX_WAIT_TIMEOUT = 'mysqlx_wait_timeout';
    public const MYSQLX_WRITE_TIMEOUT = 'mysqlx_write_timeout';
    public const MYSQLX_ZSTD_DEFAULT_COMPRESSION_LEVEL = 'mysqlx_zstd_default_compression_level';
    public const MYSQLX_ZSTD_MAX_CLIENT_COMPRESSION_LEVEL = 'mysqlx_zstd_max_client_compression_level';

    public const NAMED_PIPE = 'named_pipe';
    public const NAMED_PIPE_FULL_ACCESS_GROUP = 'named_pipe_full_access_group';

    public const NDB_ALLOW_COPYING_ALTER_TABLE = 'ndb_allow_copying_alter_table';
    public const NDB_APPLIER_ALLOW_SKIP_EPOCH = 'ndb_applier_allow_skip_epoch';
    public const NDB_APPLIER_CONFLICT_ROLE = 'ndb_applier_conflict_role';
    public const NDB_AUTOINCREMENT_PREFETCH_SZ = 'ndb_autoincrement_prefetch_sz';
    public const NDB_BATCH_SIZE = 'ndb_batch_size';
    public const NDB_BLOB_READ_BATCH_BYTES = 'ndb_blob_read_batch_bytes';
    public const NDB_BLOB_WRITE_BATCH_BYTES = 'ndb_blob_write_batch_bytes';
    public const NDB_CACHE_CHECK_TIME = 'ndb_cache_check_time';
    public const NDB_CLEAR_APPLY_STATUS = 'ndb_clear_apply_status';
    public const NDB_CLUSTER_CONNECTION_POOL = 'ndb_cluster_connection_pool';
    public const NDB_CLUSTER_CONNECTION_POOL_NODEIDS = 'ndb_cluster_connection_pool_nodeids';
    public const NDB_CONFLICT_ROLE = 'ndb_conflict_role';
    public const NDB_DATA_NODE_NEIGHBOUR = 'ndb_data_node_neighbour';
    public const NDB_DBG_CHECK_SHARES = 'ndb_dbg_check_shares';
    public const NDB_DEFAULT_COLUMN_FORMAT = 'ndb_default_column_format';
    public const NDB_DEFERRED_CONSTRAINTS = 'ndb_deferred_constraints';
    public const NDB_DISTRIBUTION = 'ndb_distribution';
    public const NDB_EVENTBUFFER_FREE_PERCENT = 'ndb_eventbuffer_free_percent';
    public const NDB_EVENTBUFFER_MAX_ALLOC = 'ndb_eventbuffer_max_alloc';
    public const NDB_EXTRA_LOGGING = 'ndb_extra_logging';
    public const NDB_FORCE_SEND = 'ndb_force_send';
    public const NDB_FULLY_REPLICATED = 'ndb_fully_replicated';
    public const NDB_INDEX_STAT_ENABLE = 'ndb_index_stat_enable';
    public const NDB_INDEX_STAT_OPTION = 'ndb_index_stat_option';
    public const NDB_JOIN_PUSHDOWN = 'ndb_join_pushdown';
    public const NDB_LOG_APPLY_STATUS = 'ndb_log_apply_status';
    public const NDB_LOG_BIN = 'ndb_log_bin';
    public const NDB_LOG_BINLOG_INDEX = 'ndb_log_binlog_index';
    public const NDB_LOG_EMPTY_EPOCHS = 'ndb_log_empty_epochs';
    public const NDB_LOG_EMPTY_UPDATE = 'ndb_log_empty_update';
    public const NDB_LOG_EXCLUSIVE_READS = 'ndb_log_exclusive_reads';
    public const NDB_LOG_FAIL_TERMINATE = 'ndb_log_fail_terminate';
    public const NDB_LOG_ORIG = 'ndb_log_orig';
    public const NDB_LOG_TRANSACTION_ID = 'ndb_log_transaction_id';
    public const NDB_LOG_TRANSACTION_COMPRESSION = 'ndb_log_transaction_compression';
    public const NDB_LOG_TRANSACTION_COMPRESSION_LEVEL_ZSTD = 'ndb_log_transaction_compression_level_zstd';
    public const NDB_LOG_TRANSACTION_DEPENDENCY = 'ndb_log_transaction_dependency';
    public const NDB_LOG_UPDATE_AS_WRITE = 'ndb_log_update_as_write';
    public const NDB_LOG_UPDATE_MINIMAL = 'ndb_log_update_minimal';
    public const NDB_LOG_UPDATED_ONLY = 'ndb_log_updated_only';
    public const NDB_METADATA_CHECK = 'ndb_metadata_check';
    public const NDB_METADATA_CHECK_INTERVAL = 'ndb_metadata_check_interval';
    public const NDB_METADATA_SYNC = 'ndb_metadata_sync';
    public const NDB_OPTIMIZATION_DELAY = 'ndb_optimization_delay';
    public const NDB_OPTIMIZED_NODE_SELECTION = 'ndb_optimized_node_selection';
    public const NDB_READ_BACKUP = 'ndb_read_backup';
    public const NDB_RECV_THREAD_ACTIVATION_THRESHOLD = 'ndb_recv_thread_activation_threshold';
    public const NDB_RECV_THREAD_CPU_MASK = 'ndb_recv_thread_cpu_mask';
    public const NDB_REPLICA_BATCH_SIZE = 'ndb_replica_batch_size';
    public const NDB_REPLICA_BLOB_WRITE_BATCH_BYTES = 'ndb_replica_blob_write_batch_bytes';
    public const NDB_REPORT_THRESH_BINLOG_EPOCH_SLIP = 'ndb_report_thresh_binlog_epoch_slip';
    public const NDB_REPORT_THRESH_BINLOG_MEM_USAGE = 'ndb_report_thresh_binlog_mem_usage';
    public const NDB_ROW_CHECKSUM = 'ndb_row_checksum';
    public const NDB_SCHEMA_DIST_LOCK_WAIT_TIMEOUT = 'ndb_schema_dist_lock_wait_timeout';
    public const NDB_SCHEMA_DIST_TIMEOUT = 'ndb_schema_dist_timeout';
    public const NDB_SCHEMA_DIST_UPGRADE_ALLOWED = 'ndb_schema_dist_upgrade_allowed';
    public const NDB_SHOW_FOREIGN_KEY_MOCK_TABLES = 'ndb_show_foreign_key_mock_tables';
    public const NDB_SLAVE_CONFLICT_ROLE = 'ndb_slave_conflict_role';
    public const NDB_TABLE_NO_LOGGING = 'ndb_table_no_logging';
    public const NDB_TABLE_TEMPORARY = 'ndb_table_temporary';
    public const NDB_USE_COPYING_ALTER_TABLE = 'ndb_use_copying_alter_table';
    public const NDB_USE_EXACT_COUNT = 'ndb_use_exact_count';
    public const NDB_USE_TRANSACTIONS = 'ndb_use_transactions';
    public const NDB_VERSION = 'ndb_version';
    public const NDB_VERSION_STRING = 'ndb_version_string';
    public const NDB_WAIT_CONNECTED = 'ndb_wait_connected';
    public const NDB_WAIT_SETUP = 'ndb_wait_setup';

    public const NDBINFO_DATABASE = 'ndbinfo_database';
    public const NDBINFO_MAX_BYTES = 'ndbinfo_max_bytes';
    public const NDBINFO_MAX_ROWS = 'ndbinfo_max_rows';
    public const NDBINFO_OFFLINE = 'ndbinfo_offline';
    public const NDBINFO_SHOW_HIDDEN = 'ndbinfo_show_hidden';
    public const NDBINFO_TABLE_PREFIX = 'ndbinfo_table_prefix';
    public const NDBINFO_VERSION = 'ndbinfo_version';

    public const NET_BUFFER_LENGTH = 'net_buffer_length';
    public const NET_READ_TIMEOUT = 'net_read_timeout';
    public const NET_RETRY_COUNT = 'net_retry_count';
    public const NET_WRITE_TIMEOUT = 'net_write_timeout';

    public const NEW = 'new';
    public const NGRAM_TOKEN_SIZE = 'ngram_token_size';

    public const NULL_AUDIT_ABORT_MESSAGE = 'null_audit_abort_message';
    public const NULL_AUDIT_ABORT_VALUE = 'null_audit_abort_value';
    public const NULL_AUDIT_EVENT_ORDER_CHECK = 'null_audit_event_order_check';
    public const NULL_AUDIT_EVENT_ORDER_CHECK_CONSUME_IGNORE_COUNT = 'null_audit_event_order_check_consume_ignore_count';
    public const NULL_AUDIT_EVENT_ORDER_CHECK_EXACT = 'null_audit_event_order_check_exact';
    public const NULL_AUDIT_EVENT_ORDER_STARTED = 'null_audit_event_order_started';
    public const NULL_AUDIT_EVENT_RECORD = 'null_audit_event_record';
    public const NULL_AUDIT_EVENT_RECORD_DEF = 'null_audit_event_record_def';

    public const OFFLINE_MODE = 'offline_mode';
    public const OLD = 'old';
    public const OLD_ALTER_TABLE = 'old_alter_table';
    public const OLD_PASSWORDS = 'old_passwords'; // removed in 8.0
    public const OPEN_FILES_LIMIT = 'open_files_limit';

    public const OPTIMIZER_MAX_SUBGRAPH_PAIRS = 'optimizer_max_subgraph_pairs';
    public const OPTIMIZER_PRUNE_LEVEL = 'optimizer_prune_level';
    public const OPTIMIZER_SEARCH_DEPTH = 'optimizer_search_depth';
    public const OPTIMIZER_SWITCH = 'optimizer_switch'; // new in 5.7Mes
    public const OPTIMIZER_TRACE = 'optimizer_trace';
    public const OPTIMIZER_TRACE_FEATURES = 'optimizer_trace_features';
    public const OPTIMIZER_TRACE_LIMIT = 'optimizer_trace_limit';
    public const OPTIMIZER_TRACE_MAX_MEM_SIZE = 'optimizer_trace_max_mem_size';
    public const OPTIMIZER_TRACE_OFFSET = 'optimizer_trace_offset';
    public const OPTIMIZER_USE_MRR = 'optimizer_use_mrr';

    public const ORIGINAL_COMMIT_TIMESTAMP = 'original_commit_timestamp';
    public const ORIGINAL_SERVER_VERSION = 'original_server_version';
    public const PARTIAL_REVOKES = 'partial_revokes';
    public const PARSER_MAX_MEM_SIZE = 'parser_max_mem_size';
    public const PASSWORD_HISTORY = 'password_history';
    public const PASSWORD_REUSE_INTERVAL = 'password_reuse_interval';
    public const PASSWORD_REQUIRE_CURRENT = 'password_require_current';

    public const PERFORMANCE_SCHEMA = 'performance_schema';
    public const PERFORMANCE_SCHEMA_ACCOUNTS_SIZE = 'performance_schema_accounts_size';
    public const PERFORMANCE_SCHEMA_DIGESTS_SIZE = 'performance_schema_digests_size';
    public const PERFORMANCE_SCHEMA_ERROR_SIZE = 'performance_schema_error_size';
    public const PERFORMANCE_SCHEMA_EVENTS_STAGES_HISTORY_LONG_SIZE = 'performance_schema_events_stages_history_long_size';
    public const PERFORMANCE_SCHEMA_EVENTS_STAGES_HISTORY_SIZE = 'performance_schema_events_stages_history_size';
    public const PERFORMANCE_SCHEMA_EVENTS_STATEMENTS_HISTORY_LONG_SIZE = 'performance_schema_events_statements_history_long_size';
    public const PERFORMANCE_SCHEMA_EVENTS_STATEMENTS_HISTORY_SIZE = 'performance_schema_events_statements_history_size';
    public const PERFORMANCE_SCHEMA_EVENTS_TRANSACTIONS_HISTORY_LONG_SIZE = 'performance_schema_events_transactions_history_long_size';
    public const PERFORMANCE_SCHEMA_EVENTS_TRANSACTIONS_HISTORY_SIZE = 'performance_schema_events_transactions_history_size';
    public const PERFORMANCE_SCHEMA_EVENTS_WAITS_HISTORY_LONG_SIZE = 'performance_schema_events_waits_history_long_size';
    public const PERFORMANCE_SCHEMA_EVENTS_WAITS_HISTORY_SIZE = 'performance_schema_events_waits_history_size';
    public const PERFORMANCE_SCHEMA_HOSTS_SIZE = 'performance_schema_hosts_size';
    public const PERFORMANCE_SCHEMA_MAX_COND_CLASSES = 'performance_schema_max_cond_classes';
    public const PERFORMANCE_SCHEMA_MAX_COND_INSTANCES = 'performance_schema_max_cond_instances';
    public const PERFORMANCE_SCHEMA_MAX_DIGEST_LENGTH = 'performance_schema_max_digest_length';
    public const PERFORMANCE_SCHEMA_MAX_DIGEST_SAMPLE_AGE = 'performance_schema_max_digest_sample_age';
    public const PERFORMANCE_SCHEMA_MAX_FILE_CLASSES = 'performance_schema_max_file_classes';
    public const PERFORMANCE_SCHEMA_MAX_FILE_HANDLES = 'performance_schema_max_file_handles';
    public const PERFORMANCE_SCHEMA_MAX_FILE_INSTANCES = 'performance_schema_max_file_instances';
    public const PERFORMANCE_SCHEMA_MAX_INDEX_STAT = 'performance_schema_max_index_stat';
    public const PERFORMANCE_SCHEMA_MAX_MEMORY_CLASSES = 'performance_schema_max_memory_classes';
    public const PERFORMANCE_SCHEMA_MAX_METADATA_LOCKS = 'performance_schema_max_metadata_locks';
    public const PERFORMANCE_SCHEMA_MAX_MUTEX_CLASSES = 'performance_schema_max_mutex_classes';
    public const PERFORMANCE_SCHEMA_MAX_MUTEX_INSTANCES = 'performance_schema_max_mutex_instances';
    public const PERFORMANCE_SCHEMA_MAX_PREPARED_STATEMENTS_INSTANCES = 'performance_schema_max_prepared_statements_instances';
    public const PERFORMANCE_SCHEMA_MAX_PROGRAM_INSTANCES = 'performance_schema_max_program_instances';
    public const PERFORMANCE_SCHEMA_MAX_RWLOCK_CLASSES = 'performance_schema_max_rwlock_classes';
    public const PERFORMANCE_SCHEMA_MAX_RWLOCK_INSTANCES = 'performance_schema_max_rwlock_instances';
    public const PERFORMANCE_SCHEMA_MAX_SOCKET_CLASSES = 'performance_schema_max_socket_classes';
    public const PERFORMANCE_SCHEMA_MAX_SOCKET_INSTANCES = 'performance_schema_max_socket_instances';
    public const PERFORMANCE_SCHEMA_MAX_SQL_TEXT_LENGTH = 'performance_schema_max_sql_text_length';
    public const PERFORMANCE_SCHEMA_MAX_STAGE_CLASSES = 'performance_schema_max_stage_classes';
    public const PERFORMANCE_SCHEMA_MAX_STATEMENT_CLASSES = 'performance_schema_max_statement_classes';
    public const PERFORMANCE_SCHEMA_MAX_STATEMENT_STACK = 'performance_schema_max_statement_stack';
    public const PERFORMANCE_SCHEMA_MAX_TABLE_HANDLES = 'performance_schema_max_table_handles';
    public const PERFORMANCE_SCHEMA_MAX_TABLE_INSTANCES = 'performance_schema_max_table_instances';
    public const PERFORMANCE_SCHEMA_MAX_TABLE_LOCK_STAT = 'performance_schema_max_table_lock_stat';
    public const PERFORMANCE_SCHEMA_MAX_THREAD_CLASSES = 'performance_schema_max_thread_classes';
    public const PERFORMANCE_SCHEMA_MAX_THREAD_INSTANCES = 'performance_schema_max_thread_instances';
    public const PERFORMANCE_SCHEMA_SHOW_PROCESSLIST = 'performance_schema_show_processlist';
    public const PERFORMANCE_SCHEMA_SESSION_CONNECT_ATTRS_SIZE = 'performance_schema_session_connect_attrs_size';
    public const PERFORMANCE_SCHEMA_SETUP_ACTORS_SIZE = 'performance_schema_setup_actors_size';
    public const PERFORMANCE_SCHEMA_SETUP_OBJECTS_SIZE = 'performance_schema_setup_objects_size';
    public const PERFORMANCE_SCHEMA_USERS_SIZE = 'performance_schema_users_size';

    public const PERSISTED_GLOBALS_LOAD = 'persisted_globals_load';
    public const PERSIST_ONLY_ADMIN_X509_SUBJECT = 'persist_only_admin_x509_subject';
    public const PERSIST_SENSITIVE_VARIABLES_IN_PLAINTEXT = 'persist_sensitive_variables_in_plaintext';
    public const PID_FILE = 'pid_file';
    public const PLUGIN_DIR = 'plugin_dir';
    public const PORT = 'port';
    public const PRELOAD_BUFFER_SIZE = 'preload_buffer_size';
    public const PRINT_IDENTIFIED_WITH_AS_HEX = 'print_identified_with_as_hex';

    public const PROFILING = 'profiling';
    public const PROFILING_HISTORY_SIZE = 'profiling_history_size';

    public const PROTOCOL_COMPRESSION_ALGORITHMS = 'protocol_compression_algorithms';
    public const PROTOCOL_VERSION = 'protocol_version';

    public const PROXY_USER = 'proxy_user';

    public const PSEUDO_REPLICA_MODE = 'pseudo_replica_mode';
    public const PSEUDO_SLAVE_MODE = 'pseudo_slave_mode';
    public const PSEUDO_THREAD_ID = 'pseudo_thread_id';

    public const QUERY_ALLOC_BLOCK_SIZE = 'query_alloc_block_size';
    public const QUERY_PREALLOC_SIZE = 'query_prealloc_size';

    public const QUERY_CACHE_LIMIT = 'query_cache_limit'; // removed in 8.0
    public const QUERY_CACHE_MIN_RES_UNIT = 'query_cache_min_res_unit'; // removed in 8.0
    public const QUERY_CACHE_SIZE = 'query_cache_size'; // removed in 8.0
    public const QUERY_CACHE_TYPE = 'query_cache_type'; // removed in 8.0
    public const QUERY_CACHE_WLOCK_INVALIDATE = 'query_cache_wlock_invalidate'; // removed in 8.0

    public const RAND_SEED1 = 'rand_seed1';
    public const RAND_SEED2 = 'rand_seed2';

    public const RANGE_ALLOC_BLOCK_SIZE = 'range_alloc_block_size';
    public const RANGE_OPTIMIZER_MAX_MEM_SIZE = 'range_optimizer_max_mem_size';

    public const RBR_EXEC_MODE = 'rbr_exec_mode';

    public const READ_BUFFER_SIZE = 'read_buffer_size';
    public const READ_ONLY = 'read_only';
    public const READ_RND_BUFFER_SIZE = 'read_rnd_buffer_size';

    public const REGEXP_STACK_LIMIT = 'regexp_stack_limit';
    public const REGEXP_TIME_LIMIT = 'regexp_time_limit';

    public const RELAY_LOG = 'relay_log';
    public const RELAY_LOG_BASENAME = 'relay_log_basename';
    public const RELAY_LOG_INDEX = 'relay_log_index';
    public const RELAY_LOG_INFO_FILE = 'relay_log_info_file';
    public const RELAY_LOG_INFO_REPOSITORY = 'relay_log_info_repository';
    public const RELAY_LOG_PURGE = 'relay_log_purge';
    public const RELAY_LOG_RECOVERY = 'relay_log_recovery';
    public const RELAY_LOG_SPACE_LIMIT = 'relay_log_space_limit';

    public const REPLICA_ALLOW_BATCHING = 'replica_allow_batching';
    public const REPLICA_CHECKPOINT_GROUP = 'replica_checkpoint_group';
    public const REPLICA_CHECKPOINT_PERIOD = 'replica_checkpoint_period';
    public const REPLICA_COMPRESSED_PROTOCOL = 'replica_compressed_protocol';
    public const REPLICA_EXEC_MODE = 'replica_exec_mode';
    public const REPLICA_LOAD_TMPDIR = 'replica_load_tmpdir';
    public const REPLICA_MAX_ALLOWED_PACKET = 'replica_max_allowed_packet';
    public const REPLICA_NET_TIMEOUT = 'replica_net_timeout';
    public const REPLICA_PARALLEL_TYPE = 'replica_parallel_type';
    public const REPLICA_PARALLEL_WORKERS = 'replica_parallel_workers';
    public const REPLICA_PENDING_JOBS_SIZE_MAX = 'replica_pending_jobs_size_max';
    public const REPLICA_PRESERVE_COMMIT_ORDER = 'replica_preserve_commit_order';
    public const REPLICA_SKIP_ERRORS = 'replica_skip_errors';
    public const REPLICA_SQL_VERIFY_CHECKSUM = 'replica_sql_verify_checksum';
    public const REPLICA_TRANSACTION_RETRIES = 'replica_transaction_retries';
    public const REPLICA_TYPE_CONVERSIONS = 'replica_type_conversions';

    public const REPLICATION_SENDER_OBSERVE_COMMIT_ONLY = 'replication_sender_observe_commit_only';
    public const REPLICATION_OPTIMIZE_FOR_STATIC_PLUGIN_CONFIG = 'replication_optimize_for_static_plugin_config';

    public const REPORT_HOST = 'report_host';
    public const REPORT_PASSWORD = 'report_password';
    public const REPORT_PORT = 'report_port';
    public const REPORT_USER = 'report_user';

    public const REQUIRE_ROW_FORMAT = 'require_row_format';
    public const REQUIRE_SECURE_TRANSPORT = 'require_secure_transport';

    public const RESULTSET_METADATA = 'resultset_metadata';

    public const REWRITER_ENABLED = 'rewriter_enabled';
    public const REWRITER_ENABLED_FOR_THREADS_WITHOUT_PRIVILEGE_CHECKS = 'rewriter_enabled_for_threads_without_privilege_checks';
    public const REWRITER_VERBOSE = 'rewriter_verbose';

    public const RPL_READ_SIZE = 'rpl_read_size';
    public const RPL_SEMI_SYNC_MASTER_ENABLED = 'rpl_semi_sync_master_enabled';
    public const RPL_SEMI_SYNC_MASTER_TIMEOUT = 'rpl_semi_sync_master_timeout';
    public const RPL_SEMI_SYNC_MASTER_TRACE_LEVEL = 'rpl_semi_sync_master_trace_level';
    public const RPL_SEMI_SYNC_MASTER_WAIT_FOR_SLAVE_COUNT = 'rpl_semi_sync_master_wait_for_slave_count';
    public const RPL_SEMI_SYNC_MASTER_WAIT_NO_SLAVE = 'rpl_semi_sync_master_wait_no_slave';
    public const RPL_SEMI_SYNC_MASTER_WAIT_POINT = 'rpl_semi_sync_master_wait_point';
    public const RPL_SEMI_SYNC_REPLICA_ENABLED = 'rpl_semi_sync_replica_enabled';
    public const RPL_SEMI_SYNC_REPLICA_TRACE_LEVEL = 'rpl_semi_sync_replica_trace_level';
    public const RPL_SEMI_SYNC_SLAVE_ENABLED = 'rpl_semi_sync_slave_enabled';
    public const RPL_SEMI_SYNC_SLAVE_TRACE_LEVEL = 'rpl_semi_sync_slave_trace_level';
    public const RPL_SEMI_SYNC_SOURCE_ENABLED = 'rpl_semi_sync_source_enabled';
    public const RPL_SEMI_SYNC_SOURCE_TIMEOUT = 'rpl_semi_sync_source_timeout';
    public const RPL_SEMI_SYNC_SOURCE_TRACE_LEVEL = 'rpl_semi_sync_source_trace_level';
    public const RPL_SEMI_SYNC_SOURCE_WAIT_FOR_REPLICA_COUNT = 'rpl_semi_sync_source_wait_for_replica_count';
    public const RPL_SEMI_SYNC_SOURCE_WAIT_NO_REPLICA = 'rpl_semi_sync_source_wait_no_replica';
    public const RPL_SEMI_SYNC_SOURCE_WAIT_POINT = 'rpl_semi_sync_source_wait_point';
    public const RPL_STOP_REPLICA_TIMEOUT = 'rpl_stop_replica_timeout';
    public const RPL_STOP_SLAVE_TIMEOUT = 'rpl_stop_slave_timeout';

    public const SCHEMA_DEFINITION_CACHE = 'schema_definition_cache';
    public const SECONDARY_ENGINE_COST_THRESHOLD = 'secondary_engine_cost_threshold';

    public const SECURE_AUTH = 'secure_auth';
    public const SECURE_FILE_PRIV = 'secure_file_priv';

    public const SELECT_INTO_BUFFER_SIZE = 'select_into_buffer_size';
    public const SELECT_INTO_DISK_SYNC = 'select_into_disk_sync';
    public const SELECT_INTO_DISK_SYNC_DELAY = 'select_into_disk_sync_delay';

    public const SERVER_ID = 'server_id';
    public const SERVER_ID_BITS = 'server_id_bits';
    public const SERVER_UUID = 'server_uuid';

    public const SESSION_TRACK_GTIDS = 'session_track_gtids';
    public const SESSION_TRACK_SCHEMA = 'session_track_schema';
    public const SESSION_TRACK_STATE_CHANGE = 'session_track_state_change';
    public const SESSION_TRACK_SYSTEM_VARIABLES = 'session_track_system_variables';
    public const SESSION_TRACK_TRANSACTION_INFO = 'session_track_transaction_info';

    public const SHA256_PASSWORD_AUTO_GENERATE_RSA_KEYS = 'sha256_password_auto_generate_rsa_keys';
    public const SHA256_PASSWORD_PRIVATE_KEY_PATH = 'sha256_password_private_key_path';
    public const SHA256_PASSWORD_PROXY_USERS = 'sha256_password_proxy_users';
    public const SHA256_PASSWORD_PUBLIC_KEY_PATH = 'sha256_password_public_key_path';

    public const SHARED_MEMORY = 'shared_memory';
    public const SHARED_MEMORY_BASE_NAME = 'shared_memory_base_name';

    public const SHOW_COMPATIBILITY_56 = 'show_compatibility_56'; // removed in 8.0
    public const SHOW_CREATE_TABLE_SKIP_SECONDARY_ENGINE = 'show_create_table_skip_secondary_engine';
    public const SHOW_CREATE_TABLE_VERBOSITY = 'show_create_table_verbosity';
    public const SHOW_GIPK_IN_CREATE_TABLE_AND_INFORMATION_SCHEMA = 'show_gipk_in_create_table_and_information_schema';
    public const SHOW_OLD_TEMPORALS = 'show_old_temporals';

    public const SKIP_EXTERNAL_LOCKING = 'skip_external_locking';
    public const SKIP_NAME_RESOLVE = 'skip_name_resolve';
    public const SKIP_NETWORKING = 'skip_networking';
    public const SKIP_REPLICA_START = 'skip_replica_start';
    public const SKIP_SHOW_DATABASE = 'skip_show_database';
    public const SKIP_SLAVE_START = 'skip_slave_start';

    public const SLAVE_ALLOW_BATCHING = 'slave_allow_batching';
    public const SLAVE_CHECKPOINT_GROUP = 'slave_checkpoint_group';
    public const SLAVE_CHECKPOINT_PERIOD = 'slave_checkpoint_period';
    public const SLAVE_COMPRESSED_PROTOCOL = 'slave_compressed_protocol';
    public const SLAVE_EXEC_MODE = 'slave_exec_mode';
    public const SLAVE_LOAD_TMPDIR = 'slave_load_tmpdir';
    public const SLAVE_MAX_ALLOWED_PACKET = 'slave_max_allowed_packet';
    public const SLAVE_NET_TIMEOUT = 'slave_net_timeout';
    public const SLAVE_PARALLEL_TYPE = 'slave_parallel_type';
    public const SLAVE_PARALLEL_WORKERS = 'slave_parallel_workers';
    public const SLAVE_PENDING_JOBS_SIZE_MAX = 'slave_pending_jobs_size_max';
    public const SLAVE_PRESERVE_COMMIT_ORDER = 'slave_preserve_commit_order';
    public const SLAVE_ROWS_SEARCH_ALGORITHMS = 'slave_rows_search_algorithms';
    public const SLAVE_SKIP_ERRORS = 'slave_skip_errors';
    public const SLAVE_SQL_VERIFY_CHECKSUM = 'slave_sql_verify_checksum';
    public const SLAVE_TRANSACTION_RETRIES = 'slave_transaction_retries';
    public const SLAVE_TYPE_CONVERSIONS = 'slave_type_conversions';

    public const SLOW_LAUNCH_TIME = 'slow_launch_time';
    public const SLOW_QUERY_LOG = 'slow_query_log';
    public const SLOW_QUERY_LOG_FILE = 'slow_query_log_file';
    public const SOCKET = 'socket';
    public const SORT_BUFFER_SIZE = 'sort_buffer_size';
    public const SOURCE_VERIFY_CHECKSUM = 'source_verify_checksum';

    public const SQL_AUTO_IS_NULL = 'sql_auto_is_null';
    public const SQL_BIG_SELECTS = 'sql_big_selects';
    public const SQL_BUFFER_RESULT = 'sql_buffer_result';
    public const SQL_GENERATE_INVISIBLE_PRIMARY_KEY = 'sql_generate_invisible_primary_key';
    public const SQL_LOG_BIN = 'sql_log_bin';
    public const SQL_LOG_OFF = 'sql_log_off';
    public const SQL_MODE = 'sql_mode';
    public const SQL_NOTES = 'sql_notes';
    public const SQL_QUOTE_SHOW_CREATE = 'sql_quote_show_create';
    public const SQL_REPLICA_SKIP_COUNTER = 'sql_replica_skip_counter';
    public const SQL_REQUIRE_PRIMARY_KEY = 'sql_require_primary_key';
    public const SQL_SAFE_UPDATES = 'sql_safe_updates';
    public const SQL_SELECT_LIMIT = 'sql_select_limit';
    public const SQL_SLAVE_SKIP_COUNTER = 'sql_slave_skip_counter';
    public const SQL_WARNINGS = 'sql_warnings';

    public const SSL_CA = 'ssl_ca';
    public const SSL_CAPATH = 'ssl_capath';
    public const SSL_CERT = 'ssl_cert';
    public const SSL_CIPHER = 'ssl_cipher';
    public const SSL_CRL = 'ssl_crl';
    public const SSL_CRLPATH = 'ssl_crlpath';
    public const SSL_FIPS_MODE = 'ssl_fips_mode';
    public const SSL_KEY = 'ssl_key';
    public const SSL_SESSION_CACHE_MODE = 'ssl_session_cache_mode';
    public const SSL_SESSION_CACHE_TIMEOUT = 'ssl_session_cache_timeout';

    public const STORED_PROGRAM_CACHE = 'stored_program_cache';
    public const STORED_PROGRAM_DEFINITION_CACHE = 'stored_program_definition_cache';
    public const SUPER_READ_ONLY = 'super_read_only';

    public const SYNC_BINLOG = 'sync_binlog';
    public const SYNC_FRM = 'sync_frm'; // removed in 8.0
    public const SYNC_MASTER_INFO = 'sync_master_info';
    public const SYNC_RELAY_LOG = 'sync_relay_log';
    public const SYNC_RELAY_LOG_INFO = 'sync_relay_log_info'; // deprecated since 8.0.34
    public const SYNC_SOURCE_INFO = 'sync_source_info';

    public const SYSTEM_TIME_ZONE = 'system_time_zone';

    public const TABLE_DEFINITION_CACHE = 'table_definition_cache';
    public const TABLE_ENCRYPTION_PRIVILEGE_CHECK = 'table_encryption_privilege_check';
    public const TABLE_OPEN_CACHE = 'table_open_cache';
    public const TABLE_OPEN_CACHE_INSTANCES = 'table_open_cache_instances';
    public const TABLESPACE_DEFINITION_CACHE = 'tablespace_definition_cache';

    public const TEMPTABLE_MAX_MMAP = 'temptable_max_mmap';
    public const TEMPTABLE_MAX_RAM = 'temptable_max_ram';
    public const TEMPTABLE_USE_MMAP = 'temptable_use_mmap';

    public const TERMINOLOGY_USE_PREVIOUS = 'terminology_use_previous';

    public const THREAD_CACHE_SIZE = 'thread_cache_size';
    public const THREAD_HANDLING = 'thread_handling';
    public const THREAD_POOL_ALGORITHM = 'thread_pool_algorithm';
    public const THREAD_POOL_HIGH_PRIORITY_CONNECTION = 'thread_pool_high_priority_connection';
    public const THREAD_POOL_MAX_ACTIVE_QUERY_THREADS = 'thread_pool_max_active_query_threads';
    public const THREAD_POOL_MAX_UNUSED_THREADS = 'thread_pool_max_unused_threads';
    public const THREAD_POOL_PRIO_KICKUP_TIMER = 'thread_pool_prio_kickup_timer';
    public const THREAD_POOL_SIZE = 'thread_pool_size';
    public const THREAD_POOL_STALL_LIMIT = 'thread_pool_stall_limit';
    public const THREAD_STACK = 'thread_stack';

    public const TIME_FORMAT = 'time_format'; // removed in 8.0
    public const TIME_ZONE = 'time_zone';
    public const TIMESTAMP = 'timestamp';

    public const TLS_CIPHERSUITES = 'tls_ciphersuites';
    public const TLS_VERSION = 'tls_version';

    public const TMP_TABLE_SIZE = 'tmp_table_size';
    public const TMPDIR = 'tmpdir';

    public const TRANSACTION_ALLOC_BLOCK_SIZE = 'transaction_alloc_block_size';
    public const TRANSACTION_ALLOW_BATCHING = 'transaction_allow_batching';
    public const TRANSACTION_ISOLATION = 'transaction_isolation';
    public const TRANSACTION_PREALLOC_SIZE = 'transaction_prealloc_size';
    public const TRANSACTION_READ_ONLY = 'transaction_read_only';
    public const TRANSACTION_WRITE_SET_EXTRACTION = 'transaction_write_set_extraction';

    public const TX_ISOLATION = 'tx_isolation';
    public const TX_READ_ONLY = 'tx_read_only';

    public const UNIQUE_CHECKS = 'unique_checks';
    public const UPDATABLE_VIEWS_WITH_LIMIT = 'updatable_views_with_limit';
    public const USE_SECONDARY_ENGINE = 'use_secondary_engine';

    public const VALIDATE_PASSWORD_POLICY = 'validate_password_policy';
    public const VALIDATE_PASSWORD_LENGTH = 'validate_password_length';
    public const VALIDATE_PASSWORD_MIXED_CASE_COUNT = 'validate_password_mixed_case_count';
    public const VALIDATE_PASSWORD_NUMBER_COUNT = 'validate_password_number_count';
    public const VALIDATE_PASSWORD_SPECIAL_CHAR_COUNT = 'validate_password_special_char_count';
    public const VALIDATE_PASSWORD_DICTIONARY_FILE = 'validate_password_dictionary_file';
    public const VALIDATE_PASSWORD_CHECK_USER_NAME = 'validate_password_check_user_name';

    public const VERSION = 'version';
    public const VERSION_COMMENT = 'version_comment';
    public const VERSION_COMPILE_MACHINE = 'version_compile_machine';
    public const VERSION_COMPILE_OS = 'version_compile_os';
    public const VERSION_COMPILE_ZLIB = 'version_compile_zlib';

    public const VERSION_TOKENS_SESSION = 'version_tokens_session';
    public const VERSION_TOKENS_SESSION_NUMBER = 'version_tokens_session_number';

    public const WAIT_TIMEOUT = 'wait_timeout';
    public const WARNING_COUNT = 'warning_count';
    public const WINDOWING_USE_HIGH_PRECISION = 'windowing_use_high_precision';
    public const XA_DETACH_ON_PREPARE = 'xa_detach_on_prepare';

    // components
    public const DRAGNET__LOG_ERROR_FILTER_RULES = 'dragnet.log_error_filter_rules';
    public const SYSEVENTLOG__TAG = 'syseventlog.tag';
    public const SYSEVENTLOG__FACILITY = 'syseventlog.facility';
    public const SYSEVENTLOG__INCLUDE_PID = 'syseventlog.include_pid';
    public const VALIDATE_PASSWORD__CHANGED_CHARACTERS_PERCENTAGE = 'validate_password.changed_characters_percentage';
    public const VALIDATE_PASSWORD__CHECK_USER_NAME = 'validate_password.check_user_name';
    public const VALIDATE_PASSWORD__DICTIONARY_FILE = 'validate_password.dictionary_file';
    public const VALIDATE_PASSWORD__LENGTH = 'validate_password.length';
    public const VALIDATE_PASSWORD__MIXED_CASE_COUNT = 'validate_password.mixed_case_count';
    public const VALIDATE_PASSWORD__NUMBER_COUNT = 'validate_password.number_count';
    public const VALIDATE_PASSWORD__POLICY = 'validate_password.policy';
    public const VALIDATE_PASSWORD__SPECIAL_CHAR_COUNT = 'validate_password.special_char_count';

    // WTF?
    public const KEYCACHE__KEY_BUFFER_SIZE = 'keycache.key_buffer_size';
    public const KEYCACHE1__KEY_BUFFER_SIZE = 'keycache1.key_buffer_size';
    public const KEYCACHE1__KEY_CACHE_BLOCK_SIZE = 'keycache1.key_cache_block_size';
    public const KEYCACHE2__KEY_BUFFER_SIZE = 'keycache2.key_buffer_size';
    public const KEYCACHE2__KEY_CACHE_BLOCK_SIZE = 'keycache2.key_cache_block_size';
    public const KEYCACHE3__KEY_BUFFER_SIZE = 'keycache3.key_buffer_size';
    public const SMALL_KEY__BUFFER_SIZE = 'small.key_buffer_size';
    public const MEDIUM__KEY_BUFFER_SIZE = 'medium.key_buffer_size';
    public const DEFAULT__KEY_BUFFER_SIZE = 'default.key_buffer_size';
    public const KEY_CACHE_NONE__KEY_CACHE_BLOCK_SIZE = 'key_cache_none.key_cache_block_size';
    public const NEW_CACHE__KEY_BUFFER_SIZE = 'new_cache.key_buffer_size';
    public const SECOND_CACHE__KEY_BUFFER_SIZE = 'second_cache.key_buffer_size';

    /** @var array<string, array{0:string|null, 1:bool, 2:string, 3:scalar|null, 4?:int, 5?:int|float|list<int|string>, 6?:int|float, 7?:int}> */
    public static array $properties = [
        // https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html
        // scope: variable is restricted to SESSION or GLOBAL scope (GLOBAL includes PERSIST and PERSIST_ONLY); null = exists in both scopes
        // dynamic: variable can be set in runtime (PERSIST_ONLY can be set anyway, unless NO_PERSIST flag is set)
        // type: variable type
        // flags: see VariableFlags::class
        // default: default value when not changed in config or in runtime
        // values: list of allowed values for ENUM or SET
        // min, max: minimal and maximal values
        // increment: value must be divisible by this number
        //                                                 0:scope, 1:dynamic, 2:type, 3:default, 4:flags, [5:values] | [5:min, 6:max, [7:increment]]
        self::ACTIVATE_ALL_ROLES_ON_LOGIN               => [S::GLOBAL,  true,  T::BOOL,     false],
        self::ADMIN_ADDRESS                             => [S::GLOBAL,  false, T::CHAR,     null],
        self::ADMIN_PORT                                => [S::GLOBAL,  false, T::UNSIGNED, 33062,      F::NONE, 1, 65535],
        self::ADMIN_SSL_CA                              => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE], // file
        self::ADMIN_SSL_CAPATH                          => [S::GLOBAL,  true,  T::CHAR,     null], // dir
        self::ADMIN_SSL_CERT                            => [S::GLOBAL,  true,  T::CHAR,     null], // file
        self::ADMIN_SSL_CIPHER                          => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE],
        self::ADMIN_SSL_CRL                             => [S::GLOBAL,  true,  T::CHAR,     null], // file
        self::ADMIN_SSL_CRLPATH                         => [S::GLOBAL,  true,  T::CHAR,     null], // dir
        self::ADMIN_SSL_KEY                             => [S::GLOBAL,  true,  T::CHAR,     null], // file
        self::ADMIN_TLS_CIPHERSUITES                    => [S::GLOBAL,  true,  T::CHAR,     null],
        self::ADMIN_TLS_VERSION                         => [S::GLOBAL,  true,  T::SET,      'TLSV1.2,TLSV1.3', F::NONE, ['TLSV1.2', 'TLSV1.3']], // < 8.0.28 "TLSv1,TLSv1.1,TLSv1.2,TLSv1.3"
        self::AUTHENTICATION_POLICY                     => [S::GLOBAL,  true,  T::CHAR,     '*,,'],
        self::AUTHENTICATION_WINDOWS_LOG_LEVEL          => [S::GLOBAL,  false, T::UNSIGNED, 2,          F::NONE, 0, 2],
        self::AUTHENTICATION_WINDOWS_USE_PRINCIPAL_NAME => [S::GLOBAL,  false, T::BOOL,     true],
        self::AUTOCOMMIT                                => [null,       true,  T::BOOL,     true],
        self::AUTOMATIC_SP_PRIVILEGES                   => [S::GLOBAL,  true,  T::BOOL,     true],
        self::AUTO_GENERATE_CERTS                       => [S::GLOBAL,  false, T::BOOL,     true],
        self::AVOID_TEMPORAL_UPGRADE                    => [S::GLOBAL,  true,  T::BOOL,     false],
        self::BACK_LOG                                  => [S::GLOBAL,  false, T::UNSIGNED, -1,         F::NONE, 1, 65535], // default -1 = auto
        self::BASEDIR                                   => [S::GLOBAL,  false, T::CHAR,     '/var/lib/mysql', F::NO_PERSIST], // dir
        self::BIG_TABLES                                => [null,       true,  T::BOOL,     false,      F::SET_VAR],
        self::BIND_ADDRESS                              => [S::GLOBAL,  false, T::CHAR,     '*',        F::NO_PERSIST],
        self::BLOCK_ENCRYPTION_MODE                     => [null,       true,  T::ENUM,     'AES-128-ECB', F::NONE, ['AES-128-ECB', 'AES-192-ECB', 'AES-256-ECB', 'AES-128-CBC', 'AES-192-CBC', 'AES-256-CBC']],
        self::BUILD_ID                                  => [S::GLOBAL,  false, T::CHAR,     ''], // sha1 hash
        self::BULK_INSERT_BUFFER_SIZE                   => [null,       true,  T::UNSIGNED, 8388608,    F::SET_VAR, 0, MAX],
        self::CACHING_SHA2_PASSWORD_DIGEST_ROUNDS       => [S::GLOBAL,  false, T::UNSIGNED, 5000,       F::NONE, 5000, 4095000],
        self::CACHING_SHA2_PASSWORD_AUTO_GENERATE_RSA_KEYS
                                                        => [S::GLOBAL,  false, T::BOOL,     true],
        self::CACHING_SHA2_PASSWORD_PRIVATE_KEY_PATH    => [S::GLOBAL,  false, T::CHAR,     'private_key.pem'], // file
        self::CACHING_SHA2_PASSWORD_PUBLIC_KEY_PATH     => [S::GLOBAL,  false, T::CHAR,     'public_key.pem'], // file
        self::CHARACTER_SET_CLIENT                      => [null,       true,  Charset::class, 'utf8mb4'],
        self::CHARACTER_SET_CONNECTION                  => [null,       true,  Charset::class, 'utf8mb4'],
        self::CHARACTER_SET_DATABASE                    => [null,       true,  Charset::class, 'utf8mb4'],
        self::CHARACTER_SET_FILESYSTEM                  => [null,       true,  Charset::class, 'binary'],
        self::CHARACTER_SET_RESULTS                     => [null,       true,  Charset::class, 'utf8mb4', F::NULLABLE],
        self::CHARACTER_SET_SERVER                      => [null,       true,  Charset::class, 'utf8mb4'],
        self::CHARACTER_SET_SYSTEM                      => [S::GLOBAL,  false, Charset::class, 'utf8mb3', F::NO_PERSIST],
        self::CHARACTER_SETS_DIR                        => [S::GLOBAL,  false, T::CHAR,     '',           F::NO_PERSIST], // dir
        self::CHECK_PROXY_USERS                         => [S::GLOBAL,  true,  T::BOOL,     false],
        self::COLLATION_CONNECTION                      => [null,       true,  Collation::class, 'utf8mb4_0900_ai_ci'],
        self::COLLATION_DATABASE                        => [null,       true,  Collation::class, 'utf8mb4_0900_ai_ci'],
        self::COLLATION_SERVER                          => [null,       true,  Collation::class, 'utf8mb4_0900_ai_ci'],
        self::COMPLETION_TYPE                           => [null,       true,  T::ENUM,     'NO_CHAIN', F::NONE, ['NO_CHAIN', 'CHAIN', 'RELEASE']],
        self::CONCURRENT_INSERT                         => [S::GLOBAL,  true,  T::ENUM,     'AUTO',     F::NONE, ['NEVER', 'AUTO', 'ALWAYS']],
        self::CONNECT_TIMEOUT                           => [S::GLOBAL,  true,  T::UNSIGNED, 10,         F::CLAMP, 2, Seconds::COMMON_YEAR],
        self::CONNECTION_MEMORY_CHUNK_SIZE              => [null,       true,  T::UNSIGNED, 8192,       F::CLAMP, 1, 536870912],
        self::CONNECTION_MEMORY_LIMIT                   => [null,       true,  T::UNSIGNED, MAX,        F::CLAMP, 2097152, MAX],
        self::CORE_FILE                                 => [S::GLOBAL,  false, T::BOOL,     false,      F::NO_PERSIST],
        self::CREATE_ADMIN_LISTENER_THREAD              => [S::GLOBAL,  false, T::BOOL,     false],
        self::CTE_MAX_RECURSION_DEPTH                   => [null,       true,  T::UNSIGNED, 1000,       F::SET_VAR, 0, I::UINT32_MAX],
        self::DATADIR                                   => [S::GLOBAL,  false, T::CHAR,     '/var/lib/mysql/data', F::NO_PERSIST], // dir
        self::DEBUG                                     => [null,       true,  T::CHAR,     'd:t:i:o,/tmp/mysqld.trace', F::NULLABLE],
        self::DEBUG_SYNC                                => [S::SESSION, true,  T::CHAR,     ''],
        self::DEBUG_SENSITIVE_SESSION_STRING            => [null,       true,  T::CHAR,     null,       F::NULLABLE],
        self::DEFAULT_AUTHENTICATION_PLUGIN             => [S::GLOBAL,  false, T::ENUM,     'CACHING_SHA2_PASSWORD', F::NONE, ['MYSQL_NATIVE_PASSWORD', 'SHA256_PASSWORD', 'CACHING_SHA2_PASSWORD']],
        self::DEFAULT_COLLATION_FOR_UTF8MB4             => [null,       true,  T::ENUM,     'UTF8MB4_0900_AI_CI', F::NONE, ['UTF8MB4_0900_AI_CI', 'UTF8MB4_GENERAL_CI']],
        self::DEFAULT_PASSWORD_LIFETIME                 => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, 65536],
        self::DEFAULT_STORAGE_ENGINE                    => [null,       true,  StorageEngine::class, StorageEngine::INNODB],
        self::DEFAULT_TABLE_ENCRYPTION                  => [null,       true,  T::BOOL,     false,      F::SET_VAR],
        self::DEFAULT_TMP_STORAGE_ENGINE                => [null,       true,  StorageEngine::class, StorageEngine::INNODB, F::SET_VAR],
        self::DEFAULT_WEEK_FORMAT                       => [null,       true,  T::UNSIGNED, 0,          F::CLAMP, 0, 7],
        self::DELAY_KEY_WRITE                           => [S::GLOBAL,  true,  T::ENUM,     'ON',       F::NONE, ['ON', 'OFF', 'ALL']],
        self::DELAYED_INSERT_LIMIT                      => [S::GLOBAL,  true,  T::UNSIGNED, 100,        F::NONE, 1, MAX],
        self::DELAYED_INSERT_TIMEOUT                    => [S::GLOBAL,  true,  T::UNSIGNED, 300,        F::CLAMP, 1, Seconds::COMMON_YEAR],
        self::DELAYED_QUEUE_SIZE                        => [S::GLOBAL,  true,  T::UNSIGNED, 1000,       F::NONE, 1, MAX],
        self::DISABLED_STORAGE_ENGINES                  => [S::GLOBAL,  false, T::CHAR,     ''],
        self::DISCONNECT_ON_EXPIRED_PASSWORD            => [S::GLOBAL,  false, T::BOOL,     true],
        self::DIV_PRECISION_INCREMENT                   => [null,       true,  T::UNSIGNED, 4,          F::SET_VAR | F::CLAMP, 0, 30],
        self::DRAGNET__LOG_ERROR_FILTER_RULES           => [S::GLOBAL,  true,  T::CHAR,     'IF prio>=INFORMATION THEN drop. IF EXISTS source_line THEN unset source_line.'],
        self::ENTERPRISE_ENCRYPTION__MAXIMUM_RSA_KEY_SIZE
                                                        => [S::GLOBAL,  true,  T::UNSIGNED, 4096,       F::NONE, 2048, 16384],
        self::ENTERPRISE_ENCRYPTION__RSA_SUPPORT_LEGACY_PADDING
                                                        => [S::GLOBAL,  true,  T::BOOL,     false],
        self::END_MARKERS_IN_JSON                       => [null,       true,  T::BOOL,     false,      F::SET_VAR],
        self::EQ_RANGE_INDEX_DIVE_LIMIT                 => [null,       true,  T::UNSIGNED, 200,        F::SET_VAR | F::CLAMP, 0, I::UINT32_MAX],
        self::ERROR_COUNT                               => [S::SESSION, false, T::UNSIGNED, 0,          F::NONE, 0, MAX],
        self::EVENT_SCHEDULER                           => [S::GLOBAL,  true,  T::ENUM,     'ON',       F::NONE, ['ON', 'OFF', 'DISABLED']], // DISABLED only with PERSIST_ONLY
        self::EXPLAIN_FORMAT                            => [null,       true,  T::ENUM,     'TRADITIONAL', F::NONE, ['TRADITIONAL', 'JSON', 'TREE', 'DEFAULT']],
        self::EXPLICIT_DEFAULTS_FOR_TIMESTAMP           => [null,       true,  T::BOOL,     true],
        self::EXTERNAL_USER                             => [S::SESSION, false, T::CHAR,     null],
        self::FLUSH                                     => [S::GLOBAL,  true,  T::BOOL,     false],
        self::FLUSH_TIME                                => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, Seconds::COMMON_YEAR],
        self::FOREIGN_KEY_CHECKS                        => [null,       true,  T::BOOL,     true,       F::SET_VAR],
        self::FT_BOOLEAN_SYNTAX                         => [S::GLOBAL,  true,  T::CHAR,     '+ -><()~*:""&|'],
        self::FT_MAX_WORD_LEN                           => [S::GLOBAL,  false, T::UNSIGNED, 84,         F::NONE, 10, 84],
        self::FT_MIN_WORD_LEN                           => [S::GLOBAL,  false, T::UNSIGNED, 4,          F::NONE, 1, 82],
        self::FT_QUERY_EXPANSION_LIMIT                  => [S::GLOBAL,  false, T::UNSIGNED, 20,         F::NONE, 0, 1000],
        self::FT_STOPWORD_FILE                          => [S::GLOBAL,  false, T::CHAR,     null,       F::NO_PERSIST], // file,
        self::GENERAL_LOG                               => [S::GLOBAL,  true,  T::BOOL,     false],
        self::GENERAL_LOG_FILE                          => [S::GLOBAL,  true,  T::CHAR,     'host_name.log', F::NON_EMPTY], // file
        self::GENERATED_RANDOM_PASSWORD_LENGTH          => [null,       true,  T::UNSIGNED, 20,         F::CLAMP, 2, 255],
        self::GLOBAL_CONNECTION_MEMORY_LIMIT            => [S::GLOBAL,  true,  T::UNSIGNED, MAX,        F::CLAMP, 16777216, MAX],
        self::GLOBAL_CONNECTION_MEMORY_TRACKING         => [null,       true,  T::BOOL,     false],
        self::GROUP_CONCAT_MAX_LEN                      => [null,       true,  T::UNSIGNED, 1024,       F::CLAMP | F::SET_VAR, 4, MAX],
        self::HAVE_COMPRESS                             => [S::GLOBAL,  false, T::BOOL,     true],
        self::HAVE_CRYPT                                => [S::GLOBAL,  false, T::BOOL,     false], // deprecated?
        self::HAVE_DYNAMIC_LOADING                      => [S::GLOBAL,  false, T::BOOL,     true],
        self::HAVE_GEOMETRY                             => [S::GLOBAL,  false, T::BOOL,     true],
        self::HAVE_OPENSSL                              => [S::GLOBAL,  false, T::CHAR,     'YES'], // deprecated
        self::HAVE_PROFILING                            => [S::GLOBAL,  false, T::BOOL,     false], // deprecated
        self::HAVE_QUERY_CACHE                          => [S::GLOBAL,  false, T::BOOL,     false], // removed
        self::HAVE_RTREE_KEYS                           => [S::GLOBAL,  false, T::BOOL,     true],
        self::HAVE_SSL                                  => [S::GLOBAL,  false, T::CHAR,     'YES'], // deprecated
        self::HAVE_STATEMENT_TIMEOUT                    => [S::GLOBAL,  false, T::BOOL,     true,       F::NO_PERSIST],
        self::HAVE_SYMLINK                              => [S::GLOBAL,  false, T::BOOL,     true,       F::NO_PERSIST],
        self::HISTOGRAM_GENERATION_MAX_MEM_SIZE         => [null,       true,  T::UNSIGNED, 20000000,   F::CLAMP, 1000000, MAX],
        self::HOST_CACHE_SIZE                           => [S::GLOBAL,  true,  T::UNSIGNED, null,       F::CLAMP, 0, 65536], // default -1 = auto
        self::HOSTNAME                                  => [S::GLOBAL,  false, T::CHAR,     null,       F::NO_PERSIST],
        self::IDENTITY                                  => [S::SESSION, true,  T::UNSIGNED, 0,          F::CLAMP, 0, MAX], // alias for last_insert_id
        self::IGNORE_DB_DIRS                            => [S::GLOBAL,  true,  T::CHAR,     null],
        self::INIT_CONNECT                              => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE],
        self::INFORMATION_SCHEMA_STATS_EXPIRY           => [null,       true,  T::UNSIGNED, 86400,      F::CLAMP, 0, Seconds::COMMON_YEAR],
        self::INIT_FILE                                 => [S::GLOBAL,  false, T::CHAR,     null], // file
        self::INSERT_ID                                 => [S::SESSION, true,  T::UNSIGNED, 0,          F::CLAMP | F::SET_VAR, 0, MAX],
        self::INTERACTIVE_TIMEOUT                       => [null,       true,  T::UNSIGNED, 28800,      F::CLAMP, 0, Seconds::COMMON_YEAR],
        self::INTERNAL_TMP_DISK_STORAGE_ENGINE          => [S::GLOBAL,  true,  StorageEngine::class, StorageEngine::INNODB],
        self::INTERNAL_TMP_MEM_STORAGE_ENGINE           => [null,       true,  StorageEngine::class, StorageEngine::TEMP_TABLE, F::SET_VAR],
        self::JOIN_BUFFER_SIZE                          => [null,       true,  T::UNSIGNED, 262144,     F::CLAMP | F::SET_VAR, 128, MAX, 128],
        self::KEEP_FILES_ON_CREATE                      => [null,       true,  T::BOOL,     false],
        self::KEY_BUFFER_SIZE                           => [S::GLOBAL,  true,  T::UNSIGNED, 8388608,    F::CLAMP, 0, MAX],
        self::KEY_CACHE_AGE_THRESHOLD                   => [S::GLOBAL,  true,  T::UNSIGNED, 300,        F::NONE, 100, MAX, 100],
        self::KEY_CACHE_BLOCK_SIZE                      => [S::GLOBAL,  true,  T::UNSIGNED, 1024,       F::CLAMP, 512, 16384, 512],
        self::KEY_CACHE_DIVISION_LIMIT                  => [S::GLOBAL,  true,  T::UNSIGNED, 100,        F::CLAMP, 1, 100],
        self::LARGE_FILES_SUPPORT                       => [S::GLOBAL,  false, T::BOOL,     true,       F::NO_PERSIST],
        self::LARGE_PAGES                               => [S::GLOBAL,  false, T::BOOL,     false],
        self::LARGE_PAGE_SIZE                           => [S::GLOBAL,  false, T::UNSIGNED, 0,          F::NO_PERSIST, 0, 65536],
        self::LAST_INSERT_ID                            => [S::SESSION, true,  T::UNSIGNED, null],
        self::LC_MESSAGES                               => [null,       true,  T::CHAR,     'en_US'],
        self::LC_MESSAGES_DIR                           => [S::GLOBAL,  false, T::CHAR,     null,       F::NO_PERSIST], // dir
        self::LC_TIME_NAMES                             => [null,       true,  T::CHAR,     'en_US'],
        self::LICENSE                                   => [S::GLOBAL,  false, T::CHAR,     'GPL',      F::NO_PERSIST],
        self::LOCAL_INFILE                              => [S::GLOBAL,  true,  T::BOOL,     false],
        self::LOCK_WAIT_TIMEOUT                         => [null,       true,  T::UNSIGNED, 31536000,   F::CLAMP | F::SET_VAR, 1, Seconds::COMMON_YEAR],
        self::LOCKED_IN_MEMORY                          => [S::GLOBAL,  false, T::BOOL,     false,      F::NO_PERSIST],
        self::LOG_ERROR                                 => [S::GLOBAL,  false, T::CHAR,     null,       F::NO_PERSIST], // file
        self::LOG_ERROR_SERVICES                        => [S::GLOBAL,  true,  T::CHAR,     'log_filter_internal; log_sink_internal'],
        self::LOG_ERROR_SUPPRESSION_LIST                => [S::GLOBAL,  true,  T::CHAR,     ''],
        self::LOG_ERROR_VERBOSITY                       => [S::GLOBAL,  true,  T::UNSIGNED, 2,          F::CLAMP, 1, 3],
        self::LOG_OUTPUT                                => [S::GLOBAL,  true,  T::CHAR,     'FILE',     F::NON_EMPTY, ['TABLE', 'FILE', 'NONE']], // looks like SET or ENUM, but it really is some terrible abomination
        self::LOG_QUERIES_NOT_USING_INDEXES             => [S::GLOBAL,  true,  T::BOOL,     false],
        self::LOG_RAW                                   => [S::GLOBAL,  true,  T::BOOL,     false],
        self::LOG_SLOW_ADMIN_STATEMENTS                 => [S::GLOBAL,  true,  T::BOOL,     false],
        self::LOG_SLOW_EXTRA                            => [S::GLOBAL,  true,  T::BOOL,     false],
        self::LOG_SYSLOG                                => [S::GLOBAL,  true,  T::BOOL,     false], // removed
        self::LOG_SYSLOG_FACILITY                       => [S::GLOBAL,  true,  T::CHAR,     'daemon'],
        self::LOG_SYSLOG_INCLUDE_PID                    => [S::GLOBAL,  true,  T::BOOL,     true],
        self::LOG_SYSLOG_TAG                            => [S::GLOBAL,  true,  T::CHAR,     ''],
        self::LOG_TIMESTAMPS                            => [S::GLOBAL,  true,  T::ENUM,     'UTC',      F::NONE, ['UTC', 'SYSTEM']],
        self::LOG_THROTTLE_QUERIES_NOT_USING_INDEXES    => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, I::UINT32_MAX],
        self::LONG_QUERY_TIME                           => [null,       true,  T::NUMERIC,  10,         F::CLAMP, 0, 31536000],
        self::LOW_PRIORITY_UPDATES                      => [null,       true,  T::BOOL,     0],
        self::LOWER_CASE_FILE_SYSTEM                    => [S::GLOBAL,  false, T::BOOL,     false,      F::NO_PERSIST],
        self::LOWER_CASE_TABLE_NAMES                    => [S::GLOBAL,  false, T::UNSIGNED, 0,          F::NONE, 0, 2],
        self::MANDATORY_ROLES                           => [S::GLOBAL,  true,  T::CHAR,     ''],
        self::MAX_ALLOWED_PACKET                        => [null,       true,  T::UNSIGNED, 67108864,   F::CLAMP | F::SESSION_READONLY, 1024, 1073741824, 1024],
        self::MAX_CONNECT_ERRORS                        => [S::GLOBAL,  true,  T::UNSIGNED, 100,        F::NONE, 1, MAX],
        self::MAX_CONNECTIONS                           => [S::GLOBAL,  true,  T::UNSIGNED, 151,        F::CLAMP, 1, 100000],
        self::MAX_DELAYED_THREADS                       => [null,       true,  T::UNSIGNED, 20,         F::CLAMP, 0, 16384], // deprecated
        self::MAX_DIGEST_LENGTH                         => [S::GLOBAL,  false, T::UNSIGNED, 1024,       F::NONE, 0, 1048576],
        self::MAX_ERROR_COUNT                           => [null,       true,  T::UNSIGNED, 1024,       F::CLAMP | F::SET_VAR, 0, 65536],
        self::MAX_EXECUTION_TIME                        => [null,       true,  T::UNSIGNED, 0,          F::CLAMP | F::SET_VAR, 0, I::UINT32_MAX],
        self::MAX_HEAP_TABLE_SIZE                       => [null,       true,  T::UNSIGNED, 16777216,   F::CLAMP | F::SET_VAR, 16384, MAX, 1024],
        self::MAX_INSERT_DELAYED_THREADS                => [null,       true,  T::UNSIGNED, 0,          F::CLAMP, 20, 16384],
        self::MAX_JOIN_SIZE                             => [null,       true,  T::UNSIGNED, MAX,        F::CLAMP | F::SET_VAR, 1, MAX],
        self::MAX_LENGTH_FOR_SORT_DATA                  => [null,       true,  T::UNSIGNED, 4096,       F::SET_VAR, 4, 8388608],
        self::MAX_POINTS_IN_GEOMETRY                    => [null,       true,  T::UNSIGNED, 65536,      F::CLAMP | F::SET_VAR, 3, 1048576],
        self::MAX_PREPARED_STMT_COUNT                   => [S::GLOBAL,  true,  T::UNSIGNED, 16384,      F::CLAMP, 0, 1048576],
        self::MAX_SEEKS_FOR_KEY                         => [null,       true,  T::UNSIGNED, MAX,        F::SET_VAR, 1, MAX],
        self::MAX_SORT_LENGTH                           => [null,       true,  T::UNSIGNED, 1024,       F::CLAMP | F::SET_VAR, 4, 8388608],
        self::MAX_SP_RECURSION_DEPTH                    => [null,       true,  T::UNSIGNED, 0,          F::CLAMP, 0, 255],
        self::MAX_USER_CONNECTIONS                      => [null,       true,  T::UNSIGNED, 0,          F::CLAMP | F::SESSION_READONLY, 0, I::UINT32_MAX],
        self::MAX_WRITE_LOCK_COUNT                      => [S::GLOBAL,  true,  T::UNSIGNED, MAX,        F::NONE, 1, MAX],
        self::MECAB_RC_FILE                             => [S::GLOBAL,  false, T::CHAR,     null], // file
        self::METADATA_LOCKS_CACHE_SIZE                 => [S::GLOBAL,  false, T::UNSIGNED, 1024,       F::NONE, 1, 1048576],
        self::METADATA_LOCKS_HASH_INSTANCES             => [S::GLOBAL,  false, T::UNSIGNED, 8,          F::NONE, 1, 1024],
        self::MIN_EXAMINED_ROW_LIMIT                    => [null,       true,  T::UNSIGNED, 0,          F::NONE, 0, MAX],
        self::MYISAM_DATA_POINTER_SIZE                  => [S::GLOBAL,  true,  T::UNSIGNED, 6,          F::CLAMP, 2, 7],
        self::MYISAM_MAX_SORT_FILE_SIZE                 => [S::GLOBAL,  true,  T::UNSIGNED, MAX,        F::NONE, 0, MAX],
        self::MYISAM_MMAP_SIZE                          => [S::GLOBAL,  false, T::UNSIGNED, MAX,        F::NONE, 7, MAX],
        self::MYISAM_RECOVER_OPTIONS                    => [S::GLOBAL,  false, T::ENUM,     'OFF',      F::NONE, ['OFF', 'DEFAULT', 'BACKUP', 'FORCE', 'QUICK']],
        self::MYISAM_REPAIR_THREADS                     => [null,       true,  T::UNSIGNED, 1,          F::NONE, 1, MAX],
        self::MYISAM_SORT_BUFFER_SIZE                   => [null,       true,  T::UNSIGNED, 8388608,    F::CLAMP, 4096, MAX],
        self::MYISAM_STATS_METHOD                       => [null,       true,  T::ENUM,     'NULLS_UNEQUAL', F::NONE, ['NULLS_UNEQUAL', 'NULLS_EQUAL', 'NULLS_IGNORED']],
        self::MYISAM_USE_MMAP                           => [S::GLOBAL,  true,  T::BOOL,     false],
        self::MYSQL_NATIVE_PASSWORD_PROXY_USERS         => [S::GLOBAL,  true,  T::BOOL,     false],
        self::NAMED_PIPE                                => [S::GLOBAL,  false, T::BOOL,     false],
        self::NAMED_PIPE_FULL_ACCESS_GROUP              => [S::GLOBAL,  true,  T::CHAR,     '',         F::NULLABLE],
        self::NET_BUFFER_LENGTH                         => [null,       true,  T::UNSIGNED, 16384,      F::CLAMP | F::SESSION_READONLY, 1024, 1048576, 1024],
        self::NET_READ_TIMEOUT                          => [null,       true,  T::UNSIGNED, 30,         F::CLAMP, 1, Seconds::COMMON_YEAR],
        self::NET_RETRY_COUNT                           => [null,       true,  T::UNSIGNED, 10,         F::NONE, 1, MAX],
        self::NET_WRITE_TIMEOUT                         => [null,       true,  T::UNSIGNED, 60,         F::CLAMP, 1, Seconds::COMMON_YEAR],
        self::NEW                                       => [null,       true,  T::BOOL,     false],
        self::NGRAM_TOKEN_SIZE                          => [S::GLOBAL,  false, T::UNSIGNED, 2,          F::NONE, 1, 10],
        self::OFFLINE_MODE                              => [S::GLOBAL,  true,  T::BOOL,     false],
        self::OLD                                       => [S::GLOBAL,  false, T::BOOL,     false],
        self::OLD_ALTER_TABLE                           => [null,       true,  T::BOOL,     false],
        self::OPEN_FILES_LIMIT                          => [S::GLOBAL,  false, T::UNSIGNED, 5000,       F::NONE, 0, MAX],
        self::OPTIMIZER_PRUNE_LEVEL                     => [null,       true,  T::UNSIGNED, 1,          F::CLAMP | F::SET_VAR, 0, 1],
        self::OPTIMIZER_SEARCH_DEPTH                    => [null,       true,  T::UNSIGNED, 62,         F::CLAMP | F::SET_VAR, 0, 62],
        self::OPTIMIZER_SWITCH                          => [null,       true,  T::CHAR,     '',         F::SET_VAR], // set
        self::OPTIMIZER_TRACE                           => [null,       true,  T::CHAR,     ''],
        self::OPTIMIZER_TRACE_FEATURES                  => [null,       true,  T::CHAR,     ''],
        self::OPTIMIZER_TRACE_LIMIT                     => [null,       true,  T::UNSIGNED, 1,          F::CLAMP, 0, I::INT32_MAX],
        self::OPTIMIZER_TRACE_MAX_MEM_SIZE              => [null,       true,  T::UNSIGNED, 1048576,    F::CLAMP | F::SET_VAR, 0, I::UINT32_MAX],
        self::OPTIMIZER_TRACE_OFFSET                    => [null,       true,  T::SIGNED,   -1,         F::NONE, I::INT32_MIN, I::INT32_MAX],
        self::PARSER_MAX_MEM_SIZE                       => [null,       true,  T::UNSIGNED, MAX,        F::NONE, 10000000, MAX],
        self::PARTIAL_REVOKES                           => [S::GLOBAL,  true,  T::BOOL,     false],
        self::PASSWORD_HISTORY                          => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, I::UINT32_MAX],
        self::PASSWORD_REQUIRE_CURRENT                  => [S::GLOBAL,  true,  T::BOOL,     false],
        self::PASSWORD_REUSE_INTERVAL                   => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, I::UINT32_MAX],
        self::PERSISTED_GLOBALS_LOAD                    => [S::GLOBAL,  false, T::BOOL,     true,       F::NO_PERSIST],
        self::PERSIST_ONLY_ADMIN_X509_SUBJECT           => [S::GLOBAL,  false, T::CHAR,     '',         F::NO_PERSIST],
        self::PERSIST_SENSITIVE_VARIABLES_IN_PLAINTEXT  => [S::GLOBAL,  false, T::BOOL,     true],
        self::PID_FILE                                  => [S::GLOBAL,  false, T::CHAR,     'example.pid', F::NO_PERSIST], // file
        self::PLUGIN_DIR                                => [S::GLOBAL,  false, T::CHAR,     '/var/lib/mysql/lib/plugin', F::NO_PERSIST], // dir
        self::PORT                                      => [S::GLOBAL,  false, T::UNSIGNED, 3306,       F::NO_PERSIST, 0, 65535],
        self::PRELOAD_BUFFER_SIZE                       => [null,       true,  T::UNSIGNED, 32768,      F::CLAMP, 1024, 1073741824],
        self::PRINT_IDENTIFIED_WITH_AS_HEX              => [null,       true,  T::BOOL,     false],
        self::PROFILING                                 => [null,       true,  T::BOOL,     false], // deprecated
        self::PROFILING_HISTORY_SIZE                    => [null,       true,  T::UNSIGNED, 15,         F::CLAMP, 1, 100], // deprecated
        self::PROTOCOL_COMPRESSION_ALGORITHMS           => [S::GLOBAL,  true,  T::SET,      'ZLIB,ZSTD,UNCOMPRESSED', F::NON_EMPTY, ['ZLIB', 'ZSTD', 'UNCOMPRESSED']],
        self::PROTOCOL_VERSION                          => [S::GLOBAL,  false, T::UNSIGNED, 10,         F::NO_PERSIST, 0, I::UINT32_MAX],
        self::PROXY_USER                                => [S::SESSION, false, T::CHAR,     null],
        self::PSEUDO_REPLICA_MODE                       => [S::SESSION, true,  T::BOOL,     false],
        self::PSEUDO_SLAVE_MODE                         => [S::SESSION, true,  T::BOOL,     false],
        self::PSEUDO_THREAD_ID                          => [S::SESSION, true,  T::UNSIGNED, 2147483647, F::NONE, 0, I::INT32_MAX],
        self::QUERY_ALLOC_BLOCK_SIZE                    => [null,       true,  T::UNSIGNED, 8192,       F::CLAMP, 1024, 4294966272, 1024],
        self::QUERY_PREALLOC_SIZE                       => [null,       true,  T::UNSIGNED, 8192,       F::CLAMP, 8192, MAX, 1024],
        self::RAND_SEED1                                => [S::SESSION, true,  T::UNSIGNED, null,       F::NONE, 0, I::UINT32_MAX],
        self::RAND_SEED2                                => [S::SESSION, true,  T::UNSIGNED, null,       F::NONE, 0, I::UINT32_MAX],
        self::RANGE_ALLOC_BLOCK_SIZE                    => [null,       true,  T::UNSIGNED, 4096,       F::CLAMP | F::SET_VAR, 4096, MAX, 1024],
        self::RANGE_OPTIMIZER_MAX_MEM_SIZE              => [null,       true,  T::UNSIGNED, 8388608,    F::CLAMP | F::SET_VAR, 0, MAX],
        self::RBR_EXEC_MODE                             => [S::SESSION, true,  T::ENUM,     'STRICT',   F::NONE, ['STRICT', 'IDEMPOTENT']],
        self::READ_BUFFER_SIZE                          => [null,       true,  T::UNSIGNED, 131072,     F::CLAMP | F::SET_VAR, 8192, I::INT32_MAX, 4096],
        self::READ_ONLY                                 => [S::GLOBAL,  true,  T::BOOL,     false],
        self::READ_RND_BUFFER_SIZE                      => [null,       true,  T::UNSIGNED, 262144,     F::CLAMP, 1, I::INT32_MAX],
        self::REGEXP_STACK_LIMIT                        => [S::GLOBAL,  true,  T::UNSIGNED, 8000000,    F::NONE, 0, I::INT32_MAX],
        self::REGEXP_TIME_LIMIT                         => [S::GLOBAL,  true,  T::UNSIGNED, 32,         F::NONE, 0, I::INT32_MAX],
        self::REQUIRE_ROW_FORMAT                        => [S::SESSION, true,  T::BOOL,     false],
        self::REQUIRE_SECURE_TRANSPORT                  => [S::GLOBAL,  true,  T::BOOL,     false],
        self::RESULTSET_METADATA                        => [S::SESSION, true,  T::ENUM,     'FULL',     F::NONE, ['FULL', 'NONE']],
        self::SECONDARY_ENGINE_COST_THRESHOLD           => [S::SESSION, true,  T::UNSIGNED, 100000.0,   F::SET_VAR, 0.0, 1.7976931348623157e+308],
        self::SCHEMA_DEFINITION_CACHE                   => [S::GLOBAL,  true,  T::UNSIGNED, 256,        F::NONE, 256, 524288],
        self::SECURE_FILE_PRIV                          => [S::GLOBAL,  false, T::CHAR,     null,       F::NO_PERSIST],
        self::SELECT_INTO_BUFFER_SIZE                   => [null,       true,  T::UNSIGNED, 131072,     F::CLAMP | F::SET_VAR, 8192, I::INT32_MAX, 4096],
        self::SELECT_INTO_DISK_SYNC                     => [null,       true,  T::BOOL,     false,      F::SET_VAR],
        self::SELECT_INTO_DISK_SYNC_DELAY               => [null,       true,  T::UNSIGNED, 0,          F::CLAMP | F::SET_VAR, 0, Seconds::COMMON_YEAR],
        self::SESSION_TRACK_GTIDS                       => [null,       true,  T::ENUM,     'OFF',      F::NONE, ['OFF', 'OWN_GTID', 'ALL_GTIDS']],
        self::SESSION_TRACK_SCHEMA                      => [null,       true,  T::BOOL,     true],
        self::SESSION_TRACK_STATE_CHANGE                => [null,       true,  T::BOOL,     false],
        self::SESSION_TRACK_SYSTEM_VARIABLES            => [null,       true,  T::CHAR,     'time_zone,autocommit,character_set_client,character_set_results,character_set_connection', F::NULLABLE],
        self::SESSION_TRACK_TRANSACTION_INFO            => [null,       true,  T::ENUM,     'OFF',      F::NONE, ['OFF', 'STATE', 'CHARACTERISTICS']],
        self::SHA256_PASSWORD_AUTO_GENERATE_RSA_KEYS    => [S::GLOBAL,  false, T::BOOL,     true],
        self::SHA256_PASSWORD_PRIVATE_KEY_PATH          => [S::GLOBAL,  false, T::CHAR,     'private_key.pem'], // file
        self::SHA256_PASSWORD_PROXY_USERS               => [S::GLOBAL,  true,  T::BOOL,     false],
        self::SHA256_PASSWORD_PUBLIC_KEY_PATH           => [S::GLOBAL,  false, T::CHAR,     'public_key.pem'], // file
        self::SHARED_MEMORY                             => [S::GLOBAL,  false, T::BOOL,     false],
        self::SHARED_MEMORY_BASE_NAME                   => [S::GLOBAL,  false, T::CHAR,     'MYSQL'],
        self::SHOW_CREATE_TABLE_SKIP_SECONDARY_ENGINE   => [S::SESSION, true,  T::BOOL,     false,      F::SET_VAR],
        self::SHOW_CREATE_TABLE_VERBOSITY               => [null,       true,  T::BOOL,     false],
        self::SHOW_GIPK_IN_CREATE_TABLE_AND_INFORMATION_SCHEMA
                                                        => [null,       true,  T::BOOL,     true],
        self::SHOW_OLD_TEMPORALS                        => [null,       true,  T::BOOL,     false],
        self::SKIP_EXTERNAL_LOCKING                     => [S::GLOBAL,  false, T::BOOL,     true,       F::NO_PERSIST],
        self::SKIP_NAME_RESOLVE                         => [S::GLOBAL,  false, T::BOOL,     false],
        self::SKIP_NETWORKING                           => [S::GLOBAL,  false, T::BOOL,     false],
        self::SKIP_SHOW_DATABASE                        => [S::GLOBAL,  false, T::BOOL,     false],
        self::SLOW_LAUNCH_TIME                          => [S::GLOBAL,  true,  T::UNSIGNED, 2,          F::CLAMP, 0, Seconds::COMMON_YEAR],
        self::SLOW_QUERY_LOG                            => [S::GLOBAL,  true,  T::BOOL,     false],
        self::SLOW_QUERY_LOG_FILE                       => [S::GLOBAL,  true,  T::CHAR,     'example-slow.log'],
        self::SOCKET                                    => [S::GLOBAL,  false, T::CHAR,     '/tmp/mysql.sock', F::NO_PERSIST],
        self::SORT_BUFFER_SIZE                          => [null,       true,  T::UNSIGNED, 262144,     F::CLAMP | F::SET_VAR, 32768, MAX],
        self::SQL_AUTO_IS_NULL                          => [null,       true,  T::BOOL,     false,      F::SET_VAR],
        self::SQL_BIG_SELECTS                           => [null,       true,  T::BOOL,     false,      F::SET_VAR],
        self::SQL_BUFFER_RESULT                         => [null,       true,  T::BOOL,     false,      F::SET_VAR],
        self::SQL_GENERATE_INVISIBLE_PRIMARY_KEY        => [null,       true,  T::BOOL,     false],
        self::SQL_LOG_OFF                               => [null,       true,  T::BOOL,     false],
        self::SQL_MODE                                  => [null,       true,  SqlMode::class, 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION', F::SET_VAR],
        self::SQL_NOTES                                 => [null,       true,  T::BOOL,     true],
        self::SQL_QUOTE_SHOW_CREATE                     => [null,       true,  T::BOOL,     true],
        self::SQL_REQUIRE_PRIMARY_KEY                   => [null,       true,  T::BOOL,     false,      F::SET_VAR],
        self::SQL_SAFE_UPDATES                          => [null,       true,  T::BOOL,     false,      F::SET_VAR],
        self::SQL_SELECT_LIMIT                          => [null,       true,  T::UNSIGNED, MAX,        F::SET_VAR, 0, MAX],
        self::SQL_WARNINGS                              => [null,       true,  T::BOOL,     false],
        self::SSL_CA                                    => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE], // file
        self::SSL_CAPATH                                => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE], // dir
        self::SSL_CERT                                  => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE], // file
        self::SSL_CIPHER                                => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE],
        self::SSL_CRL                                   => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE], // file
        self::SSL_CRLPATH                               => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE], // dir
        self::SSL_FIPS_MODE                             => [S::GLOBAL,  true,  T::ENUM,     'OFF',      F::NONE, ['OFF', 'ON', 'STRICT']],
        self::SSL_KEY                                   => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE], // file
        self::SSL_SESSION_CACHE_MODE                    => [S::GLOBAL,  true,  T::BOOL,     true],
        self::SSL_SESSION_CACHE_TIMEOUT                 => [S::GLOBAL,  true,  T::UNSIGNED, 300,        F::CLAMP, 0, 86400],
        self::STORED_PROGRAM_CACHE                      => [S::GLOBAL,  true,  T::UNSIGNED, 256,        F::CLAMP, 16, 524288],
        self::STORED_PROGRAM_DEFINITION_CACHE           => [S::GLOBAL,  true,  T::UNSIGNED, 256,        F::NONE, 256, 524288],
        self::SUPER_READ_ONLY                           => [S::GLOBAL,  true,  T::BOOL,     false],
        self::SYSEVENTLOG__FACILITY                     => [S::GLOBAL,  true,  T::CHAR,     'daemon',   F::NON_EMPTY],
        self::SYSEVENTLOG__INCLUDE_PID                  => [S::GLOBAL,  true,  T::BOOL,     true],
        self::SYSEVENTLOG__TAG                          => [S::GLOBAL,  true,  T::CHAR,     ''],
        self::SYSTEM_TIME_ZONE                          => [S::GLOBAL,  false, T::CHAR,     null,       F::NO_PERSIST],
        self::TABLE_DEFINITION_CACHE                    => [S::GLOBAL,  true,  T::UNSIGNED, 2000,       F::CLAMP, 400, 524288], // -1 = auto
        self::TABLE_ENCRYPTION_PRIVILEGE_CHECK          => [S::GLOBAL,  true,  T::BOOL,     false],
        self::TABLE_OPEN_CACHE                          => [S::GLOBAL,  true,  T::UNSIGNED, 4000,       F::CLAMP, 1, 524288],
        self::TABLE_OPEN_CACHE_INSTANCES                => [S::GLOBAL,  false, T::UNSIGNED, 16,         F::NONE, 1, 64],
        self::TABLESPACE_DEFINITION_CACHE               => [S::GLOBAL,  true,  T::UNSIGNED, 256,        F::NONE, 256, 524288],
        self::TEMPTABLE_MAX_MMAP                        => [S::GLOBAL,  true,  T::UNSIGNED, 1073741824, F::NONE, 0, MAX],
        self::TEMPTABLE_MAX_RAM                         => [S::GLOBAL,  true,  T::UNSIGNED, 1073741824, F::CLAMP, 0, MAX],
        self::TEMPTABLE_USE_MMAP                        => [S::GLOBAL,  true,  T::BOOL,     true],
        self::THREAD_CACHE_SIZE                         => [S::GLOBAL,  true,  T::UNSIGNED, 9,          F::CLAMP, 0, 16384], // -1 = auto
        self::THREAD_HANDLING                           => [S::GLOBAL,  false, T::ENUM,     'ONE-THREAD-PER-CONNECTION', F::NONE, ['NO-THREADS', 'ONE-THREAD-PER-CONNECTION', 'LOADED-DYNAMICALLY']],
        self::THREAD_POOL_ALGORITHM                     => [S::GLOBAL,  false, T::UNSIGNED, 0,          F::NONE, 0, 1],
        self::THREAD_POOL_HIGH_PRIORITY_CONNECTION      => [null,       true,  T::UNSIGNED, 0,          F::NONE, 0, 1],
        self::THREAD_POOL_MAX_ACTIVE_QUERY_THREADS      => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, 512],
        self::THREAD_POOL_MAX_UNUSED_THREADS            => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, 4096],
        self::THREAD_POOL_PRIO_KICKUP_TIMER             => [S::GLOBAL,  true,  T::UNSIGNED, 1000,       F::NONE, 0, 4294967294],
        self::THREAD_POOL_SIZE                          => [S::GLOBAL,  false, T::UNSIGNED, 16,         F::NONE, 1, 512],
        self::THREAD_POOL_STALL_LIMIT                   => [S::GLOBAL,  true,  T::UNSIGNED, 6,          F::NONE, 4, 600],
        self::THREAD_STACK                              => [S::GLOBAL,  false, T::UNSIGNED, 1048576,    F::NONE, 131072, MAX, 1024],
        self::TIME_ZONE                                 => [null,       true,  T::CHAR,     'SYSTEM',   F::SET_VAR],
        self::TIMESTAMP                                 => [S::SESSION, true,  T::UNSIGNED, 1,          F::CLAMP_MIN | F::SET_VAR, 0, I::INT32_MAX], // unix_timestamp()
        self::TLS_CIPHERSUITES                          => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE],
        self::TLS_VERSION                               => [S::GLOBAL,  true,  T::SET,     'TLSV1.2,TLSV1.3', F::NONE, ['TLSV1.2', 'TLSV1.3']],
        self::TMP_TABLE_SIZE                            => [null,       true,  T::UNSIGNED, 16777216,   F::CLAMP | F::SET_VAR, 1024, MAX],
        self::TMPDIR                                    => [S::GLOBAL,  false, T::CHAR,     '/tmp',     F::NO_PERSIST], // dir
        self::TRANSACTION_ALLOC_BLOCK_SIZE              => [null,       true,  T::UNSIGNED, 8192,       F::CLAMP, 1024, 131072, 1024],
        self::TRANSACTION_ISOLATION                     => [null,       true,  T::ENUM,     'REPEATABLE-READ', F::NONE, ['READ-UNCOMMITTED', 'READ-COMMITTED', 'REPEATABLE-READ', 'SERIALIZABLE']],
        self::TRANSACTION_PREALLOC_SIZE                 => [null,       true,  T::UNSIGNED, 4096,       F::CLAMP, 1024, 131072, 1024],
        self::TRANSACTION_READ_ONLY                     => [null,       true,  T::BOOL,     false],
        self::UNIQUE_CHECKS                             => [null,       true,  T::BOOL,     true,       F::SET_VAR],
        self::UPDATABLE_VIEWS_WITH_LIMIT                => [null,       true,  T::ENUM,     true,       F::SET_VAR, ['YES', 'NO']],
        self::USE_SECONDARY_ENGINE                      => [S::SESSION, true,  T::ENUM,     'ON',       F::SET_VAR, ['OFF', 'ON', 'FORCED']],
        self::VERSION                                   => [S::GLOBAL,  false, T::CHAR,     'x.y.z',    F::NO_PERSIST], // initialized in Session::__construct()
        self::VERSION_COMMENT                           => [S::GLOBAL,  false, T::CHAR,     '',         F::NO_PERSIST],
        self::VERSION_COMPILE_MACHINE                   => [S::GLOBAL,  false, T::CHAR,     '',         F::NO_PERSIST],
        self::VERSION_COMPILE_OS                        => [S::GLOBAL,  false, T::CHAR,     '',         F::NO_PERSIST],
        self::VERSION_COMPILE_ZLIB                      => [S::GLOBAL,  false, T::CHAR,     '',         F::NO_PERSIST],
        self::WAIT_TIMEOUT                              => [null,       true,  T::UNSIGNED, 28800,      F::CLAMP, 1, 31536000],
        self::WARNING_COUNT                             => [S::SESSION, false, T::UNSIGNED, 0],
        self::WINDOWING_USE_HIGH_PRECISION              => [null,       true,  T::BOOL,     true,       F::SET_VAR],
        self::XA_DETACH_ON_PREPARE                      => [null,       true,  T::BOOL,     true],

        // https://dev.mysql.com/doc/refman/8.0/en/audit-log-reference.html
        self::AUDIT_LOG_BUFFER_SIZE                     => [S::GLOBAL,  false, T::UNSIGNED, 1048576,    F::NONE, 4096, MAX, 4096],
        self::AUDIT_LOG_COMPRESSION                     => [S::GLOBAL,  false, T::ENUM,     'NONE',     F::NONE, ['NONE', 'GZIP']],
        self::AUDIT_LOG_CONNECTION_POLICY               => [S::GLOBAL,  false, T::ENUM,     'ALL',      F::NONE, ['ALL', 'ERRORS', 'NONE']],
        self::AUDIT_LOG_CURRENT_SESSION                 => [null,       false, T::BOOL,     true],
        self::AUDIT_LOG_DATABASE                        => [S::GLOBAL,  false, T::CHAR,     'mysql'], // since 8.0.33
        self::AUDIT_LOG_DISABLE                         => [S::GLOBAL,  true,  T::BOOL,     false], // since 8.0.28
        self::AUDIT_LOG_ENCRYPTION                      => [S::GLOBAL,  false, T::ENUM,     'NONE',     F::NONE, ['NONE', 'AES']],
        self::AUDIT_LOG_EXCLUDE_ACCOUNTS                => [S::GLOBAL,  true,  T::CHAR,     null],
        self::AUDIT_LOG_FILE                            => [S::GLOBAL,  false, T::CHAR,     'audit.log'],
        self::AUDIT_LOG_FILTER_ID                       => [null,       false, T::UNSIGNED, 1,          F::NONE, 0, I::UINT32_MAX],
        self::AUDIT_LOG_FLUSH                           => [S::GLOBAL,  true,  T::BOOL,     false],
        self::AUDIT_LOG_FORMAT                          => [S::GLOBAL,  false, T::ENUM,     'NEW',      F::NONE, ['OLD', 'NEW', 'JSON']],
        self::AUDIT_LOG_FORMAT_UNIX_TIMESTAMP           => [S::GLOBAL,  true,  T::BOOL,     false], // since 8.0.26
        self::AUDIT_LOG_INCLUDE_ACCOUNTS                => [S::GLOBAL,  true,  T::CHAR,     null],
        self::AUDIT_LOG_MAX_SIZE                        => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, MAX], // since 8.0.26
        self::AUDIT_LOG_PASSWORD_HISTORY_KEEP_DAYS      => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, I::UINT32_MAX], // since 8.0.17
        self::AUDIT_LOG_POLICY                          => [S::GLOBAL,  false, T::ENUM,     'ALL',      F::NONE, ['ALL', 'LOGINS', 'QUERIES', 'NONE']],
        self::AUDIT_LOG_PRUNE_SECONDS                   => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, MAX], // since 8.0.24
        self::AUDIT_LOG_READ_BUFFER_SIZE                => [S::GLOBAL,  true,  T::UNSIGNED, 32768,      F::NONE, 32768, 4194304],
        self::AUDIT_LOG_ROTATE_ON_SIZE                  => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, MAX, 4096],
        self::AUDIT_LOG_STATEMENT_POLICY                => [S::GLOBAL,  true,  T::ENUM,     'ALL',      F::NONE, ['ALL', 'ERRORS', 'NONE']],
        self::AUDIT_LOG_STRATEGY                        => [S::GLOBAL,  false, T::ENUM,     'ASYNCHRONOUS', F::NONE, ['ASYNCHRONOUS', 'PERFORMANCE', 'SEMISYNCHRONOUS', 'SYNCHRONOUS']],

        // https://dev.mysql.com/doc/refman/8.0/en/replication-options-binary-log.html
        self::BINLOG_CACHE_SIZE                         => [S::GLOBAL,  true,  T::UNSIGNED, 32768,      F::CLAMP, 4096, MAX, 4096],
        self::BINLOG_CHECKSUM                           => [S::GLOBAL,  true,  T::ENUM,     'CRC32',    F::NONE, ['NONE', 'CRC32']],
        self::BINLOG_DIRECT_NON_TRANSACTIONAL_UPDATES   => [null,       true,  T::BOOL,     false],
        self::BINLOG_ENCRYPTION                         => [S::GLOBAL,  true,  T::BOOL,     false],
        self::BINLOG_ERROR_ACTION                       => [S::GLOBAL,  true,  T::ENUM,     'ABORT_SERVER', F::NONE, ['IGNORE_ERROR', 'ABORT_SERVER']],
        self::BINLOG_EXPIRE_LOGS_SECONDS                => [S::GLOBAL,  true,  T::UNSIGNED, 2592000,    F::CLAMP, 0, 4294967295],
        self::BINLOG_EXPIRE_LOGS_AUTO_PURGE             => [S::GLOBAL,  true,  T::BOOL,     true],
        self::BINLOG_FORMAT                             => [null,       true,  T::ENUM,     'ROW',      F::NONE, ['MIXED', 'STATEMENT', 'ROW']],
        self::BINLOG_GROUP_COMMIT_SYNC_DELAY            => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, 1000000],
        self::BINLOG_GROUP_COMMIT_SYNC_NO_DELAY_COUNT   => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, 1000000],
        self::BINLOG_MAX_FLUSH_QUEUE_TIME               => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, 1000000],
        self::BINLOG_ORDER_COMMITS                      => [S::GLOBAL,  true,  T::BOOL,     false],
        self::BINLOG_ROTATE_ENCRYPTION_MASTER_KEY_AT_STARTUP
                                                        => [S::GLOBAL,  false, T::BOOL,     false],
        self::BINLOG_ROW_EVENT_MAX_SIZE                 => [S::GLOBAL,  false, T::UNSIGNED, 8192,       F::NONE, 256, MAX, 256],
        self::BINLOG_ROW_IMAGE                          => [null,       true,  T::ENUM,     'FULL',     F::NONE, ['FULL', 'MINIMAL', 'NOBLOB']],
        self::BINLOG_ROW_METADATA                       => [S::GLOBAL,  true,  T::ENUM,     'MINIMAL',  F::NONE, ['FULL', 'MINIMAL']],
        self::BINLOG_ROW_VALUE_OPTIONS                  => [null,       true,  T::SET,      '',         F::NONE, ['PARTIAL_JSON']],
        self::BINLOG_ROWS_QUERY_LOG_EVENTS              => [null,       true,  T::BOOL,     false],
        self::BINLOG_STMT_CACHE_SIZE                    => [S::GLOBAL,  true,  T::UNSIGNED, 32768,      F::NONE, 4096, MAX, 4096],
        self::BINLOG_TRANSACTION_COMPRESSION            => [null,       true,  T::BOOL,     false],
        self::BINLOG_TRANSACTION_COMPRESSION_LEVEL_ZSTD => [null,       true,  T::UNSIGNED, 3,          F::CLAMP, 1, 22],
        self::BINLOG_TRANSACTION_DEPENDENCY_TRACKING    => [S::GLOBAL,  true,  T::ENUM,     'COMMIT_ORDER', F::NONE, ['COMMIT_ORDER', 'WRITESET', 'WRITESET_SESSION']],
        self::BINLOG_TRANSACTION_DEPENDENCY_HISTORY_SIZE
                                                        => [S::GLOBAL,  true,  T::UNSIGNED, 25000,      F::CLAMP, 1, 1000000],
        self::BINLOGGING_IMPOSSIBLE_MODE                => [null,       true,  T::ENUM,     'IGNORE_ERROR', F::NONE, ['IGNORE_ERROR', 'ABORT_SERVER']],
        self::EXPIRE_LOGS_DAYS                          => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, 99],
        self::LOG_BIN                                   => [S::GLOBAL,  false, T::BOOL,     false,      F::NO_PERSIST],
        self::LOG_BIN_BASENAME                          => [S::GLOBAL,  false, T::CHAR,     null,       F::NO_PERSIST], // file
        self::LOG_BIN_INDEX                             => [S::GLOBAL,  false, T::BOOL,     null,       F::NO_PERSIST],
        self::LOG_BIN_TRUST_FUNCTION_CREATORS           => [S::GLOBAL,  true,  T::BOOL,     false],
        self::LOG_BIN_USE_V1_ROW_EVENTS                 => [S::GLOBAL,  true,  T::BOOL,     false,      F::NO_PERSIST],
        self::LOG_REPLICA_UPDATES                       => [S::GLOBAL,  false, T::BOOL,     false],
        self::LOG_SLAVE_UPDATES                         => [S::GLOBAL,  false, T::BOOL,     true],
        self::LOG_STATEMENTS_UNSAFE_FOR_BINLOG          => [S::GLOBAL,  true,  T::BOOL,     true],
        self::MASTER_VERIFY_CHECKSUM                    => [S::GLOBAL,  true,  T::BOOL,     false],
        self::MAX_BINLOG_CACHE_SIZE                     => [S::GLOBAL,  true,  T::UNSIGNED, MAX,        F::CLAMP, 4096, MAX, 4096],
        self::MAX_BINLOG_SIZE                           => [S::GLOBAL,  true,  T::UNSIGNED, 1073741824, F::CLAMP, 4096, 1073741824, 4096],
        self::MAX_BINLOG_STMT_CACHE_SIZE                => [S::GLOBAL,  true,  T::UNSIGNED, MAX,        F::CLAMP, 4096, MAX, 4096],
        self::ORIGINAL_COMMIT_TIMESTAMP                 => [S::SESSION, true,  T::NUMERIC,  null],
        self::SOURCE_VERIFY_CHECKSUM                    => [S::GLOBAL,  true,  T::BOOL,     false],
        self::SQL_LOG_BIN                               => [S::SESSION, true,  T::BOOL,     true],
        self::SYNC_BINLOG                               => [S::GLOBAL,  true,  T::UNSIGNED, 1,          F::CLAMP, 0, 4294967295],
        self::TRANSACTION_WRITE_SET_EXTRACTION          => [null,       true,  T::ENUM,     'XXHASH64', F::NONE, ['OFF', 'MURMUR32', 'XXHASH64']],

        // https://dev.mysql.com/doc/refman/8.0/en/clone-plugin-options-variables.html
        self::CLONE_AUTOTUNE_CONCURRENCY                => [S::GLOBAL,  true,  T::BOOL,     true],
        self::CLONE_BUFFER_SIZE                         => [S::GLOBAL,  true,  T::UNSIGNED, 4194304,    F::NONE, 1048576, 268435456],
        self::CLONE_BLOCK_DDL                           => [S::GLOBAL,  true,  T::BOOL,     false],
        self::CLONE_DELAY_AFTER_DATA_DROP               => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, 3600],
        self::CLONE_DDL_TIMEOUT                         => [S::GLOBAL,  true,  T::UNSIGNED, 300,        F::NONE, 0, 2592000],
        self::CLONE_DONOR_TIMEOUT_AFTER_NETWORK_FAILURE => [S::GLOBAL,  true,  T::UNSIGNED, 5,          F::NONE, 0, 30],
        self::CLONE_ENABLE_COMPRESSION                  => [S::GLOBAL,  true,  T::BOOL,     false],
        self::CLONE_MAX_CONCURRENCY                     => [S::GLOBAL,  true,  T::UNSIGNED, 16,         F::NONE, 1, 128],
        self::CLONE_MAX_DATA_BANDWIDTH                  => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, 1048576],
        self::CLONE_MAX_NETWORK_BANDWIDTH               => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, 1048576],
        self::CLONE_SSL_CA                              => [S::GLOBAL,  true,  T::CHAR,     ''], // file
        self::CLONE_SSL_CERT                            => [S::GLOBAL,  true,  T::CHAR,     ''], // file
        self::CLONE_SSL_KEY                             => [S::GLOBAL,  true,  T::CHAR,     ''],
        self::CLONE_VALID_DONOR_LIST                    => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE],

        // https://dev.mysql.com/doc/refman/8.0/en/connection-control-variables.html
        self::CONNECTION_CONTROL_FAILED_CONNECTIONS_THRESHOLD   => [S::GLOBAL,  true,  T::UNSIGNED, 3,          F::NULLABLE, 0, I::INT32_MAX],
        self::CONNECTION_CONTROL_MAX_CONNECTION_DELAY           => [S::GLOBAL,  true,  T::UNSIGNED, 2147483647, F::NONE, 1000, I::INT32_MAX],
        self::CONNECTION_CONTROL_MIN_CONNECTION_DELAY           => [S::GLOBAL,  true,  T::UNSIGNED, 1000,       F::NONE, 1000, I::INT32_MAX],

        // https://dev.mysql.com/doc/refman/8.0/en/replication-options-gtids.html
        self::BINLOG_GTID_SIMPLE_RECOVERY       => [S::GLOBAL,  false, T::BOOL,     true],
        self::ENFORCE_GTID_CONSISTENCY          => [S::GLOBAL,  true,  T::ENUM,     'OFF',  F::NONE, ['OFF', 'ON', 'WARN']],
        self::GTID_EXECUTED                     => [S::GLOBAL,  false, T::CHAR,     '',     F::NO_PERSIST],
        self::GTID_EXECUTED_COMPRESSION_PERIOD  => [S::GLOBAL,  true,  T::UNSIGNED, 0,      F::CLAMP, 0, 4294967295],
        self::GTID_MODE                         => [S::GLOBAL,  true,  T::ENUM,     'OFF',  F::NONE, ['OFF', 'OFF_PERMISSIVE', 'ON_PERMISSIVE', 'ON']],
        self::GTID_NEXT                         => [S::SESSION, true,  T::CHAR,     'AUTOMATIC', F::NONE], // 'UUID:NUMBER', 'AUTOMATIC', 'ANONYMOUS'
        self::GTID_OWNED                        => [null,       false, T::CHAR,     '',     F::NO_PERSIST],
        self::GTID_PURGED                       => [S::GLOBAL,  true,  T::CHAR,     '',     F::NO_DEFAULT],
        self::SIMPLIFIED_BINLOG_GTID_RECOVERY   => [S::GLOBAL,  false, T::BOOL,     true],

        // https://dev.mysql.com/doc/refman/8.0/en/group-replication-options.html
        self::GROUP_REPLICATION_ADVERTISE_RECOVERY_ENDPOINTS        => [S::GLOBAL,  true,  T::CHAR,     'DEFAULT'],
        self::GROUP_REPLICATION_ALLOW_LOCAL_DISJOINT_GTIDS_JOIN     => [S::GLOBAL,  true,  T::BOOL,     false],
        self::GROUP_REPLICATION_ALLOW_LOCAL_LOWER_VERSION_JOIN      => [S::GLOBAL,  true,  T::BOOL,     false],
        self::GROUP_REPLICATION_AUTO_INCREMENT_INCREMENT            => [S::GLOBAL,  true,  T::UNSIGNED, 7,          F::NO_DEFAULT, 1, 65535],
        self::GROUP_REPLICATION_AUTOREJOIN_TRIES                    => [S::GLOBAL,  true,  T::UNSIGNED, 3,          F::NONE, 0, 2016],
        self::GROUP_REPLICATION_BOOTSTRAP_GROUP                     => [S::GLOBAL,  true,  T::BOOL,     false],
        self::GROUP_REPLICATION_CLONE_THRESHOLD                     => [S::GLOBAL,  true,  T::UNSIGNED, MAX,        F::NONE, 1, MAX],
        self::GROUP_REPLICATION_COMMUNICATION_DEBUG_OPTIONS         => [S::GLOBAL,  true,  T::SET,      'GCS_DEBUG_NONE', F::NONE, ['GCS_DEBUG_NONE', 'GCS_DEBUG_BASIC', 'GCS_DEBUG_TRACE', 'XCOM_DEBUG_BASIC', 'XCOM_DEBUG_TRACE', 'GCS_DEBUG_ALL']],
        self::GROUP_REPLICATION_COMMUNICATION_MAX_MESSAGE_SIZE      => [S::GLOBAL,  true,  T::UNSIGNED, 10485760,   F::NONE, 0, 1073741824],
        self::GROUP_REPLICATION_COMMUNICATION_STACK                 => [S::GLOBAL,  true,  T::ENUM,     'XCOM',     F::NONE, ['XCOM', 'MYSQL']],
        self::GROUP_REPLICATION_COMPONENTS_STOP_TIMEOUT             => [S::GLOBAL,  true,  T::UNSIGNED, 300,        F::CLAMP, 2, 31536000],
        self::GROUP_REPLICATION_COMPRESSION_THRESHOLD               => [S::GLOBAL,  true,  T::UNSIGNED, 1000000,    F::NO_DEFAULT, 0, I::UINT32_MAX],
        self::GROUP_REPLICATION_CONSISTENCY                         => [null,       true,  T::ENUM,     'EVENTUAL', F::NONE, ['EVENTUAL', 'BEFORE_ON_PRIMARY_FAILOVER', 'BEFORE', 'AFTER', 'BEFORE_AND_AFTER']],
        self::GROUP_REPLICATION_ENFORCE_UPDATE_EVERYWHERE_CHECKS    => [S::GLOBAL,  true,  T::BOOL,     false,      F::NO_DEFAULT],
        self::GROUP_REPLICATION_EXIT_STATE_ACTION                   => [S::GLOBAL,  true,  T::ENUM,     'READ_ONLY', F::NONE, ['ABORT_SERVER', 'OFFLINE_MODE', 'READ_ONLY']],
        self::GROUP_REPLICATION_FLOW_CONTROL_APPLIER_THRESHOLD      => [S::GLOBAL,  true,  T::UNSIGNED, 25000,      F::CLAMP, 0, I::INT32_MAX],
        self::GROUP_REPLICATION_FLOW_CONTROL_CERTIFIER_THRESHOLD    => [S::GLOBAL,  true,  T::UNSIGNED, 25000,      F::CLAMP, 0, I::INT32_MAX],
        self::GROUP_REPLICATION_FLOW_CONTROL_HOLD_PERCENT           => [S::GLOBAL,  true,  T::UNSIGNED, 10,         F::CLAMP, 0, 100],
        self::GROUP_REPLICATION_FLOW_CONTROL_MAX_QUOTA              => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, I::INT32_MAX],
        self::GROUP_REPLICATION_FLOW_CONTROL_MEMBER_QUOTA_PERCENT   => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, 100],
        self::GROUP_REPLICATION_FLOW_CONTROL_MIN_QUOTA              => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, I::INT32_MAX],
        self::GROUP_REPLICATION_FLOW_CONTROL_MIN_RECOVERY_QUOTA     => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, I::INT32_MAX],
        self::GROUP_REPLICATION_FLOW_CONTROL_MODE                   => [S::GLOBAL,  true,  T::ENUM,     'QUOTA',    F::NONE, ['DISABLED', 'QUOTA']],
        self::GROUP_REPLICATION_FLOW_CONTROL_PERIOD                 => [S::GLOBAL,  true,  T::UNSIGNED, 1,          F::CLAMP, 1, 60],
        self::GROUP_REPLICATION_FLOW_CONTROL_RELEASE_PERCENT        => [S::GLOBAL,  true,  T::UNSIGNED, 50,         F::CLAMP, 0, 1000],
        self::GROUP_REPLICATION_FORCE_MEMBERS                       => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE | F::NO_PERSIST],
        self::GROUP_REPLICATION_GROUP_NAME                          => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE | F::NO_DEFAULT],
        self::GROUP_REPLICATION_GROUP_SEEDS                         => [S::GLOBAL,  true,  T::CHAR,     'localhost::33061', F::NULLABLE],
        self::GROUP_REPLICATION_GTID_ASSIGNMENT_BLOCK_SIZE          => [S::GLOBAL,  true,  T::UNSIGNED, 1000000,    F::NO_DEFAULT, 1, MAX],
        self::GROUP_REPLICATION_IP_ALLOWLIST                        => [S::GLOBAL,  true,  T::CHAR,     'AUTOMATIC', F::NO_DEFAULT],
        self::GROUP_REPLICATION_IP_WHITELIST                        => [S::GLOBAL,  true,  T::CHAR,     'AUTOMATIC'],
        self::GROUP_REPLICATION_LOCAL_ADDRESS                       => [S::GLOBAL,  true,  T::CHAR,     'localhost::33061', F::NULLABLE],
        self::GROUP_REPLICATION_MEMBER_EXPEL_TIMEOUT                => [S::GLOBAL,  true,  T::UNSIGNED, 5,          F::NONE, 0, 3600],
        self::GROUP_REPLICATION_MEMBER_WEIGHT                       => [S::GLOBAL,  true,  T::UNSIGNED, 50,         F::CLAMP, 0, 100],
        self::GROUP_REPLICATION_MESSAGE_CACHE_SIZE                  => [S::GLOBAL,  true,  T::UNSIGNED, 1073741824, F::NONE, 134217728, MAX],
        self::GROUP_REPLICATION_PAXOS_SINGLE_LEADER                 => [S::GLOBAL,  true,  T::BOOL,     false],
        self::GROUP_REPLICATION_POLL_SPIN_LOOPS                     => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, MAX],
        self::GROUP_REPLICATION_RECOVERY_COMPLETE_AT                => [S::GLOBAL,  true,  T::ENUM,     'TRANSACTIONS_APPLIED', F::NONE, ['TRANSACTIONS_CERTIFIED', 'TRANSACTIONS_APPLIED']],
        self::GROUP_REPLICATION_RECOVERY_COMPRESSION_ALGORITHMS     => [S::GLOBAL,  true,  T::ENUM,     'UNCOMPRESSED', F::NONE, ['ZLIB', 'ZSTD', 'UNCOMPRESSED']],
        self::GROUP_REPLICATION_RECOVERY_GET_PUBLIC_KEY             => [S::GLOBAL,  true,  T::BOOL,     false],
        self::GROUP_REPLICATION_RECOVERY_PUBLIC_KEY_PATH            => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE], // file
        self::GROUP_REPLICATION_RECOVERY_RECONNECT_INTERVAL         => [S::GLOBAL,  true,  T::UNSIGNED, 60,         F::CLAMP, 0, Seconds::COMMON_YEAR],
        self::GROUP_REPLICATION_RECOVERY_RETRY_COUNT                => [S::GLOBAL,  true,  T::UNSIGNED, 10,         F::CLAMP, 0, Seconds::COMMON_YEAR],
        self::GROUP_REPLICATION_RECOVERY_SSL_CA                     => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE], // file
        self::GROUP_REPLICATION_RECOVERY_SSL_CAPATH                 => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE], // dir
        self::GROUP_REPLICATION_RECOVERY_SSL_CERT                   => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE], // file
        self::GROUP_REPLICATION_RECOVERY_SSL_CIPHER                 => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE],
        self::GROUP_REPLICATION_RECOVERY_SSL_CRL                    => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE], // file
        self::GROUP_REPLICATION_RECOVERY_SSL_CRLPATH                => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE], // dir
        self::GROUP_REPLICATION_RECOVERY_SSL_KEY                    => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE],
        self::GROUP_REPLICATION_RECOVERY_SSL_VERIFY_SERVER_CERT     => [S::GLOBAL,  true,  T::BOOL,     false],
        self::GROUP_REPLICATION_RECOVERY_TLS_CIPHERSUITES           => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE],
        self::GROUP_REPLICATION_RECOVERY_TLS_VERSION                => [S::GLOBAL,  true,  T::CHAR,     'TLSv1.2,TLSv1.3'],
        self::GROUP_REPLICATION_RECOVERY_USE_SSL                    => [S::GLOBAL,  true,  T::BOOL,     true],
        self::GROUP_REPLICATION_RECOVERY_ZSTD_COMPRESSION_LEVEL     => [S::GLOBAL,  true,  T::UNSIGNED, 3,          F::NONE, 1, 22],
        self::GROUP_REPLICATION_SINGLE_PRIMARY_MODE                 => [S::GLOBAL,  true,  T::BOOL,     true,       F::CLAMP | F::NO_DEFAULT],
        self::GROUP_REPLICATION_SSL_MODE                            => [S::GLOBAL,  true,  T::ENUM,     'DISABLED', F::NONE, ['DISABLED', 'REQUIRED', 'VERIFY_CA', 'VERIFY_IDENTITY']],
        self::GROUP_REPLICATION_START_ON_BOOT                       => [S::GLOBAL,  true,  T::BOOL,     true],
        self::GROUP_REPLICATION_TLS_SOURCE                          => [S::GLOBAL,  true,  T::ENUM,     'MYSQL_MAIN', F::NONE, ['MYSQL_MAIN', 'MYSQL_ADMIN']],
        self::GROUP_REPLICATION_TRANSACTION_SIZE_LIMIT              => [S::GLOBAL,  true,  T::UNSIGNED, 150000000,  F::CLAMP, 0, I::INT32_MAX],
        self::GROUP_REPLICATION_UNREACHABLE_MAJORITY_TIMEOUT        => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, Seconds::COMMON_YEAR],
        self::GROUP_REPLICATION_VIEW_CHANGE_UUID                    => [S::GLOBAL,  true,  T::CHAR,     'AUTOMATIC'],

        // https://dev.mysql.com/doc/refman/8.0/en/innodb-parameters.html
        self::DAEMON_MEMCACHED_ENABLE_BINLOG            => [S::GLOBAL,  false, T::BOOL,     false],
        self::DAEMON_MEMCACHED_ENGINE_LIB_NAME          => [S::GLOBAL,  false, T::CHAR,     'innodb_engine.so'], // file
        self::DAEMON_MEMCACHED_ENGINE_LIB_PATH          => [S::GLOBAL,  false, T::CHAR,     null], // dir
        self::DAEMON_MEMCACHED_OPTION                   => [S::GLOBAL,  false, T::CHAR,     ''],
        self::DAEMON_MEMCACHED_R_BATCH_SIZE             => [S::GLOBAL,  false, T::UNSIGNED, 1,          F::NONE, 1, 1073741824],
        self::DAEMON_MEMCACHED_W_BATCH_SIZE             => [S::GLOBAL,  false, T::UNSIGNED, 1,          F::NONE, 1, 1048576],
        self::INNODB_ADAPTIVE_FLUSHING                  => [S::GLOBAL,  true,  T::BOOL,     true],
        self::INNODB_ADAPTIVE_FLUSHING_LWM              => [S::GLOBAL,  true,  T::UNSIGNED, 10,         F::CLAMP, 0, 70],
        self::INNODB_ADAPTIVE_HASH_INDEX                => [S::GLOBAL,  true,  T::BOOL,     true],
        self::INNODB_ADAPTIVE_HASH_INDEX_PARTS          => [S::GLOBAL,  false, T::UNSIGNED, 8,          F::NONE, 1, 512],
        self::INNODB_ADAPTIVE_MAX_SLEEP_DELAY           => [S::GLOBAL,  true,  T::UNSIGNED, 150000,     F::CLAMP, 0, 1000000],
        self::INNODB_API_BK_COMMIT_INTERVAL             => [S::GLOBAL,  true,  T::UNSIGNED, 5,          F::CLAMP, 1, 1073741824],
        self::INNODB_API_DISABLE_ROWLOCK                => [S::GLOBAL,  false, T::BOOL,     false],
        self::INNODB_API_ENABLE_BINLOG                  => [S::GLOBAL,  false, T::BOOL,     false],
        self::INNODB_API_ENABLE_MDL                     => [S::GLOBAL,  false, T::BOOL,     false],
        self::INNODB_API_TRX_LEVEL                      => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, 3],
        self::INNODB_AUTOEXTEND_INCREMENT               => [S::GLOBAL,  true,  T::UNSIGNED, 64,         F::CLAMP, 1, 1000],
        self::INNODB_AUTOINC_LOCK_MODE                  => [S::GLOBAL,  false, T::UNSIGNED, 2,          F::NONE, 0, 2],
        self::INNODB_BACKGROUND_DROP_LIST_EMPTY         => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_BUF_FLUSH_LIST_NOW                 => [S::GLOBAL,  true,  T::BOOL,     false], // undocumented
        self::INNODB_BUFFER_POOL_CHUNK_SIZE             => [S::GLOBAL,  false, T::UNSIGNED, 134217728,  F::NONE, 1048576, MAX],
        self::INNODB_BUFFER_POOL_DEBUG                  => [S::GLOBAL,  false, T::BOOL,     false],
        self::INNODB_BUFFER_POOL_DUMP_AT_SHUTDOWN       => [S::GLOBAL,  true,  T::BOOL,     true],
        self::INNODB_BUFFER_POOL_DUMP_NOW               => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_BUFFER_POOL_DUMP_PCT               => [S::GLOBAL,  true,  T::UNSIGNED, 25,         F::CLAMP, 1, 100],
        self::INNODB_BUFFER_POOL_EVICT                  => [S::GLOBAL,  true,  T::CHAR,     null],
        self::INNODB_BUFFER_POOL_FILENAME               => [S::GLOBAL,  true,  T::CHAR,     'ib_buffer_pool'], // file
        self::INNODB_BUFFER_POOL_IN_CORE_FILE           => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_BUFFER_POOL_INSTANCES              => [S::GLOBAL,  false, T::UNSIGNED, 8,          F::NONE, 1, 64],
        self::INNODB_BUFFER_POOL_LOAD_ABORT             => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_BUFFER_POOL_LOAD_AT_STARTUP        => [S::GLOBAL,  false, T::BOOL,     true],
        self::INNODB_BUFFER_POOL_LOAD_NOW               => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_BUFFER_POOL_SIZE                   => [S::GLOBAL,  true,  T::UNSIGNED, 134217728,  F::CLAMP, 5242880, MAX],
        self::INNODB_CHANGE_BUFFER_MAX_SIZE             => [S::GLOBAL,  true,  T::UNSIGNED, 25,         F::CLAMP, 0, 50],
        self::INNODB_CHANGE_BUFFERING                   => [S::GLOBAL,  true,  T::ENUM,     'ALL',      F::NONE, ['NONE', 'INSERTS', 'DELETES', 'CHANGES', 'PURGES', 'ALL', 0, 1, 2, 3, 4, 5]],
        self::INNODB_CHANGE_BUFFERING_DEBUG             => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, 2],
        self::INNODB_CHECKPOINT_DISABLED                => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_CHECKSUM_ALGORITHM                 => [S::GLOBAL,  true,  T::ENUM,     'CRC32',    F::NONE, ['CRC32', 'STRICT_CRC32', 'INNODB', 'STRICT_INNODB', 'NONE', 'STRICT_NONE']],
        self::INNODB_CMP_PER_INDEX_ENABLED              => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_COMMIT_CONCURRENCY                 => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, 1000], // cannot dynamically change from 0 to non-zero or vice versa
        self::INNODB_COMPRESS_DEBUG                     => [S::GLOBAL,  true,  T::ENUM,     'NONE',     F::NONE, ['NONE', 'ZLIB', 'LZ4', 'LZ4HC']],
        self::INNODB_COMPRESSION_FAILURE_THRESHOLD_PCT  => [S::GLOBAL,  true,  T::UNSIGNED, 5,          F::CLAMP, 0, 100],
        self::INNODB_COMPRESSION_LEVEL                  => [S::GLOBAL,  true,  T::UNSIGNED, 6,          F::CLAMP, 0, 9],
        self::INNODB_COMPRESSION_PAD_PCT_MAX            => [S::GLOBAL,  true,  T::UNSIGNED, 50,         F::CLAMP, 0, 75],
        self::INNODB_CONCURRENCY_TICKETS                => [S::GLOBAL,  true,  T::UNSIGNED, 5000,       F::CLAMP, 1, I::UINT32_MAX],
        self::INNODB_DATA_FILE_PATH                     => [S::GLOBAL,  false, T::CHAR,     'ibdata1:12M:autoextend', F::NO_PERSIST],
        self::INNODB_DATA_HOME_DIR                      => [S::GLOBAL,  false, T::CHAR,     null], // dir
        self::INNODB_DDL_BUFFER_SIZE                    => [null,       true,  T::UNSIGNED, 1048576,    F::CLAMP, 65536, I::UINT32_MAX],
        self::INNODB_DDL_LOG_CRASH_RESET_DEBUG          => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_DDL_THREADS                        => [null,       true,  T::UNSIGNED, 4,          F::NONE, 1, 64],
        self::INNODB_DEADLOCK_DETECT                    => [S::GLOBAL,  true,  T::BOOL,     true],
        self::INNODB_DEDICATED_SERVER                   => [S::GLOBAL,  false, T::BOOL,     false,      F::NO_PERSIST],
        self::INNODB_DEFAULT_ROW_FORMAT                 => [S::GLOBAL,  true,  T::ENUM,     'DYNAMIC',  F::NONE, ['REDUNDANT', 'COMPACT', 'DYNAMIC']],
        self::INNODB_DIRECTORIES                        => [S::GLOBAL,  false, T::CHAR,     null], // dir
        self::INNODB_DISABLE_SORT_FILE_CACHE            => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_DOUBLEWRITE                        => [S::GLOBAL,  true,  T::ENUM,     'ON',       F::NO_PERSIST, ['ON', 'OFF', 'DETECT_AND_RECOVER', 'DETECT_ONLY']],
        self::INNODB_DOUBLEWRITE_BATCH_SIZE             => [S::GLOBAL,  false, T::UNSIGNED, 0,          F::NONE, 0, 256],
        self::INNODB_DOUBLEWRITE_DIR                    => [S::GLOBAL,  false, T::CHAR,     null], // dir
        self::INNODB_DOUBLEWRITE_FILES                  => [S::GLOBAL,  false, T::UNSIGNED, 16,         F::NONE, 2, 256],
        self::INNODB_DOUBLEWRITE_PAGES                  => [S::GLOBAL,  false, T::UNSIGNED, 4,          F::NONE, 4, 512],
        self::INNODB_EXTEND_AND_INITIALIZE              => [S::GLOBAL,  true,  T::BOOL,     true],
        self::INNODB_FAST_SHUTDOWN                      => [S::GLOBAL,  true,  T::UNSIGNED, 1,          F::CLAMP, 0, 2],
        self::INNODB_FIL_MAKE_PAGE_DIRTY_DEBUG          => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, I::UINT32_MAX],
        self::INNODB_FILE_PER_TABLE                     => [S::GLOBAL,  true,  T::BOOL,     true],
        self::INNODB_FILL_FACTOR                        => [S::GLOBAL,  true,  T::UNSIGNED, 100,        F::CLAMP, 10, 100],
        self::INNODB_FLUSH_LOG_AT_TIMEOUT               => [S::GLOBAL,  true,  T::UNSIGNED, 1,          F::CLAMP, 1, 2700],
        self::INNODB_FLUSH_LOG_AT_TRX_COMMIT            => [S::GLOBAL,  true,  T::UNSIGNED, 1,          F::CLAMP, 0, 2],
        self::INNODB_FLUSH_METHOD                       => [S::GLOBAL,  false, T::ENUM,     'FSYNC',    F::NONE, ['FSYNC', 'O_DSYNC', 'LITTLESYNC', 'NOSYNC', 'O_DIRECT', 'O_DIRECT_NO_FSYNC']],
        self::INNODB_FLUSH_NEIGHBORS                    => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, 2],
        self::INNODB_FLUSH_SYNC                         => [S::GLOBAL,  true,  T::BOOL,     true],
        self::INNODB_FLUSHING_AVG_LOOPS                 => [S::GLOBAL,  true,  T::UNSIGNED, 30,         F::CLAMP, 1, 1000],
        self::INNODB_FORCE_LOAD_CORRUPTED               => [S::GLOBAL,  false, T::BOOL,     false],
        self::INNODB_FORCE_RECOVERY                     => [S::GLOBAL,  false, T::UNSIGNED, 0,          F::NONE, 0, 6],
        self::INNODB_FORCE_RECOVERY_CRASH               => [S::GLOBAL,  false, T::BOOL,     false], // undocumented
        self::INNODB_FSYNC_THRESHOLD                    => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, MAX],
        self::INNODB_FT_AUX_TABLE                       => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE],
        self::INNODB_FT_CACHE_SIZE                      => [S::GLOBAL,  false, T::UNSIGNED, 8000000,    F::NONE, 160000, 80000000],
        self::INNODB_FT_ENABLE_DIAG_PRINT               => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_FT_ENABLE_STOPWORD                 => [null,       true,  T::BOOL,     true],
        self::INNODB_FT_MAX_TOKEN_SIZE                  => [S::GLOBAL,  false, T::UNSIGNED, 84,         F::NONE, 10, 84],
        self::INNODB_FT_MIN_TOKEN_SIZE                  => [S::GLOBAL,  false, T::UNSIGNED, 3,          F::NONE, 0, 16],
        self::INNODB_FT_NUM_WORD_OPTIMIZE               => [S::GLOBAL,  true,  T::UNSIGNED, 2000,       F::CLAMP, 1000, 10000],
        self::INNODB_FT_RESULT_CACHE_LIMIT              => [S::GLOBAL,  true,  T::UNSIGNED, 2000000000, F::CLAMP, 1000000, I::UINT32_MAX],
        self::INNODB_FT_SERVER_STOPWORD_TABLE           => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE],
        self::INNODB_FT_SORT_PLL_DEGREE                 => [S::GLOBAL,  false, T::UNSIGNED, 2,          F::NONE, 1, 32],
        self::INNODB_FT_TOTAL_CACHE_SIZE                => [S::GLOBAL,  false, T::UNSIGNED, 640000000,  F::NONE, 32000000, 1600000000],
        self::INNODB_FT_USER_STOPWORD_TABLE             => [null,       true,  T::CHAR,     null,       F::NULLABLE],
        self::INNODB_IDLE_FLUSH_PCT                     => [S::GLOBAL,  true,  T::UNSIGNED, 100,        F::CLAMP, 0, 100],
        self::INNODB_INTERPRETER                        => [null,       true,  T::CHAR,     ''], // undocumented, debug
        self::INNODB_IO_CAPACITY                        => [S::GLOBAL,  true,  T::UNSIGNED, 200,        F::CLAMP, 100, MAX],
        self::INNODB_IO_CAPACITY_MAX                    => [S::GLOBAL,  true,  T::UNSIGNED, 2000,       F::CLAMP, 100, I::UINT32_MAX],
        self::INNODB_LIMIT_OPTIMISTIC_INSERT_DEBUG      => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, I::UINT32_MAX],
        self::INNODB_LOCK_WAIT_TIMEOUT                  => [null,       true,  T::UNSIGNED, 50,         F::CLAMP, 1, 1073741824],
        self::INNODB_LOG_BUFFER_SIZE                    => [S::GLOBAL,  true,  T::UNSIGNED, 16777216,   F::CLAMP, 1048576, I::UINT32_MAX],
        self::INNODB_LOG_CHECKPOINT_FUZZY_NOW           => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_LOG_CHECKPOINT_NOW                 => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_LOG_CHECKSUMS                      => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_LOG_COMPRESSED_PAGES               => [S::GLOBAL,  true,  T::BOOL,     true],
        self::INNODB_LOG_FILE_SIZE                      => [S::GLOBAL,  false, T::UNSIGNED, 50331648,   F::NONE, 4194304, PowersOfTwo::_256G],
        self::INNODB_LOG_FILES_IN_GROUP                 => [S::GLOBAL,  false, T::UNSIGNED, 2,          F::NONE, 2, 100],
        self::INNODB_LOG_FLUSH_NOW                      => [S::GLOBAL,  true,  T::BOOL,     false], // undocumented
        self::INNODB_LOG_GROUP_HOME_DIR                 => [S::GLOBAL,  false, T::CHAR,     null], // dir
        self::INNODB_LOG_SPIN_CPU_ABS_LWM               => [S::GLOBAL,  true,  T::UNSIGNED, 80,         F::CLAMP, 0, I::UINT32_MAX],
        self::INNODB_LOG_SPIN_CPU_PCT_HWM               => [S::GLOBAL,  true,  T::UNSIGNED, 50,         F::CLAMP, 0, 100],
        self::INNODB_LOG_WAIT_FOR_FLUSH_SPIN_HWM        => [S::GLOBAL,  true,  T::UNSIGNED, 400,        F::CLAMP, 0, MAX],
        self::INNODB_LOG_WRITE_AHEAD_SIZE               => [S::GLOBAL,  true,  T::UNSIGNED, 8192,       F::CLAMP, 512, 16384, 512],
        self::INNODB_LOG_WRITER_THREADS                 => [S::GLOBAL,  true,  T::BOOL,     true],
        self::INNODB_LRU_SCAN_DEPTH                     => [S::GLOBAL,  true,  T::UNSIGNED, 1024,       F::CLAMP, 100, MAX],
        self::INNODB_MAX_DIRTY_PAGES_PCT                => [S::GLOBAL,  true,  T::UNSIGNED, 90,         F::CLAMP, 0, 99.99],
        self::INNODB_MAX_DIRTY_PAGES_PCT_LWM            => [S::GLOBAL,  true,  T::UNSIGNED, 10,         F::CLAMP, 0, 99.99],
        self::INNODB_MAX_PURGE_LAG                      => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, I::UINT32_MAX],
        self::INNODB_MAX_PURGE_LAG_DELAY                => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, 10000000],
        self::INNODB_MAX_UNDO_LOG_SIZE                  => [S::GLOBAL,  true,  T::UNSIGNED, 1073741824, F::NONE, 1048576, MAX],
        self::INNODB_MERGE_THRESHOLD_SET_ALL_DEBUG      => [S::GLOBAL,  true,  T::UNSIGNED, 50,         F::CLAMP, 1, 50],
        self::INNODB_MONITOR_DISABLE                    => [S::GLOBAL,  true,  T::CHAR,     null],
        self::INNODB_MONITOR_ENABLE                     => [S::GLOBAL,  true,  T::CHAR,     null],
        self::INNODB_MONITOR_RESET                      => [S::GLOBAL,  true,  T::CHAR,     null,       F::NONE], // ['COUNTER', 'MODULE', 'PATTERN', 'ALL', 'LATCH', 'MODULE_METADATA', 'METADATA_TABLE_HANDLES_OPENED', 'MODULE_DML', 'MODULE_SAMPLING', 'MODULE_DDL']], // 'latch'... is not documented
        self::INNODB_MONITOR_RESET_ALL                  => [S::GLOBAL,  true,  T::CHAR,     null,       F::NONE], // ['COUNTER', 'MODULE', 'PATTERN', 'ALL', 'LATCH', 'MODULE_METADATA', 'METADATA_TABLE_HANDLES_OPENED', 'MODULE_DML', 'MODULE_SAMPLING', 'MODULE_DDL']],
        self::INNODB_NUMA_INTERLEAVE                    => [S::GLOBAL,  false, T::BOOL,     false],
        self::INNODB_OLD_BLOCKS_PCT                     => [S::GLOBAL,  true,  T::UNSIGNED, 37,         F::CLAMP, 5, 95],
        self::INNODB_OLD_BLOCKS_TIME                    => [S::GLOBAL,  true,  T::UNSIGNED, 1000,       F::CLAMP, 0, I::UINT32_MAX],
        self::INNODB_ONLINE_ALTER_LOG_MAX_SIZE          => [S::GLOBAL,  true,  T::UNSIGNED, 134217728,  F::CLAMP, 65536, MAX],
        self::INNODB_OPEN_FILES                         => [S::GLOBAL,  true,  T::UNSIGNED, -1,         F::NONE, 10, I::INT32_MAX], // -1 = auto
        self::INNODB_OPTIMIZE_FULLTEXT_ONLY             => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_PAGE_CLEANERS                      => [S::GLOBAL,  false, T::UNSIGNED, 4,          F::NONE, 1, 64],
        self::INNODB_PAGE_HASH_LOCKS                    => [S::GLOBAL,  false, T::BOOL,     false], // undocumented, debug
        self::INNODB_PAGE_SIZE                          => [S::GLOBAL,  false, T::ENUM,     16384,      F::NONE, [4096, 8192, 16384, 32768, 65536]],
        self::INNODB_PARALLEL_READ_THREADS              => [null,       true,  T::UNSIGNED, 4,          F::CLAMP, 1, 256], // documented as session only
        self::INNODB_PRINT_ALL_DEADLOCKS                => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_PRINT_DDL_LOGS                     => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_PURGE_BATCH_SIZE                   => [S::GLOBAL,  true,  T::UNSIGNED, 300,        F::CLAMP, 1, 5000],
        self::INNODB_PURGE_RUN_NOW                      => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_PURGE_STOP_NOW                     => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_PURGE_THREADS                      => [S::GLOBAL,  false, T::UNSIGNED, 4,          F::NONE, 1, 32],
        self::INNODB_PURGE_RSEG_TRUNCATE_FREQUENCY      => [S::GLOBAL,  true,  T::UNSIGNED, 128,        F::CLAMP, 1, 128],
        self::INNODB_RANDOM_READ_AHEAD                  => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_READ_AHEAD_THRESHOLD               => [S::GLOBAL,  true,  T::UNSIGNED, 56,         F::CLAMP, 0, 64],
        self::INNODB_READ_IO_THREADS                    => [S::GLOBAL,  false, T::UNSIGNED, 4,          F::NONE, 1, 64],
        self::INNODB_READ_ONLY                          => [S::GLOBAL,  false, T::BOOL,     false],
        self::INNODB_REDO_LOG_ARCHIVE_DIRS              => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE],
        self::INNODB_REDO_LOG_CAPACITY                  => [S::GLOBAL,  true,  T::UNSIGNED, 104857600,  F::NONE, 8388608, 137438953472],
        self::INNODB_REDO_LOG_ENCRYPT                   => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_REPLICATION_DELAY                  => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, I::UINT32_MAX],
        self::INNODB_ROLLBACK_ON_TIMEOUT                => [S::GLOBAL,  false, T::BOOL,     false],
        self::INNODB_ROLLBACK_SEGMENTS                  => [S::GLOBAL,  true,  T::UNSIGNED, 128,        F::CLAMP, 1, 128],
        self::INNODB_SAVED_PAGE_NUMBER_DEBUG            => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, I::UINT32_MAX],
        self::INNODB_SEGMENT_RESERVE_FACTOR             => [S::GLOBAL,  true,  T::NUMERIC,  12.5,       F::CLAMP, 0.03, 40.0],
        self::INNODB_SEMAPHORE_WAIT_TIMEOUT_DEBUG       => [S::GLOBAL,  true,  T::UNSIGNED, 600,        F::CLAMP, 25, 600], // undocumented, debug
        self::INNODB_SORT_BUFFER_SIZE                   => [S::GLOBAL,  false, T::UNSIGNED, 1048576,    F::CLAMP, 65536, 67108864],
        self::INNODB_SPIN_WAIT_DELAY                    => [S::GLOBAL,  true,  T::UNSIGNED, 6,          F::CLAMP, 0, MAX],
        self::INNODB_SPIN_WAIT_PAUSE_MULTIPLIER         => [S::GLOBAL,  true,  T::UNSIGNED, 50,         F::CLAMP, 1, 100],
        self::INNODB_STATS_AUTO_RECALC                  => [S::GLOBAL,  true,  T::BOOL,     true],
        self::INNODB_STATS_INCLUDE_DELETE_MARKED        => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_STATS_METHOD                       => [S::GLOBAL,  true,  T::ENUM,     'NULLS_EQUAL', F::NONE, ['NULLS_EQUAL', 'NULLS_UNEQUAL', 'NULLS_IGNORED']],
        self::INNODB_STATS_ON_METADATA                  => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_STATS_PERSISTENT                   => [S::GLOBAL,  true,  T::BOOL,     true],
        self::INNODB_STATS_PERSISTENT_SAMPLE_PAGES      => [S::GLOBAL,  true,  T::UNSIGNED, 20,         F::CLAMP, 1, MAX],
        self::INNODB_STATS_TRANSIENT_SAMPLE_PAGES       => [S::GLOBAL,  true,  T::UNSIGNED, 8,          F::CLAMP, 1, MAX],
        self::INNODB_STATUS_OUTPUT                      => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_STATUS_OUTPUT_LOCKS                => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_STRICT_MODE                        => [null,       true,  T::BOOL,     true],
        self::INNODB_SYNC_ARRAY_SIZE                    => [S::GLOBAL,  false, T::UNSIGNED, 1,          F::NONE, 1, 1024],
        self::INNODB_SYNC_SPIN_LOOPS                    => [S::GLOBAL,  true,  T::UNSIGNED, 30,         F::CLAMP, 0, I::UINT32_MAX],
        self::INNODB_SYNC_DEBUG                         => [S::GLOBAL,  false, T::BOOL,     false],
        self::INNODB_TABLE_LOCKS                        => [null,       true,  T::BOOL,     true],
        self::INNODB_TEMP_DATA_FILE_PATH                => [S::GLOBAL,  false, T::CHAR,     'ibtmp1:12M:autoextend'],
        self::INNODB_TEMP_TABLESPACES_DIR               => [S::GLOBAL,  false, T::CHAR,     '#innodb_temp', F::NO_PERSIST], // dir
        self::INNODB_THREAD_CONCURRENCY                 => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, 1000],
        self::INNODB_THREAD_SLEEP_DELAY                 => [S::GLOBAL,  true,  T::UNSIGNED, 10000,      F::CLAMP, 0, 1000000],
        self::INNODB_TMPDIR                             => [null,       true,  T::CHAR,     null,       F::NULLABLE], // dir
        self::INNODB_TRX_PURGE_VIEW_UPDATE_ONLY_DEBUG   => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_TRX_RSEG_N_SLOTS_DEBUG             => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, 1024],
        self::INNODB_UNDO_DIRECTORY                     => [S::GLOBAL,  false, T::CHAR,     null], // dir
        self::INNODB_UNDO_LOG_ENCRYPT                   => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_UNDO_LOG_TRUNCATE                  => [S::GLOBAL,  true,  T::BOOL,     true],
        self::INNODB_UNDO_TABLESPACES                   => [S::GLOBAL,  true,  T::UNSIGNED, 2,          F::CLAMP, 2, 127], // deprecated
        self::INNODB_USE_FDATASYNC                      => [S::GLOBAL,  true,  T::BOOL,     false],
        self::INNODB_USE_NATIVE_AIO                     => [S::GLOBAL,  false, T::BOOL,     true],
        self::INNODB_VALIDATE_TABLESPACE_PATHS          => [S::GLOBAL,  false, T::BOOL,     true],
        self::INNODB_VERSION                            => [S::GLOBAL,  false, T::CHAR,     '8.0.30',   F::NO_PERSIST],
        self::INNODB_WRITE_IO_THREADS                   => [S::GLOBAL,  false, T::UNSIGNED, 4,          F::NONE, 1, 64],

        // https://dev.mysql.com/doc/refman/8.0/en/keyring-system-variables.html
        self::KEYRING_AWS_CMK_ID                    => [S::GLOBAL,  true,  T::CHAR, null],
        self::KEYRING_AWS_CONF_FILE                 => [S::GLOBAL,  false, T::CHAR, null], // file
        self::KEYRING_AWS_DATA_FILE                 => [S::GLOBAL,  false, T::CHAR, null], // file
        self::KEYRING_AWS_REGION                    => [S::GLOBAL,  true,  T::CHAR, 'us-east-1'], // enum
        self::KEYRING_ENCRYPTED_FILE_DATA           => [S::GLOBAL,  true,  T::CHAR, null], // file
        self::KEYRING_ENCRYPTED_FILE_PASSWORD       => [S::GLOBAL,  true,  T::CHAR, null],
        self::KEYRING_FILE_DATA                     => [S::GLOBAL,  true,  T::CHAR, null,   F::NON_EMPTY], // file
        self::KEYRING_HASHICORP_AUTH_PATH           => [S::GLOBAL,  true,  T::CHAR, '/v1/auth/approle/login', F::NO_PERSIST], // file
        self::KEYRING_HASHICORP_CA_PATH             => [S::GLOBAL,  true,  T::CHAR, null,   F::NO_PERSIST], // file
        self::KEYRING_HASHICORP_CACHING             => [S::GLOBAL,  true,  T::BOOL, false,  F::NO_PERSIST],
        self::KEYRING_HASHICORP_COMMIT_AUTH_PATH    => [S::GLOBAL,  false, T::CHAR, null,   F::NO_PERSIST],
        self::KEYRING_HASHICORP_COMMIT_CA_PATH      => [S::GLOBAL,  false, T::CHAR, null,   F::NO_PERSIST],
        self::KEYRING_HASHICORP_COMMIT_CACHING      => [S::GLOBAL,  false, T::CHAR, null,   F::NO_PERSIST],
        self::KEYRING_HASHICORP_COMMIT_ROLE_ID      => [S::GLOBAL,  false, T::CHAR, null,   F::NO_PERSIST],
        self::KEYRING_HASHICORP_COMMIT_SERVER_URL   => [S::GLOBAL,  false, T::CHAR, null,   F::NO_PERSIST],
        self::KEYRING_HASHICORP_COMMIT_STORE_PATH   => [S::GLOBAL,  false, T::CHAR, null,   F::NO_PERSIST],
        self::KEYRING_HASHICORP_ROLE_ID             => [S::GLOBAL,  true,  T::CHAR, ''],
        self::KEYRING_HASHICORP_SECRET_ID           => [S::GLOBAL,  true,  T::CHAR, ''],
        self::KEYRING_HASHICORP_SERVER_URL          => [S::GLOBAL,  true,  T::CHAR, 'https://127.0.0.1:8200'],
        self::KEYRING_HASHICORP_STORE_PATH          => [S::GLOBAL,  true,  T::CHAR, ''],
        self::KEYRING_OCI_CA_CERTIFICATE            => [S::GLOBAL,  false, T::CHAR, ''],
        self::KEYRING_OCI_COMPARTMENT               => [S::GLOBAL,  false, T::CHAR, null],
        self::KEYRING_OCI_ENCRYPTION_ENDPOINT       => [S::GLOBAL,  false, T::CHAR, null],
        self::KEYRING_OCI_KEY_FILE                  => [S::GLOBAL,  false, T::CHAR, null],
        self::KEYRING_OCI_KEY_FINGERPRINT           => [S::GLOBAL,  false, T::CHAR, null],
        self::KEYRING_OCI_MANAGEMENT_ENDPOINT       => [S::GLOBAL,  false, T::CHAR, null],
        self::KEYRING_OCI_MASTER_KEY                => [S::GLOBAL,  false, T::CHAR, null],
        self::KEYRING_OCI_SECRETS_ENDPOINT          => [S::GLOBAL,  false, T::CHAR, null],
        self::KEYRING_OCI_TENANCY                   => [S::GLOBAL,  false, T::CHAR, null],
        self::KEYRING_OCI_USER                      => [S::GLOBAL,  false, T::CHAR, null],
        self::KEYRING_OCI_VAULTS_ENDPOINT           => [S::GLOBAL,  false, T::CHAR, null],
        self::KEYRING_OCI_VIRTUAL_VAULT             => [S::GLOBAL,  false, T::CHAR, null],
        self::KEYRING_OKV_CONF_DIR                  => [S::GLOBAL,  true,  T::CHAR, ''], // dir
        self::KEYRING_OPERATIONS                    => [S::GLOBAL,  true,  T::BOOL, true],

        // https://dev.mysql.com/doc/refman/8.0/en/lock-order-tool.html
        self::LOCK_ORDER                            => [S::GLOBAL,  false, T::BOOL, false],
        self::LOCK_ORDER_DEBUG_LOOP                 => [S::GLOBAL,  false, T::BOOL, false],
        self::LOCK_ORDER_DEBUG_MISSING_ARC          => [S::GLOBAL,  false, T::BOOL, false],
        self::LOCK_ORDER_DEBUG_MISSING_KEY          => [S::GLOBAL,  false, T::BOOL, false],
        self::LOCK_ORDER_DEBUG_MISSING_UNLOCK       => [S::GLOBAL,  false, T::BOOL, false],
        self::LOCK_ORDER_DEPENDENCIES               => [S::GLOBAL,  false, T::CHAR, ''], // file
        self::LOCK_ORDER_EXTRA_DEPENDENCIES         => [S::GLOBAL,  false, T::CHAR, ''], // file
        self::LOCK_ORDER_OUTPUT_DIRECTORY           => [S::GLOBAL,  false, T::CHAR, ''], // dir
        self::LOCK_ORDER_PRINT_TXT                  => [S::GLOBAL,  false, T::BOOL, false],
        self::LOCK_ORDER_TRACE_LOOP                 => [S::GLOBAL,  false, T::BOOL, false],
        self::LOCK_ORDER_TRACE_MISSING_ARC          => [S::GLOBAL,  false, T::BOOL, true],
        self::LOCK_ORDER_TRACE_MISSING_KEY          => [S::GLOBAL,  false, T::BOOL, false],
        self::LOCK_ORDER_TRACE_MISSING_UNLOCK       => [S::GLOBAL,  false, T::BOOL, true],

        // https://dev.mysql.com/doc/refman/8.0/en/mysql-cluster-options-variables.html
        self::CREATE_OLD_TEMPORALS                  => [S::GLOBAL,  false, T::BOOL,     false],
        self::NDBINFO_DATABASE                      => [S::GLOBAL,  false, T::CHAR,     'ndbinfo'],
        self::NDBINFO_MAX_BYTES                     => [null,       true,  T::UNSIGNED, 0,          F::NONE, 0, 65536],
        self::NDBINFO_MAX_ROWS                      => [null,       true,  T::UNSIGNED, 10,         F::NONE, 0, 256],
        self::NDBINFO_OFFLINE                       => [S::GLOBAL,  true,  T::BOOL,     false],
        self::NDBINFO_SHOW_HIDDEN                   => [null,       true,  T::BOOL,     false],
        self::NDBINFO_TABLE_PREFIX                  => [S::GLOBAL,  false, T::CHAR,     'ndb$'],
        self::NDBINFO_VERSION                       => [S::GLOBAL,  false, T::CHAR,     '8.0.30'],
        self::NDB_ALLOW_COPYING_ALTER_TABLE         => [null,       true,  T::CHAR,     true],
        self::NDB_APPLIER_ALLOW_SKIP_EPOCH          => [S::GLOBAL,  true,  T::BOOL,     null], // documented as non-dynamic
        self::NDB_AUTOINCREMENT_PREFETCH_SZ         => [null,       true,  T::UNSIGNED, 512,        F::NONE, 1, 65536],
        self::NDB_BATCH_SIZE                        => [null,       true,  T::UNSIGNED, 32768,      F::NONE, 0, I::INT32_MAX], // documented as non-dynamic, global
        self::NDB_BLOB_READ_BATCH_BYTES             => [null,       true,  T::UNSIGNED, 65536,      F::NONE, 0, I::UINT32_MAX],
        self::NDB_BLOB_WRITE_BATCH_BYTES            => [null,       true,  T::UNSIGNED, 65536,      F::NONE, 0, I::UINT32_MAX],
        self::NDB_CACHE_CHECK_TIME                  => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, Seconds::COMMON_YEAR],
        self::NDB_CLEAR_APPLY_STATUS                => [S::GLOBAL,  true,  T::BOOL,     true],
        self::NDB_CLUSTER_CONNECTION_POOL           => [S::GLOBAL,  false, T::UNSIGNED, 1,          F::NONE, 1, 63],
        self::NDB_CLUSTER_CONNECTION_POOL_NODEIDS   => [S::GLOBAL,  false, T::CHAR,     ''],
        self::NDB_CONFLICT_ROLE                     => [S::GLOBAL,  true,  T::ENUM,     'NONE',     F::NONE, ['NONE', 'PRIMARY', 'SECONDARY', 'PASS']],
        self::NDB_DATA_NODE_NEIGHBOUR               => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, 255],
        self::NDB_DBG_CHECK_SHARES                  => [null,       true,  T::UNSIGNED, 0,          F::NONE, 0, 1],
        self::NDB_DEFAULT_COLUMN_FORMAT             => [S::GLOBAL,  true,  T::ENUM,     'FIXED',    F::NONE, ['FIXED', 'DYNAMIC']],
        self::NDB_DEFERRED_CONSTRAINTS              => [null,       true,  T::UNSIGNED, 0,          F::NONE, 0, 1],
        self::NDB_DISTRIBUTION                      => [S::GLOBAL,  true,  T::ENUM,     'KEYHASH',  F::NONE, ['LINHASH', 'KEYHASH']],
        self::NDB_EVENTBUFFER_FREE_PERCENT          => [S::GLOBAL,  true,  T::UNSIGNED, 20,         F::NONE, 1, 99],
        self::NDB_EVENTBUFFER_MAX_ALLOC             => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, MAX],
        self::NDB_EXTRA_LOGGING                     => [S::GLOBAL,  true,  T::UNSIGNED, 1,          F::NONE, 0, 1],
        self::NDB_FORCE_SEND                        => [null,       true,  T::BOOL,     true],
        self::NDB_FULLY_REPLICATED                  => [null,       true,  T::BOOL,     false],
        self::NDB_INDEX_STAT_ENABLE                 => [null,       true,  T::BOOL,     true],
        self::NDB_INDEX_STAT_OPTION                 => [null,       true,  T::CHAR,     'loop_checkon=1000ms,loop_idle=1000ms,loop_busy=100ms, update_batch=1,read_batch=4,idle_batch=32,check_batch=32, check_delay=1m,delete_batch=8,clean_delay=0,error_batch=4, error_delay=1m,evict_batch=8,evict_delay=1m,cache_limit=32M, cache_lowpct=90'],
        self::NDB_JOIN_PUSHDOWN                     => [null,       true,  T::BOOL,     true],
        self::NDB_LOG_APPLY_STATUS                  => [S::GLOBAL,  true,  T::BOOL,     false], // documented as non-dynamic
        self::NDB_LOG_BIN                           => [null,       false, T::BOOL,     false],
        self::NDB_LOG_BINLOG_INDEX                  => [S::GLOBAL,  true,  T::BOOL,     true],
        self::NDB_LOG_EMPTY_EPOCHS                  => [S::GLOBAL,  true,  T::BOOL,     false],
        self::NDB_LOG_EMPTY_UPDATE                  => [S::GLOBAL,  true,  T::BOOL,     false],
        self::NDB_LOG_EXCLUSIVE_READS               => [null,       true,  T::BOOL,     false],
        self::NDB_LOG_FAIL_TERMINATE                => [S::GLOBAL,  true,  T::BOOL,     false],
        self::NDB_LOG_ORIG                          => [S::GLOBAL,  false, T::BOOL,     false],
        self::NDB_LOG_TRANSACTION_COMPRESSION       => [S::GLOBAL,  true,  T::BOOL,     false],
        self::NDB_LOG_TRANSACTION_COMPRESSION_LEVEL_ZSTD
                                                    => [S::GLOBAL,  true,  T::UNSIGNED, 3,          F::CLAMP, 1, 22],
        self::NDB_LOG_TRANSACTION_ID                => [S::GLOBAL,  false, T::BOOL,     false],
        self::NDB_LOG_UPDATE_AS_WRITE               => [S::GLOBAL,  true,  T::BOOL,     true],
        self::NDB_LOG_UPDATE_MINIMAL                => [S::GLOBAL,  true,  T::BOOL,     false],
        self::NDB_LOG_UPDATED_ONLY                  => [S::GLOBAL,  true,  T::BOOL,     true],
        self::NDB_METADATA_CHECK                    => [S::GLOBAL,  true,  T::BOOL,     true],
        self::NDB_METADATA_CHECK_INTERVAL           => [S::GLOBAL,  true,  T::UNSIGNED, 60,         F::NONE, 0, Seconds::COMMON_YEAR],
        self::NDB_METADATA_SYNC                     => [S::GLOBAL,  true,  T::BOOL,     false],
        self::NDB_OPTIMIZATION_DELAY                => [null,       true,  T::UNSIGNED, 10,         F::NONE, 0, 100000], // documented as global
        self::NDB_OPTIMIZED_NODE_SELECTION          => [null,       true,  T::UNSIGNED, 3,          F::CLAMP, 0, 3], // documented as non-dynamic, global
        self::NDB_READ_BACKUP                       => [S::GLOBAL,  true,  T::BOOL,     true],
        self::NDB_RECV_THREAD_ACTIVATION_THRESHOLD  => [S::GLOBAL,  true,  T::UNSIGNED, 8,          F::NONE, 0, 16],
        self::NDB_RECV_THREAD_CPU_MASK              => [S::GLOBAL,  true,  T::CHAR,     ''],
        self::NDB_REPLICA_BATCH_SIZE                => [S::GLOBAL,  true,  T::UNSIGNED, 2097152,    F::CLAMP, 0, 2097152],
        self::NDB_REPLICA_BLOB_WRITE_BATCH_BYTES    => [S::GLOBAL,  true,  T::UNSIGNED, 2097152,    F::CLAMP, 0, 2097152],
        self::NDB_REPORT_THRESH_BINLOG_EPOCH_SLIP   => [S::GLOBAL,  true,  T::UNSIGNED, 10,         F::NONE, 0, 256],
        self::NDB_REPORT_THRESH_BINLOG_MEM_USAGE    => [S::GLOBAL,  true,  T::UNSIGNED, 10,         F::NONE, 0, 10],
        self::NDB_ROW_CHECKSUM                      => [null,       true,  T::UNSIGNED, 1,          F::NONE, 0, 1],
        self::NDB_SCHEMA_DIST_LOCK_WAIT_TIMEOUT     => [S::GLOBAL,  true,  T::UNSIGNED, 30,         F::NONE, 0, 1200],
        self::NDB_SCHEMA_DIST_TIMEOUT               => [S::GLOBAL,  false, T::UNSIGNED, 120,        F::NONE, 5, 1200],
        self::NDB_SCHEMA_DIST_UPGRADE_ALLOWED       => [S::GLOBAL,  false, T::BOOL,     true],
        self::NDB_SHOW_FOREIGN_KEY_MOCK_TABLES      => [null,       true,  T::BOOL,     false], // documented as global
        self::NDB_SLAVE_CONFLICT_ROLE               => [S::GLOBAL,  true,  T::ENUM,     'NONE',     F::NONE, ['NONE', 'PRIMARY', 'SECONDARY', 'PASS']],
        self::NDB_TABLE_NO_LOGGING                  => [S::SESSION, true,  T::BOOL,     false],
        self::NDB_TABLE_TEMPORARY                   => [S::SESSION, true,  T::BOOL,     false],
        self::NDB_USE_COPYING_ALTER_TABLE           => [null,       true,  T::BOOL,     false], // documented as non-dynamic
        self::NDB_USE_EXACT_COUNT                   => [null,       true,  T::BOOL,     false],
        self::NDB_USE_TRANSACTIONS                  => [null,       true,  T::BOOL,     true],
        self::NDB_VERSION                           => [S::GLOBAL,  false, T::CHAR,     '80030',    F::NO_PERSIST],
        self::NDB_VERSION_STRING                    => [S::GLOBAL,  false, T::CHAR,     'ndb-8.0.30', F::NO_PERSIST],
        self::NDB_WAIT_CONNECTED                    => [S::GLOBAL,  false, T::UNSIGNED, 120,        F::NONE, 0, Seconds::COMMON_YEAR],
        self::NDB_WAIT_SETUP                        => [S::GLOBAL,  false, T::UNSIGNED, 120,        F::NONE, 0, Seconds::COMMON_YEAR],
        self::REPLICA_ALLOW_BATCHING                => [S::GLOBAL,  true,  T::BOOL,     true],
        self::SERVER_ID                             => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, I::UINT32_MAX], // undocumented
        self::SERVER_ID_BITS                        => [S::GLOBAL,  true,  T::UNSIGNED, 32,         F::CLAMP, 7, 32],
        self::SERVER_UUID                           => [S::GLOBAL,  false, T::CHAR,     'cc15263f-c01d-11ec-a01c-18c04d93aa6b', F::NO_PERSIST], // random
        self::SLAVE_ALLOW_BATCHING                  => [S::GLOBAL,  true,  T::BOOL,     true],
        self::TRANSACTION_ALLOW_BATCHING            => [S::SESSION, true,  T::BOOL,     false],

        // audit plugin. where is doc?
        self::NULL_AUDIT_ABORT_MESSAGE              => [null,       true,  T::CHAR,     null,      F::NULLABLE],
        self::NULL_AUDIT_EVENT_ORDER_CHECK          => [null,       true,  T::CHAR,     null,      F::NULLABLE],
        self::NULL_AUDIT_EVENT_RECORD               => [null,       true,  T::CHAR,     null,      F::NON_EMPTY],

        // https://dev.mysql.com/doc/refman/8.0/en/replication-options-source.html
        self::AUTO_INCREMENT_INCREMENT                      => [null,       true,  T::UNSIGNED, 1,      F::SET_VAR | F::CLAMP, 1, 65536],
        self::AUTO_INCREMENT_OFFSET                         => [null,       true,  T::UNSIGNED, 1,      F::SET_VAR | F::CLAMP, 1, 65536],
        self::IMMEDIATE_SERVER_VERSION                      => [S::SESSION, true,  T::UNSIGNED, 999999, F::CLAMP, 0, 999999],
        self::ORIGINAL_SERVER_VERSION                       => [S::SESSION, true,  T::UNSIGNED, 999999, F::CLAMP, 0, 999999],
        self::RPL_SEMI_SYNC_MASTER_ENABLED                  => [S::GLOBAL,  true,  T::BOOL,     false],
        self::RPL_SEMI_SYNC_MASTER_TIMEOUT                  => [S::SESSION, true,  T::UNSIGNED, 10000,  F::NONE, 0, I::UINT32_MAX],
        self::RPL_SEMI_SYNC_MASTER_TRACE_LEVEL              => [S::GLOBAL,  true,  T::UNSIGNED, 32,     F::NONE, 0, I::UINT32_MAX],
        self::RPL_SEMI_SYNC_MASTER_WAIT_FOR_SLAVE_COUNT     => [S::GLOBAL,  true,  T::UNSIGNED, 1,      F::NONE, 1, 65536],
        self::RPL_SEMI_SYNC_MASTER_WAIT_NO_SLAVE            => [S::GLOBAL,  true,  T::BOOL,     false],
        self::RPL_SEMI_SYNC_MASTER_WAIT_POINT               => [S::GLOBAL,  true,  T::ENUM,     'AFTER_SYNC', F::NONE, ['AFTER_SYNC', 'AFTER_COMMIT']],
        self::RPL_SEMI_SYNC_SOURCE_ENABLED                  => [S::GLOBAL,  true,  T::BOOL,     false],
        self::RPL_SEMI_SYNC_SOURCE_TIMEOUT                  => [S::GLOBAL,  true,  T::UNSIGNED, 10000,  F::NONE, 0, I::UINT32_MAX],
        self::RPL_SEMI_SYNC_SOURCE_TRACE_LEVEL              => [S::GLOBAL,  true,  T::UNSIGNED, 32,     F::NONE, 0, I::UINT32_MAX],
        self::RPL_SEMI_SYNC_SOURCE_WAIT_FOR_REPLICA_COUNT   => [S::GLOBAL,  true,  T::UNSIGNED, 1,      F::CLAMP, 1, 65536],
        self::RPL_SEMI_SYNC_SOURCE_WAIT_NO_REPLICA          => [S::GLOBAL,  true,  T::BOOL,     true],
        self::RPL_SEMI_SYNC_SOURCE_WAIT_POINT               => [S::GLOBAL,  true,  T::ENUM,     'AFTER_SYNC', F::NONE, ['AFTER_SYNC', 'AFTER_COMMIT']],

        // https://dev.mysql.com/doc/refman/8.0/en/replication-options-replica.html
        self::INIT_REPLICA                          => [S::GLOBAL,  true,  T::CHAR,     null,       F::NULLABLE],
        self::INIT_SLAVE                            => [S::GLOBAL,  true,  T::BOOL,     null],
        self::LOG_SLOW_REPLICA_STATEMENTS           => [S::GLOBAL,  true,  T::BOOL,     false],
        self::LOG_SLOW_SLAVE_STATEMENTS             => [S::GLOBAL,  true,  T::BOOL,     false],
        self::MASTER_INFO_REPOSITORY                => [S::GLOBAL,  true,  T::ENUM,     'TABLE',    F::NONE, ['FILE', 'TABLE']],
        self::MAX_RELAY_LOG_SIZE                    => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, 1073741824, 4096],
        self::RELAY_LOG                             => [S::GLOBAL,  false, T::BOOL,     null], // file
        self::RELAY_LOG_BASENAME                    => [S::GLOBAL,  false, T::CHAR,     '/var/lib/mysql/data/example-relay-bin', F::NO_PERSIST], // file
        self::RELAY_LOG_INDEX                       => [S::GLOBAL,  false, T::CHAR,     'example-relay-bin.index', F::NO_PERSIST], // file
        self::RELAY_LOG_INFO_FILE                   => [S::GLOBAL,  false, T::CHAR,     'relay-log.info', F::NO_PERSIST], // file
        self::RELAY_LOG_INFO_REPOSITORY             => [S::GLOBAL,  true,  T::ENUM,     'TABLE',    F::NONE, ['FILE', 'TABLE']],
        self::RELAY_LOG_PURGE                       => [S::GLOBAL,  true,  T::BOOL,     true],
        self::RELAY_LOG_RECOVERY                    => [S::GLOBAL,  false, T::BOOL,     false],
        self::RELAY_LOG_SPACE_LIMIT                 => [S::GLOBAL,  false, T::UNSIGNED, 0,          F::NONE, 0, I::UINT32_MAX],
        self::REPLICA_CHECKPOINT_GROUP              => [S::GLOBAL,  true,  T::UNSIGNED, 512,        F::CLAMP, 32, 534280, 8],
        self::REPLICA_CHECKPOINT_PERIOD             => [S::GLOBAL,  true,  T::UNSIGNED, 300,        F::NONE, 1, I::UINT32_MAX],
        self::REPLICA_COMPRESSED_PROTOCOL           => [S::GLOBAL,  true,  T::BOOL,     false],
        self::REPLICA_EXEC_MODE                     => [S::GLOBAL,  true,  T::ENUM,     'STRICT',   F::NONE, ['STRICT', 'IDEMPOTENT']],
        self::REPLICA_LOAD_TMPDIR                   => [S::GLOBAL,  false, T::CHAR,     '/tmp',     F::NO_PERSIST], // dir
        self::REPLICA_MAX_ALLOWED_PACKET            => [S::GLOBAL,  true,  T::UNSIGNED, 1073741824, F::NONE, 1024, 1073741824, 1024],
        self::REPLICA_NET_TIMEOUT                   => [S::GLOBAL,  true,  T::UNSIGNED, 60,         F::NONE, 1, 31536000],
        self::REPLICA_PARALLEL_TYPE                 => [S::GLOBAL,  true,  T::ENUM,     'LOGICAL_CLOCK', F::NONE, ['DATABASE', 'LOGICAL_CLOCK']],
        self::REPLICA_PARALLEL_WORKERS              => [S::GLOBAL,  true,  T::UNSIGNED, 4,          F::NONE, 0, 1024],
        self::REPLICA_PENDING_JOBS_SIZE_MAX         => [S::GLOBAL,  true,  T::UNSIGNED, 134217728,  F::NONE, 1024, MAX, 1024],
        self::REPLICA_PRESERVE_COMMIT_ORDER         => [S::GLOBAL,  true,  T::BOOL,     true],
        self::REPLICA_SKIP_ERRORS                   => [S::GLOBAL,  false, T::CHAR,     'OFF'], // ['OFF', 'ALL', 'DDL_EXIST_ERRORS', '...'],
        self::REPLICA_SQL_VERIFY_CHECKSUM           => [S::GLOBAL,  true,  T::BOOL,     true],
        self::REPLICA_TRANSACTION_RETRIES           => [S::GLOBAL,  true,  T::UNSIGNED, 10,         F::NONE, 0, MAX],
        self::REPLICA_TYPE_CONVERSIONS              => [S::GLOBAL,  true,  T::SET,      '',         F::NONE, ['ALL_LOSSY', 'ALL_NON_LOSSY', 'ALL_SIGNED', 'ALL_UNSIGNED']],
        self::REPLICATION_OPTIMIZE_FOR_STATIC_PLUGIN_CONFIG
                                                    => [S::GLOBAL,  true,  T::BOOL,     false],
        self::REPLICATION_SENDER_OBSERVE_COMMIT_ONLY
                                                    => [S::GLOBAL,  true,  T::BOOL,     false],
        self::REPORT_HOST                           => [S::GLOBAL,  false, T::CHAR,     null],
        self::REPORT_PASSWORD                       => [S::GLOBAL,  false, T::CHAR,     null],
        self::REPORT_PORT                           => [S::GLOBAL,  false, T::UNSIGNED, 3307,       F::NONE, 0, 65536],
        self::REPORT_USER                           => [S::GLOBAL,  false, T::CHAR,     null],
        self::RPL_READ_SIZE                         => [S::GLOBAL,  true,  T::UNSIGNED, 8192,       F::CLAMP, 8192, I::UINT32_MAX, 8192],
        self::RPL_SEMI_SYNC_REPLICA_ENABLED         => [S::GLOBAL,  true,  T::BOOL,     false],
        self::RPL_SEMI_SYNC_REPLICA_TRACE_LEVEL     => [S::GLOBAL,  true,  T::UNSIGNED, 32,         F::NONE, 0, I::UINT32_MAX],
        self::RPL_SEMI_SYNC_SLAVE_ENABLED           => [S::GLOBAL,  true,  T::BOOL,     false],
        self::RPL_SEMI_SYNC_SLAVE_TRACE_LEVEL       => [S::GLOBAL,  true,  T::UNSIGNED, 32,         F::NONE, 0, I::UINT32_MAX],
        self::RPL_STOP_REPLICA_TIMEOUT              => [S::GLOBAL,  true,  T::UNSIGNED, 31536000,   F::NONE, 2, Seconds::COMMON_YEAR],
        self::RPL_STOP_SLAVE_TIMEOUT                => [S::GLOBAL,  true,  T::UNSIGNED, 31536000,   F::NONE, 2, Seconds::COMMON_YEAR],
        self::SKIP_REPLICA_START                    => [S::GLOBAL,  false, T::BOOL,     false],
        self::SKIP_SLAVE_START                      => [S::GLOBAL,  false, T::BOOL,     false],
        self::SLAVE_CHECKPOINT_GROUP                => [S::GLOBAL,  true,  T::UNSIGNED, 512,        F::NONE, 32, 524288, 8],
        self::SLAVE_CHECKPOINT_PERIOD               => [S::GLOBAL,  true,  T::UNSIGNED, 300,        F::NONE, 1, I::UINT32_MAX],
        self::SLAVE_COMPRESSED_PROTOCOL             => [S::GLOBAL,  true,  T::BOOL,     false],
        self::SLAVE_EXEC_MODE                       => [S::GLOBAL,  true,  T::ENUM,     'STRICT',   F::NONE, ['STRICT', 'IDEMPOTENT']],
        self::SLAVE_LOAD_TMPDIR                     => [S::GLOBAL,  true,  T::CHAR,     '/tmp'], // dir
        self::SLAVE_MAX_ALLOWED_PACKET              => [S::GLOBAL,  true,  T::UNSIGNED, 1073741824, F::NONE, 1024, 1073741824, 1024],
        self::SLAVE_NET_TIMEOUT                     => [S::GLOBAL,  true,  T::UNSIGNED, 60,         F::NONE, 1, 31536000],
        self::SLAVE_PARALLEL_TYPE                   => [S::GLOBAL,  true,  T::ENUM,     'LOGICAL_CLOCK', F::NONE, ['DATABASE', 'LOGICAL_CLOCK']],
        self::SLAVE_PARALLEL_WORKERS                => [S::GLOBAL,  true,  T::UNSIGNED, 4,          F::NONE, 0, 1024],
        self::SLAVE_PENDING_JOBS_SIZE_MAX           => [S::GLOBAL,  true,  T::UNSIGNED, 134217728,  F::NONE, 1024, MAX, 1024],
        self::SLAVE_PRESERVE_COMMIT_ORDER           => [S::GLOBAL,  true,  T::BOOL,     true],
        self::SLAVE_ROWS_SEARCH_ALGORITHMS          => [S::GLOBAL,  true,  T::SET,      'INDEX_SCAN,HASH_SCAN', F::NON_EMPTY, ['TABLE_SCAN', 'INDEX_SCAN', 'HASH_SCAN']],
        self::SLAVE_SKIP_ERRORS                     => [S::GLOBAL,  false, T::CHAR,     'OFF'], // ['OFF', 'ALL', 'DDL_EXIST_ERRORS', '...'],
        self::SLAVE_SQL_VERIFY_CHECKSUM             => [S::GLOBAL,  true,  T::BOOL,     true],
        self::SLAVE_TRANSACTION_RETRIES             => [S::GLOBAL,  true,  T::UNSIGNED, 10,         F::NONE, 0, MAX],
        self::SLAVE_TYPE_CONVERSIONS                => [S::GLOBAL,  true,  T::SET,      '',         F::NONE, ['ALL_LOSSY', 'ALL_NON_LOSSY', 'ALL_SIGNED', 'ALL_UNSIGNED']],
        self::SQL_REPLICA_SKIP_COUNTER              => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, I::UINT32_MAX],
        self::SQL_SLAVE_SKIP_COUNTER                => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::NONE, 0, I::UINT32_MAX],
        self::SYNC_MASTER_INFO                      => [S::GLOBAL,  true,  T::UNSIGNED, 10000,      F::NONE, 0, I::UINT32_MAX],
        self::SYNC_RELAY_LOG                        => [S::GLOBAL,  true,  T::UNSIGNED, 10000,      F::NONE, 0, I::UINT32_MAX],
        self::SYNC_RELAY_LOG_INFO                   => [S::GLOBAL,  true,  T::UNSIGNED, 10000,      F::NONE, 0, I::UINT32_MAX],
        self::SYNC_SOURCE_INFO                      => [S::GLOBAL,  true,  T::UNSIGNED, 10000,      F::NONE, 0, I::UINT32_MAX],
        self::TERMINOLOGY_USE_PREVIOUS              => [null,       true,  T::ENUM,     'NONE',     F::NONE, ['NONE', 'BEFORE_8_0_26']],

        // https://dev.mysql.com/doc/refman/8.0/en/rewriter-query-rewrite-plugin-reference.html
        self::REWRITER_ENABLED                                      => [S::GLOBAL,  true, T::BOOL,     true],
        self::REWRITER_ENABLED_FOR_THREADS_WITHOUT_PRIVILEGE_CHECKS => [S::GLOBAL,  true, T::BOOL,     true],
        self::REWRITER_VERBOSE                                      => [S::GLOBAL,  true, T::UNSIGNED, 0],

        // https://dev.mysql.com/doc/refman/8.0/en/performance-schema-system-variables.html
        self::PERFORMANCE_SCHEMA                            => [S::GLOBAL,  false, T::BOOL,     true],
        self::PERFORMANCE_SCHEMA_ACCOUNTS_SIZE              => [S::GLOBAL,  false, T::SIGNED,   -1, F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_DIGESTS_SIZE               => [S::GLOBAL,  false, T::SIGNED,   -1, F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_ERROR_SIZE                 => [S::GLOBAL,  false, T::UNSIGNED, 0,  F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_EVENTS_STAGES_HISTORY_LONG_SIZE        => [S::GLOBAL, false, T::SIGNED, -1, F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_EVENTS_STAGES_HISTORY_SIZE             => [S::GLOBAL, false, T::SIGNED, -1, F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_EVENTS_STATEMENTS_HISTORY_LONG_SIZE    => [S::GLOBAL, false, T::SIGNED, -1, F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_EVENTS_STATEMENTS_HISTORY_SIZE         => [S::GLOBAL, false, T::SIGNED, -1, F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_EVENTS_TRANSACTIONS_HISTORY_LONG_SIZE  => [S::GLOBAL, false, T::SIGNED, -1, F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_EVENTS_TRANSACTIONS_HISTORY_SIZE       => [S::GLOBAL, false, T::SIGNED, -1, F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_EVENTS_WAITS_HISTORY_LONG_SIZE         => [S::GLOBAL, false, T::SIGNED, -1, F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_EVENTS_WAITS_HISTORY_SIZE              => [S::GLOBAL, false, T::SIGNED, -1, F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_HOSTS_SIZE                 => [S::GLOBAL,  false, T::SIGNED,   -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_COND_CLASSES           => [S::GLOBAL,  false, T::UNSIGNED, 150,    F::NONE, 0, 1024],
        self::PERFORMANCE_SCHEMA_MAX_COND_INSTANCES         => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_DIGEST_LENGTH          => [S::GLOBAL,  false, T::UNSIGNED, 1024,   F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_DIGEST_SAMPLE_AGE      => [S::GLOBAL,  true,  T::UNSIGNED, 60,     F::CLAMP, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_FILE_CLASSES           => [S::GLOBAL,  false, T::UNSIGNED, 80,     F::NONE, 0, 1024],
        self::PERFORMANCE_SCHEMA_MAX_FILE_HANDLES           => [S::GLOBAL,  false, T::UNSIGNED, 32768,  F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_FILE_INSTANCES         => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_INDEX_STAT             => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_MEMORY_CLASSES         => [S::GLOBAL,  false, T::UNSIGNED, 450,    F::NONE, 0, 1024],
        self::PERFORMANCE_SCHEMA_MAX_METADATA_LOCKS         => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_MUTEX_CLASSES          => [S::GLOBAL,  false, T::UNSIGNED, 350,    F::NONE, 0, 1024],
        self::PERFORMANCE_SCHEMA_MAX_MUTEX_INSTANCES        => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_PREPARED_STATEMENTS_INSTANCES
                                                            => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_RWLOCK_CLASSES         => [S::GLOBAL,  false, T::UNSIGNED, 60,     F::NONE, 0, 1024],
        self::PERFORMANCE_SCHEMA_MAX_RWLOCK_INSTANCES       => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_PROGRAM_INSTANCES      => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_SOCKET_CLASSES         => [S::GLOBAL,  false, T::UNSIGNED, 10,     F::NONE, 0, 1024],
        self::PERFORMANCE_SCHEMA_MAX_SOCKET_INSTANCES       => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_SQL_TEXT_LENGTH        => [S::GLOBAL,  false, T::UNSIGNED, 1024,   F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_STAGE_CLASSES          => [S::GLOBAL,  false, T::UNSIGNED, 175,    F::NONE, 0, 1024],
        self::PERFORMANCE_SCHEMA_MAX_STATEMENT_CLASSES      => [S::GLOBAL,  false, T::UNSIGNED, 128,    F::NONE, 0, 256],
        self::PERFORMANCE_SCHEMA_MAX_STATEMENT_STACK        => [S::GLOBAL,  false, T::UNSIGNED, 10,     F::NONE, 1, 256],
        self::PERFORMANCE_SCHEMA_MAX_TABLE_HANDLES          => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_TABLE_INSTANCES        => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_TABLE_LOCK_STAT        => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_MAX_THREAD_CLASSES         => [S::GLOBAL,  false, T::UNSIGNED, 100,    F::NONE, 0, 1024],
        self::PERFORMANCE_SCHEMA_MAX_THREAD_INSTANCES       => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_SESSION_CONNECT_ATTRS_SIZE => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_SETUP_ACTORS_SIZE          => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_SETUP_OBJECTS_SIZE         => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],
        self::PERFORMANCE_SCHEMA_SHOW_PROCESSLIST           => [S::GLOBAL,  true,  T::BOOL,     false],
        self::PERFORMANCE_SCHEMA_USERS_SIZE                 => [S::GLOBAL,  false, T::UNSIGNED, -1,     F::NONE, 0, 1048576],

        // https://dev.mysql.com/doc/refman/8.0/en/validate-password-options-variables.html
        self::VALIDATE_PASSWORD__CHECK_USER_NAME    => [S::GLOBAL,  true,  T::BOOL,     true],
        self::VALIDATE_PASSWORD__DICTIONARY_FILE    => [S::GLOBAL,  true,  T::CHAR,     null, F::NULLABLE], // file
        self::VALIDATE_PASSWORD__LENGTH             => [S::GLOBAL,  true,  T::UNSIGNED, 8, F::CLAMP, 0, MAX],
        self::VALIDATE_PASSWORD__MIXED_CASE_COUNT   => [S::GLOBAL,  true,  T::UNSIGNED, 1, F::NONE, 0, MAX],
        self::VALIDATE_PASSWORD__NUMBER_COUNT       => [S::GLOBAL,  true,  T::UNSIGNED, 1, F::NONE, 0, MAX],
        self::VALIDATE_PASSWORD__POLICY             => [S::GLOBAL,  true,  T::UNSIGNED, 1, F::NONE, 0, 2],
        self::VALIDATE_PASSWORD__SPECIAL_CHAR_COUNT => [S::GLOBAL,  true,  T::UNSIGNED, 1, F::NONE, 0, MAX],
        self::VALIDATE_PASSWORD__CHANGED_CHARACTERS_PERCENTAGE => [S::GLOBAL,  true,  T::UNSIGNED, 0, F::NONE, 0, 100], // since 8.0.34
        self::VALIDATE_PASSWORD_CHECK_USER_NAME     => [S::GLOBAL,  true,  T::BOOL,     true],
        self::VALIDATE_PASSWORD_DICTIONARY_FILE     => [S::GLOBAL,  true,  T::CHAR,     null, F::NULLABLE], // file
        self::VALIDATE_PASSWORD_LENGTH              => [S::GLOBAL,  true,  T::UNSIGNED, 8, F::CLAMP, 0, MAX],
        self::VALIDATE_PASSWORD_MIXED_CASE_COUNT    => [S::GLOBAL,  true,  T::UNSIGNED, 1, F::NONE, 0, MAX],
        self::VALIDATE_PASSWORD_NUMBER_COUNT        => [S::GLOBAL,  true,  T::UNSIGNED, 1, F::NONE, 0, MAX],
        self::VALIDATE_PASSWORD_POLICY              => [S::GLOBAL,  true,  T::UNSIGNED, 1, F::NONE, 0, 2],
        self::VALIDATE_PASSWORD_SPECIAL_CHAR_COUNT  => [S::GLOBAL,  true,  T::UNSIGNED, 1, F::NONE, 0, MAX],

        // https://dev.mysql.com/doc/refman/8.0/en/version-tokens-reference.html
        self::VERSION_TOKENS_SESSION        => [null, true,  T::CHAR, null, F::NULLABLE],
        self::VERSION_TOKENS_SESSION_NUMBER => [null, false, T::UNSIGNED, 0, F::NO_PERSIST],

        // https://dev.mysql.com/doc/refman/8.0/en/x-plugin-options-system-variables.html
        self::MYSQLX_BIND_ADDRESS                           => [S::GLOBAL,  false, T::CHAR,     '*'],
        self::MYSQLX_COMPRESSION_ALGORITHMS                 => [S::GLOBAL,  true,  T::SET,      'DEFLATE_STREAM,LZ4_MESSAGE,ZSTD_STREAM', F::NONE, ['DEFLATE_STREAM', 'LZ4_MESSAGE', 'ZSTD_STREAM']],
        self::MYSQLX_CONNECT_TIMEOUT                        => [S::GLOBAL,  true,  T::UNSIGNED, 30,         F::CLAMP, 1, 1000000000],
        self::MYSQLX_DEFLATE_DEFAULT_COMPRESSION_LEVEL      => [S::GLOBAL,  true,  T::UNSIGNED, 3,          F::NONE, 1, 9],
        self::MYSQLX_DEFLATE_MAX_CLIENT_COMPRESSION_LEVEL   => [S::GLOBAL,  true,  T::UNSIGNED, 5,          F::NONE, 1, 9],
        self::MYSQLX_DOCUMENT_ID_UNIQUE_PREFIX              => [S::GLOBAL,  true,  T::UNSIGNED, 0,          F::CLAMP, 0, 65536],
        self::MYSQLX_ENABLE_HELLO_NOTICE                    => [S::GLOBAL,  true,  T::BOOL,     true],
        self::MYSQLX_IDLE_WORKER_THREAD_TIMEOUT             => [S::GLOBAL,  true,  T::UNSIGNED, 60,         F::CLAMP, 0, 3600],
        self::MYSQLX_INTERACTIVE_TIMEOUT                    => [S::GLOBAL,  true,  T::UNSIGNED, 28800,      F::CLAMP, 1, 2147483],
        self::MYSQLX_LZ4_DEFAULT_COMPRESSION_LEVEL          => [S::GLOBAL,  true,  T::UNSIGNED, 2,          F::NONE, 0, 16],
        self::MYSQLX_LZ4_MAX_CLIENT_COMPRESSION_LEVEL       => [S::GLOBAL,  true,  T::UNSIGNED, 8,          F::NONE, 0, 16],
        self::MYSQLX_MAX_ALLOWED_PACKET                     => [S::GLOBAL,  true,  T::UNSIGNED, 67108864,   F::CLAMP, 512, 1073741824],
        self::MYSQLX_MAX_CONNECTIONS                        => [S::GLOBAL,  true,  T::UNSIGNED, 100,        F::CLAMP, 1, 65535],
        self::MYSQLX_MIN_WORKER_THREADS                     => [S::GLOBAL,  true,  T::UNSIGNED, 2,          F::CLAMP, 1, 100],
        self::MYSQLX_PORT                                   => [S::GLOBAL,  false, T::UNSIGNED, 33060,      F::NONE, 1, 65535],
        self::MYSQLX_PORT_OPEN_TIMEOUT                      => [S::GLOBAL,  false, T::UNSIGNED, 0,          F::NONE, 0, 120],
        self::MYSQLX_READ_TIMEOUT                           => [null,       true,  T::UNSIGNED, 30,         F::CLAMP, 1, 2147483],
        self::MYSQLX_SOCKET                                 => [S::GLOBAL,  false, T::CHAR,     '/tmp/mysqlx.sock'],
        self::MYSQLX_SSL_CA                                 => [S::GLOBAL,  false, T::CHAR,     null], // file
        self::MYSQLX_SSL_CAPATH                             => [S::GLOBAL,  false, T::CHAR,     null], // dir
        self::MYSQLX_SSL_CERT                               => [S::GLOBAL,  false, T::CHAR,     null], // file
        self::MYSQLX_SSL_CIPHER                             => [S::GLOBAL,  false, T::CHAR,     null],
        self::MYSQLX_SSL_CRL                                => [S::GLOBAL,  false, T::CHAR,     null], // file
        self::MYSQLX_SSL_CRLPATH                            => [S::GLOBAL,  false, T::CHAR,     null], // dir
        self::MYSQLX_SSL_KEY                                => [S::GLOBAL,  false, T::CHAR,     null], // file
        self::MYSQLX_WAIT_TIMEOUT                           => [null,       true,  T::UNSIGNED, 28800,      F::CLAMP, 1, 2147483],
        self::MYSQLX_WRITE_TIMEOUT                          => [null,       true,  T::UNSIGNED, 60,         F::CLAMP, 1, 2147483],
        self::MYSQLX_ZSTD_DEFAULT_COMPRESSION_LEVEL         => [S::GLOBAL,  true,  T::SIGNED,   3,          F::NON_ZERO, -131072, 22],
        self::MYSQLX_ZSTD_MAX_CLIENT_COMPRESSION_LEVEL      => [S::GLOBAL,  true,  T::SIGNED,   11,         F::NON_ZERO, -131072, 22],
        //                                                      0:scope, 1:dynamic, 2:type, 3:default, [4:flags, [5:values]|[5:min, 6:max, [7:increment]]]
    ];

    public static function normalizeValue(string $value): string
    {
        $valid = self::validateValue($value);
        if (!$valid) {
            throw new InvalidDefinitionException("'{$value}' is not a valid name of system variable.");
        }

        return $value;
    }

    public static function getScope(string $variable): ?string
    {
        return self::$properties[$variable][0] ?? null;
    }

    public static function isDynamic(string $variable): ?bool
    {
        return self::$properties[$variable][1] ?? null;
    }

    public static function isSessionReadonly(string $variable): bool
    {
        $properties = self::$properties[$variable] ?? [];
        $flags = $properties[4] ?? 0;

        return ($flags & F::SESSION_READONLY) !== 0;
    }

    public static function supportsSetVar(string $variable): bool
    {
        $properties = self::$properties[$variable] ?? [];
        $flags = $properties[4] ?? 0;

        return ($flags & F::SET_VAR) !== 0;
    }

    public static function isNonPersistent(string $variable): bool
    {
        $properties = self::$properties[$variable] ?? [];
        $flags = $properties[4] ?? 0;

        return ($flags & F::NO_PERSIST) !== 0;
    }

    public static function getType(string $variable): string
    {
        return self::$properties[$variable][2] ?? T::CHAR;
    }

    /**
     * @return list<int|string>
     */
    public static function getValues(string $variable): array
    {
        $type = self::$properties[$variable][2] ?? null;
        if ($type === T::ENUM || $type === T::SET) {
            /** @var list<int|string> $values */
            $values = self::$properties[$variable][5] ?? [];

            return $values;
        } else {
            return [];
        }
    }

    /**
     * @return scalar|null
     */
    public static function getDefault(string $variable)
    {
        return self::$properties[$variable][3] ?? null;
    }

    public static function hasDefault(string $variable): bool
    {
        $properties = self::$properties[$variable] ?? [];
        $flags = $properties[4] ?? 0;

        return ($flags & F::NO_DEFAULT) === 0;
    }

    public static function getInfo(string $variable): SystemVariableInfo
    {
        $properties = self::$properties[$variable] ?? [];
        $type = $properties[2] ?? T::CHAR;
        $enum = $type === T::ENUM || $type === T::SET;
        $flags = $properties[4] ?? 0;
        /** @var non-empty-list<int|string>|null $values */
        $values = $enum ? $properties[5] ?? null : null;
        /** @var int|float|null $min */
        $min = !$enum ? $properties[5] ?? null : null;
        $max = !$enum ? $properties[6] ?? null : null;
        $increment = !$enum ? $properties[7] ?? null : null;

        return new SystemVariableInfo(
            $variable,
            $type,
            ($flags & F::NULLABLE) !== 0,
            ($flags & F::NON_EMPTY) !== 0,
            ($flags & F::NON_ZERO) !== 0,
            $values,
            $min,
            $max,
            $increment,
            !$enum && ($flags & F::CLAMP) !== 0,
            !$enum && ($flags & F::CLAMP_MIN) !== 0
        );
    }

}
