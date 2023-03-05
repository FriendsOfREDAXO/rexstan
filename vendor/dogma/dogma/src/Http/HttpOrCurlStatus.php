<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md'; distributed with this source code
 */

// spell-check-ignore: CONV REQD MALFORMAT RECV CRL PASV RETR PRET CSEQ STOR ACCEPTTIMOUT EPSV recv
// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingBefore

namespace Dogma\Http;

use Dogma\Arr;
use Dogma\Check;
use Dogma\Enum\PartialIntEnum;
use function str_replace;
use function strtolower;
use function substr;
use function ucwords;

/**
 * HTTP 1.1 response status codes and CURL error codes
 *
 * Why they are together?
 * Because whether a fault happened on the way in the destination should not be the concern of your application.
 * Storing them separately adds complexity with no benefits.
 */
class HttpOrCurlStatus extends PartialIntEnum
{

    public const S100_CONTINUE = 100;
    public const S101_SWITCHING_PROTOCOLS = 101;
    public const S102_PROCESSING = 102;
    public const S103_CHECKPOINT = 103;

    public const S200_OK = 200;
    public const S201_CREATED = 201;
    public const S202_ACCEPTED = 202;
    public const S203_NON_AUTHORITATIVE_INFORMATION = 203;
    public const S204_NO_CONTENT = 204;
    public const S205_RESET_CONTENT = 205;
    public const S206_PARTIAL_CONTENT = 206;
    public const S207_MULTI_STATUS = 207;
    public const S208_ALREADY_REPORTED = 208;
    public const S226_IM_USER = 226;

    public const S300_MULTIPLE_CHOICES = 300;
    public const S301_MOVED_PERMANENTLY = 301;
    public const S302_FOUND = 302;
    public const S303_SEE_OTHER = 303;
    public const S304_NOT_MODIFIED = 304;
    public const S305_USE_PROXY = 305;
    public const S306_SWITCH_PROXY = 306;
    public const S307_TEMPORARY_REDIRECT = 307;
    public const S308_RESUME_INCOMPLETE = 308;

    public const S400_BAD_REQUEST = 400;
    public const S401_UNAUTHORIZED = 401;
    public const S402_PAYMENT_REQUIRED = 402;
    public const S403_FORBIDDEN = 403;
    public const S404_NOT_FOUND = 404;
    public const S405_METHOD_NOT_ALLOWED = 405;
    public const S406_NOT_ACCEPTABLE = 406;
    public const S407_PROXY_AUTHENTICATION_REQUIRED = 407;
    public const S408_REQUEST_TIMEOUT = 408;
    public const S409_CONFLICT = 409;
    public const S410_GONE = 410;
    public const S411_LENGTH_REQUIRED = 411;
    public const S412_PRECONDITION_FAILED = 412;
    public const S413_REQUESTED_ENTITY_TOO_LARGE = 413;
    public const S414_REQUEST_URI_TOO_LONG = 414;
    public const S415_UNSUPPORTED_MEDIA_TYPE = 415;
    public const S416_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    public const S417_EXPECTATION_FAILED = 417;
    public const S418_IM_A_TEAPOT = 418;
    public const S419_AUTHENTICATION_TIMEOUT = 419;
    public const S420_ENHANCE_YOUR_CALM = 420;
    public const S421_MISDIRECTED_REQUEST = 421;
    public const S422_UNPROCESSABLE_ENTITY = 422;
    public const S423_LOCKED = 423;
    public const S424_FAILED_DEPENDENCY = 424;
    /** @deprecated ? */
    public const S425_UNORDERED_COLLECTION = 425;
    public const S425_TOO_EARLY = 425;
    public const S426_UPGRADE_REQUIRED = 426;
    public const S428_PRECONDITION_REQUIRED = 428;
    public const S429_TOO_MANY_REQUESTS = 429;
    public const S431_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    public const S449_RETRY_WITH = 449;
    public const S450_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS = 450;
    public const S451_UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    public const S500_INTERNAL_SERVER_ERROR = 500;
    public const S501_NOT_IMPLEMENTED = 501;
    public const S502_BAD_GATEWAY = 502;
    public const S503_SERVICE_UNAVAILABLE = 503;
    public const S504_GATEWAY_TIMEOUT = 504;
    public const S505_HTTP_VERSION_NOT_SUPPORTED = 505;
    public const S506_VARIANT_ALSO_NEGOTIATES = 506;
    public const S507_INSUFFICIENT_STORAGE = 507;
    public const S508_LOOP_DETECTED = 508;
    public const S509_BANDWIDTH_LIMIT_EXCEEDED = 509;
    public const S510_NOT_EXTENDED = 510;
    public const S511_NETWORK_AUTHENTICATION_REQUIRED = 511;

