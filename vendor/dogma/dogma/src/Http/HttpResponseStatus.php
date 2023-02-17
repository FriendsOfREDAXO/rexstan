<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: CONV REQD MALFORMAT RECV CRL PASV RETR PRET CSEQ
// phpcs:disable SlevomatCodingStandard.Classes.ConstantSpacing.IncorrectCountOfBlankLinesAfterConstant

namespace Dogma\Http;

use Dogma\Check;

/**
 * HTTP 1.1 response status codes
 */
class HttpResponseStatus extends HttpOrCurlStatus
{

    /** @deprecated @removed */
    public const FAILED_INIT = 2;
    /** @deprecated @removed */
    public const NOT_BUILT_IN = 4;
    /** @deprecated @removed */
    public const OUT_OF_MEMORY = 27;
    /** @deprecated @removed */
    public const HTTP_POST_ERROR = 34;
    /** @deprecated @removed */
    public const FUNCTION_NOT_FOUND = 41;
    /** @deprecated @removed */
    public const BAD_FUNCTION_ARGUMENT = 43;
    /** @deprecated @removed */
    public const SEND_FAIL_REWIND = 65;
    /** @deprecated @removed */
    public const CONV_FAILED = 75;
    /** @deprecated @removed */
    public const CONV_REQD = 76;

    // file system
    /** @deprecated @removed */
    public const READ_ERROR = 26;
    /** @deprecated @removed */
    public const WRITE_ERROR = 23;
    /** @deprecated @removed */
    public const COULD_NOT_READ_FILE = 37;
    /** @deprecated @removed */
    public const FILE_SIZE_EXCEEDED = 63;

    // user error
    /** @deprecated @removed */
    public const UNSUPPORTED_PROTOCOL = 1;
    /** @deprecated @removed */
    public const URL_MALFORMAT = 3;
    /** @deprecated @removed */
    public const HTTP_RETURNED_ERROR = 22;
    /** @deprecated @removed */
    public const BAD_DOWNLOAD_RESUME = 36;
    /** @deprecated @removed */
    public const UNKNOWN_OPTION = 48;
    /** @deprecated @removed */
    public const BAD_CONTENT_ENCODING = 61;
    /** @deprecated @removed */
    public const LOGIN_DENIED = 67;
    /** @deprecated @removed */
    public const REMOTE_FILE_NOT_FOUND = 78;

    // network/socket
    /** @deprecated @removed */
    public const COULD_NOT_RESOLVE_PROXY = 5;
    /** @deprecated @removed */
    public const COULD_NOT_RESOLVE_HOST = 6;
    /** @deprecated @removed */
    public const COULD_NOT_CONNECT = 7;
    /** @deprecated @removed */
    public const INTERFACE_FAILED = 45;
    /** @deprecated @removed */
    public const SEND_ERROR = 55;
    /** @deprecated @removed */
    public const RECV_ERROR = 56;
    /** @deprecated @removed */
    public const TRY_AGAIN = 81;

    // server
    /** @deprecated @removed */
    public const UNKNOWN_RESPONSE_CODE = -1;
    /** @deprecated @removed */
    public const RANGE_ERROR = 33;
    /** @deprecated @removed */
    public const GOT_NOTHING = 52;

    // other
    /** @deprecated @removed */
    public const PARTIAL_FILE = 18;
    /** @deprecated @removed */
    public const OPERATION_TIMED_OUT = 28;
    /** @deprecated @removed */
    public const ABORTED_BY_CALLBACK = 42;
    /** @deprecated @removed */
    public const TOO_MANY_REDIRECTS = 47;

