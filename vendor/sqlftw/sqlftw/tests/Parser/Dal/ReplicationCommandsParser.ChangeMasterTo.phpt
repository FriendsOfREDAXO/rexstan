<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// CHANGE MASTER TO
Assert::parseSerialize("CHANGE MASTER TO
  MASTER_BIND = 'interface_name',
  MASTER_HOST = 'host_name',
  MASTER_USER = 'user_name',
  MASTER_PASSWORD = 'password',
  MASTER_PORT = 3306,
  MASTER_CONNECT_RETRY = 60,
  MASTER_RETRY_COUNT = 10,
  MASTER_DELAY = 61,
  MASTER_HEARTBEAT_PERIOD = 62,
  MASTER_LOG_FILE = 'master.log',
  MASTER_LOG_POS = 123,
  MASTER_AUTO_POSITION = 1,
  RELAY_LOG_FILE = 'relay.log',
  RELAY_LOG_POS = 456,
  MASTER_SSL = 1,
  MASTER_SSL_CA = 'ca_file_name',
  MASTER_SSL_CAPATH = 'ca_directory_name',
  MASTER_SSL_CERT = 'cert_file_name',
  MASTER_SSL_CRL = 'crl_file_name',
  MASTER_SSL_CRLPATH = 'crl_directory_name',
  MASTER_SSL_KEY = 'key_file_name',
  MASTER_SSL_CIPHER = 'cipher_list',
  MASTER_SSL_VERIFY_SERVER_CERT = 1,
  MASTER_TLS_VERSION = 'protocol_list',
  IGNORE_SERVER_IDS = (1, 2, 3)
FOR CHANNEL 'channel_name'");