    // system & CURL internals
    public const FAILED_INIT           = 2;  // Very early initialization code failed. This is likely to be an internal error or problem; or a resource problem where something fundamental could not get done at init time.
    public const NOT_BUILT_IN          = 4;  // (CURLE_URL_MALFORMAT_USER) A requested feature; protocol or option was not found built-in in this libcurl due to a build-time decision. This means that a feature or option was not enabled or explicitly disabled when libcurl was built and in order to get it to function you have to get a rebuilt libcurl.
    public const OUT_OF_MEMORY         = 27; // A memory allocation request failed. This is serious badness and things are severely screwed up if this ever occurs.
    public const HTTP_POST_ERROR       = 34; // This is an odd error that mainly occurs due to internal confusion.
    public const FUNCTION_NOT_FOUND    = 41; // Function not found. A required zlib function was not found.
    public const BAD_FUNCTION_ARGUMENT = 43; // Internal error. A function was called with a bad parameter.
    public const SEND_FAIL_REWIND      = 65; // When doing a send operation curl had to rewind the data to retransmit; but the rewinding operation failed.
    public const CONV_FAILED           = 75; // Character conversion failed.
    public const CONV_REQD             = 76; // Caller must register conversion callbacks.

    // file system
    public const READ_ERROR            = 26; // There was a problem reading a local file or an error returned by the read callback.
    public const WRITE_ERROR           = 23; // An error occurred when writing received data to a local file; or an error was returned to libcurl from a write callback.
    public const COULD_NOT_READ_FILE   = 37; // A file given with FILE:// could not be opened. Most likely because the file path does not identify an existing file. Did you check file permissions?
    public const FILE_SIZE_EXCEEDED    = 63; // Maximum file size exceeded.

    // user error
    public const UNSUPPORTED_PROTOCOL  = 1;  // The URL you passed to libcurl used a protocol that this libcurl does not support. The support might be a compile-time option that you did not use; it can be a misspelled protocol string or just a protocol libcurl has no code for.
    public const URL_MALFORMAT         = 3;  // The URL was not properly formatted.
    public const HTTP_RETURNED_ERROR   = 22; // (CURLE_HTTP_NOT_FOUND) This is returned if CURLOPT_FAILONERROR is set TRUE and the HTTP server returns an error code that is >= 400.
    public const BAD_DOWNLOAD_RESUME   = 36; // (CURLE_FTP_BAD_DOWNLOAD_RESUME) The download could not be resumed because the specified offset was out of the file boundary.
    public const UNKNOWN_OPTION        = 48; // (CURLE_UNKNOWN_TELNET_OPTION) An option passed to libcurl is not recognized/known. Refer to the appropriate documentation. This is most likely a problem in the program that uses libcurl. The error buffer might contain more specific information about which exact option it concerns.
    public const BAD_CONTENT_ENCODING  = 61; // Unrecognized transfer encoding.
    public const LOGIN_DENIED          = 67; // The remote server denied curl to login
    public const REMOTE_FILE_NOT_FOUND = 78; // The resource referenced in the URL does not exist.

    // network/socket
    public const COULD_NOT_RESOLVE_PROXY = 5; // Could not resolve proxy. The given proxy host could not be resolved.
    public const COULD_NOT_RESOLVE_HOST = 6; // Could not resolve host. The given remote host was not resolved.
    public const COULD_NOT_CONNECT     = 7;  // Failed to connect() to host or proxy.
    public const INTERFACE_FAILED      = 45; // (CURLE_HTTP_PORT_FAILED) Interface error. A specified outgoing interface could not be used. Set which interface to use for outgoing connections' source IP address with CURLOPT_INTERFACE.
    public const SEND_ERROR            = 55; // Failed sending network data.
    public const RECV_ERROR            = 56; // Failure with receiving network data.
    public const TRY_AGAIN             = 81; // [CURL_AGAIN] Socket is not ready for send/recv wait till it's ready and try again. This return code is only returned from curl_easy_recv(3) and curl_easy_send(3)

    // server
    public const UNKNOWN_RESPONSE_CODE = -1; // An unknown (not listed above) HTTP response code was received
    public const RANGE_ERROR           = 33; // (CURLE_HTTP_RANGE_ERROR) The server does not support or accept range requests.
    public const GOT_NOTHING           = 52; // Nothing was returned from the server; and under the circumstances; getting nothing is considered an error.