    // SSL
    /** @deprecated @removed */
    public const SSL_CONNECT_ERROR = 35;
    /** @deprecated @removed */
    public const SSL_PEER_FAILED_VERIFICATION = 51;
    /** @deprecated @removed */
    public const SSL_ENGINE_NOT_FOUND = 53;
    /** @deprecated @removed */
    public const SSL_ENGINE_SET_FAILED = 54;
    /** @deprecated @removed */
    public const SSL_CERT_PROBLEM = 58;
    /** @deprecated @removed */
    public const SSL_CIPHER = 59;
    /** @deprecated @removed */
    public const SSL_CA_CERT = 60;
    /** @deprecated @removed */
    public const SSL_ENGINE_INIT_FAILED = 66;
    /** @deprecated @removed */
    public const SSL_CA_CERT_BAD_FILE = 77;
    /** @deprecated @removed */
    public const SSL_SHUTDOWN_FAILED = 80;
    /** @deprecated @removed */
    public const SSL_CRL_BAD_FILE = 82;
    /** @deprecated @removed */
    public const SSL_ISSUER_ERROR = 83;

    // FTP
    /** @deprecated @removed */
    public const FTP_WEIRD_SERVER_REPLY = 8;
    /** @deprecated @removed */
    public const FTP_ACCESS_DENIED = 9;
    /** @deprecated @removed */
    public const FTP_ACCEPT_FAILED = 10;
    /** @deprecated @removed */
    public const FTP_WEIRD_PASS_REPLY = 11;
    /** @deprecated @removed */
    public const FTP_ACCEPT_TIMEOUT = 12;
    /** @deprecated @removed */
    public const FTP_WEIRD_PASV_REPLY = 13;
    /** @deprecated @removed */
    public const FTP_WEIRD_227_FORMAT = 14;
    /** @deprecated @removed */
    public const FTP_CANT_GET_HOST = 15;
    /** @deprecated @removed */
    public const FTP_COULD_NOT_SET_TYPE = 17;
    /** @deprecated @removed */
    public const FTP_COULD_NOT_RETR_FILE = 19;
    /** @deprecated @removed */
    public const FTP_QUOTE_ERROR = 21;
    /** @deprecated @removed */
    public const FTP_UPLOAD_FAILED = 25;
    /** @deprecated @removed */
    public const FTP_PORT_FAILED = 30;
    /** @deprecated @removed */
    public const FTP_COULD_NOT_USE_REST = 31;
    /** @deprecated @removed */
    public const FTP_SSL_FAILED = 64;
    /** @deprecated @removed */
    public const FTP_PRET_FAILED = 84;
    /** @deprecated @removed */
    public const FTP_BAD_FILE_LIST = 87;
    /** @deprecated @removed */
    public const FTP_CHUNK_FAILED = 88;

    // TFTP
    /** @deprecated @removed */
    public const TFTP_NOT_FOUND = 68;
    /** @deprecated @removed */
    public const TFTP_PERM = 69;
    /** @deprecated @removed */
    public const TFTP_REMOTE_DISK_FULL = 70;
    /** @deprecated @removed */
    public const TFTP_ILLEGAL = 71;
    /** @deprecated @removed */
    public const TFTP_UNKNOWN_ID = 72;
    /** @deprecated @removed */
    public const TFTP_REMOTE_FILE_EXISTS = 73;
    /** @deprecated @removed */
    public const TFTP_NO_SUCH_USER = 74;

    // SSH
    /** @deprecated @removed */
    public const SSH_ERROR = 79;

    // LDAP
    /** @deprecated @removed */
    public const LDAP_CANNOT_BIND = 38;
    /** @deprecated @removed */
    public const LDAP_SEARCH_FAILED = 39;
    /** @deprecated @removed */
    public const LDAP_INVALID_URL = 62;

    // Telnet
    /** @deprecated @removed */
    public const TELNET_OPTION_SYNTAX = 49;

    // RTSP
    /** @deprecated @removed */
    public const RTSP_CSEQ_ERROR = 85;
    /** @deprecated @removed */
    public const RTSP_SESSION_ERROR = 86;

    public static function validateValue(int &$value): bool
    {
        Check::range($value, 100, 999);

        return true;
    }

    public static function getValueRegexp(): string
    {
        return '[1-9][0-9]{2}';
    }

}