    // other
    public const PARTIAL_FILE          = 18; // A file transfer was shorter or larger than expected. This happens when the server first reports an expected transfer size; and then delivers data that does not match the previously given size.
    public const OPERATION_TIMED_OUT   = 28; // (CURLE_OPERATION_TIMEOUTED) Operation timeout. The specified time-out period was reached according to the conditions.
    public const ABORTED_BY_CALLBACK   = 42; // Aborted by callback. A callback returned "abort" to libcurl.
    public const TOO_MANY_REDIRECTS    = 47; // Too many redirects. When following redirects; libcurl hit the maximum amount. Set your limit with CURLOPT_MAXREDIRS.

    // SSL
    public const SSL_CONNECT_ERROR     = 35; // A problem occurred somewhere in the SSL/TLS handshake. You really want the error buffer and read the message there as it pinpoints the problem slightly more. Could be certificates (file formats; paths; permissions); passwords; and others.
    public const SSL_PEER_FAILED_VERIFICATION = 51; // (CURLE_SSL_PEER_CERTIFICATE) The remote server's SSL certificate or SSH md5 fingerprint was deemed not OK.
    public const SSL_ENGINE_NOT_FOUND  = 53; // The specified crypto engine was not found.
    public const SSL_ENGINE_SET_FAILED = 54; // Failed setting the selected SSL crypto engine as default!
    public const SSL_CERT_PROBLEM      = 58; // problem with the local client certificate.
    public const SSL_CIPHER            = 59; // Could not use specified cipher.
    public const SSL_CA_CERT           = 60; // Peer certificate cannot be authenticated with known CA certificates.
    public const SSL_ENGINE_INIT_FAILED = 66; // Initiating the SSL Engine failed.
    public const SSL_CA_CERT_BAD_FILE  = 77; // Problem with reading the SSL CA cert (path? access rights?)
    public const SSL_SHUTDOWN_FAILED   = 80; // Failed to shut down the SSL connection.
    public const SSL_CRL_BAD_FILE      = 82; // Failed to load CRL file
    public const SSL_ISSUER_ERROR      = 83; // Issuer check failed

    // FTP
    public const FTP_WEIRD_SERVER_REPLY = 8; // After connecting to a FTP server; libcurl expects to get a certain reply back. This error code implies that it got a strange or bad reply. The given remote server is probably not an OK FTP server.
    public const FTP_ACCESS_DENIED     = 9;  // We were denied access to the resource given in the URL. For FTP; this occurs while trying to change to the remote directory.
    public const FTP_ACCEPT_FAILED     = 10; // (CURLE_FTP_USER_PASSWORD_INCORRECT) While waiting for the server to connect back when an active FTP session is used; an error code was sent over the control connection or similar.
    public const FTP_WEIRD_PASS_REPLY  = 11; // After having sent the FTP password to the server; libcurl expects a proper reply. This error code indicates that an unexpected code was returned.
    public const FTP_ACCEPT_TIMEOUT    = 12; // (CURLE_FTP_WEIRD_USER_REPLY) During an active FTP session while waiting for the server to connect; the CURLOPT_ACCEPTTIMOUT_MS (or the internal default) timeout expired.
    public const FTP_WEIRD_PASV_REPLY  = 13; // libcurl failed to get a sensible result back from the server as a response to either a PASV or a EPSV command. The server is flawed.
    public const FTP_WEIRD_227_FORMAT  = 14; // FTP servers return a 227-line as a response to a PASV command. If libcurl fails to parse that line; this return code is passed back.
    public const FTP_CANT_GET_HOST     = 15; // An internal failure to lookup the host used for the new connection.
    public const FTP_COULD_NOT_SET_TYPE = 17; // (CURLE_FTP_COULDNT_SET_BINARY) Received an error when trying to set the transfer mode to binary or ASCII.
    public const FTP_COULD_NOT_RETR_FILE = 19; // This was either a weird reply to a 'RETR' command or a zero byte transfer complete.
    public const FTP_QUOTE_ERROR       = 21; // When sending custom "QUOTE" commands to the remote server; one of the commands returned an error code that was 400 or higher (for FTP) or otherwise indicated unsuccessful completion of the command.
    public const FTP_UPLOAD_FAILED     = 25; // (CURLE_FTP_COULDNT_STOR_FILE) Failed starting the upload. For FTP; the server typically denied the STOR command. The error buffer usually contains the server's explanation for this.
    public const FTP_PORT_FAILED       = 30; // The FTP PORT command returned error. This mostly happens when you haven't specified a good enough address for libcurl to use. See CURLOPT_FTPPORT.
    public const FTP_COULD_NOT_USE_REST = 31; // The FTP REST command returned error. This should never happen if the server is sane.
    public const FTP_SSL_FAILED        = 64; // (CURLE_FTP_SSL_FAILED) Requested FTP SSL level failed.
    public const FTP_PRET_FAILED       = 84; // The FTP server does not understand the PRET command at all or does not support the given argument. Be careful when using CURLOPT_CUSTOMREQUEST; a custom LIST command will be sent with PRET CMD before PASV as well.
    public const FTP_BAD_FILE_LIST     = 87; // Unable to parse FTP file list (during FTP wildcard downloading).
    public const FTP_CHUNK_FAILED      = 88; // Chunk callback reported error.

    // TFTP
    public const TFTP_NOT_FOUND        = 68; // File not found on TFTP server.
    public const TFTP_PERM             = 69; // Permission problem on TFTP server.
    public const TFTP_REMOTE_DISK_FULL = 70; // Out of disk space on the server.
    public const TFTP_ILLEGAL          = 71; // Illegal TFTP operation.
    public const TFTP_UNKNOWN_ID       = 72; // Unknown TFTP transfer ID.
    public const TFTP_REMOTE_FILE_EXISTS = 73; // File already exists and will not be overwritten.
    public const TFTP_NO_SUCH_USER     = 74; // This error should never be returned by a properly functioning TFTP server.

    // SSH
    public const SSH_ERROR             = 79; // [CURL_SSH] An unspecified error occurred during the SSH session.

    // LDAP
    public const LDAP_CANNOT_BIND      = 38; // LDAP cannot bind. LDAP bind operation failed.
    public const LDAP_SEARCH_FAILED    = 39; // LDAP search failed.
    public const LDAP_INVALID_URL      = 62; // Invalid LDAP URL.

    // Telnet
    public const TELNET_OPTION_SYNTAX  = 49; // A telnet option string was Illegally formatted.

    // RTSP
    public const RTSP_CSEQ_ERROR       = 85; // Mismatch of RTSP CSeq numbers.
    public const RTSP_SESSION_ERROR    = 86; // Mismatch of RTSP Session Identifiers.

    /**
     * Get formatted status name
     */
    public function getDescription(): string
    {
        $id = $this->getConstantName();
        if ($id[0] === 'S' && $id[4] === '_') {
            $id = substr($id, 5);
        }

        return ucwords(str_replace(
            ['http', 'ftp', 'ssh', 'ldap', 'tftp', 'rtsp', 'url', 'ok', '_'],
            ['HTTP', 'FTP', 'SSH', 'LDAP', 'TFTP', 'RTSP', 'URL', 'OK', ' '],
            strtolower($id)
        ));
    }

    /**
     * Is an information/handshaking HTTP response code (1xx)
     */
    public function isInfo(): bool
    {
        return $this->getValue() >= 100 && $this->getValue() < 200;
    }

    /**
     * Is a positive HTTP response code (2xx)
     */
    public function isOk(): bool
    {
        return $this->getValue() >= 200 && $this->getValue() < 300;
    }

    /**
     * Is a HTTP redirection code (3xx)
     */
    public function isRedirect(): bool
    {
        return ($this->getValue() >= 300 && $this->getValue() < 400) || $this->getValue() === self::TOO_MANY_REDIRECTS;
    }

    /**
     * Is an HTTP error response code (4xx or 5xx)
     */
    public function isHttpError(): bool
    {
        return $this->getValue() >= 400 && $this->getValue() < 600;
    }

    /**
     * Is a CURL error code
     */
    public function isCurlError(): bool
    {
        return $this->getValue() < 100;
    }

    /**
     * Is an HTTP or CURL error code
     */
    public function isError(): bool
    {
        return $this->isCurlError() || $this->isHttpError();
    }

    /**
     * Is a network connection error. Possibility of successful retry
     */
    public function isNetworkError(): bool
    {
        return Arr::contains([
            self::COULD_NOT_RESOLVE_PROXY,
            self::COULD_NOT_RESOLVE_HOST,
            self::COULD_NOT_CONNECT,
            self::SEND_ERROR, // is this network or system?
            self::RECV_ERROR, // is this network or system?
            self::TRY_AGAIN,
        ], $this->getValue());
    }

    /**
     * CURL errors which should throw an exception immediately. Something is very wrong
     */
    public function isFatalError(): bool
    {
        return Arr::contains([
            self::FAILED_INIT,
            self::OUT_OF_MEMORY,
            self::UNKNOWN_OPTION,
            self::SSL_ENGINE_NOT_FOUND,
            self::SSL_ENGINE_SET_FAILED,
            self::SSL_CERT_PROBLEM,
            self::SSL_ENGINE_INIT_FAILED,
            self::INTERFACE_FAILED,
            //self::SEND_ERROR,
            //self::RECV_ERROR,
            self::CONV_REQD,
        ], $this->getValue());
    }

    public static function validateValue(int &$value): bool
    {
        Check::range($value, 1, 999);

        return true;
    }

    public static function getValueRegexp(): string
    {
        return '[0-9]{1,3}';
    }

}
