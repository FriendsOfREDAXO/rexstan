<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: DNT TSV ATT PROTO Proto UIDH deviceid uidh ua att tsv dnt

namespace Dogma\Http;

use Dogma\Enum\PartialStringEnum;
use function array_map;
use function explode;
use function implode;
use function strtolower;

class HttpHeader extends PartialStringEnum
{

    // IETF
    public const ACCEPT = 'Accept';
    public const ACCEPT_CHARSET = 'Accept-Charset';
    public const ACCEPT_DATETIME = 'Accept-Datetime';
    public const ACCEPT_ENCODING = 'Accept-Encoding';
    public const ACCEPT_LANGUAGE = 'Accept-Language';
    public const ACCEPT_PATCH = 'Accept-Patch';
    public const ACCEPT_RANGES = 'Accept-Ranges';
    public const ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    public const AGE = 'Age';
    public const ALLOW = 'Allow';
    public const ALT_SVC = 'Alt-Svc';
    public const AUTHORIZATION = 'Authorization';
    public const CACHE_CONTROL = 'Cache-Control';
    public const CONNECTION = 'Connection';
    public const COOKIE = 'Cookie';
    public const CONTENT_DISPOSITION = 'Content-Disposition';
    public const CONTENT_ENCODING = 'Content-Encoding';
    public const CONTENT_LANGUAGE = 'Content-Language';
    public const CONTENT_LENGTH = 'Content-Length';
    public const CONTENT_LOCATION = 'Content-Location';
    public const CONTENT_MD5 = 'Content-MD5';
    public const CONTENT_RANGE = 'Content-Range';
    public const CONTENT_SECURITY_POLICY = 'Content-Security-Policy';
    public const CONTENT_TYPE = 'Content-Type';
    public const DATE = 'Date';
    public const DNT = 'DNT';
    public const EXPECT = 'Expect';
    public const ET = 'ET';
    public const ETAG = 'ETag';
    public const EXPIRES = 'Expires';
    public const FORWARDED = 'Forwarded';
    public const FROM = 'From';
    public const HOST = 'Host';
    public const IF_MATCH = 'If-Match';
    public const IF_MODIFIED_SINCE = 'If-Modified-Since';
    public const IF_NONE_MATCH = 'If-None-Match';
    public const IF_RANGE = 'If-Range';
    public const IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
    public const LAST_MODIFIED = 'Last-Modified';
    public const LINK = 'Link';
    public const LOCATION = 'Location';
    public const MAX_FORWARDS = 'Max-Forwards';
    public const ORIGIN = 'Origin';
    public const P3P = 'P3P';
    public const PRAGMA = 'Pragma';
    public const PROXY_AUTHENTICATE = 'Proxy-Authenticate';
    public const PROXY_AUTHORIZATION = 'Proxy-Authorization';
    public const PUBLIC_KEY_PINS = 'Public-Key-Pins';
    public const RANGE = 'Range';
    public const REFERER = 'Referer';
    public const REFRESH = 'Refresh';
    public const RETRY_AFTER = 'Retry-After';
    public const SAVE_DATA = 'Save-Data';
    public const SERVER = 'Server';
    public const SET_COOKIE = 'Set-Cookie';
    public const STATUS = 'Status';
    public const STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';
    public const TE = 'TE';
    public const TRAILER = 'Trailer';
    public const TRANSFER_ENCODING = 'Transfer-Encoding';
    public const TSV = 'TSV';
    public const USER_AGENT = 'User-Agent';
    public const UPGRADE = 'Upgrade';
    public const VARY = 'Vary';
    public const VIA = 'Via';
    public const WARNING = 'Warning';
    public const WWW_AUTHENTICATE = 'WWW-Authenticate';
    public const X_FRAME_OPTIONS = 'X-Frame-Options';

    // non-standard
    public const CONTENT_CHARSET = 'Content-Charset';
    public const FRONT_END_HTTPS = 'Front-End-Https';
    public const HTTP_VERSION = 'Http-Version';
    public const PROXY_CONNECTION = 'Proxy-Connection';
    public const UPGRADE_INSECURE_REQUESTS = 'Upgrade-Insecure-Requests';
    public const X_ATT_DEVICE_ID = 'X-ATT-DeviceId';
    public const X_CONTENT_DURATION = 'X-Content-Duration';
    public const X_CONTENT_SECURITY_POLICY = 'X-Content-Security-Policy';
    public const X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';
    public const X_CORRELATION_ID = 'X-Correlation-ID';
    public const X_CSRF_TOKEN = 'X-Csrf-Token';
    public const X_DO_NOT_TRACK = 'X-Do-Not-Track';
    public const X_FORWARDED_FOR = 'X-Forwarded-For';
    public const X_FORWARDED_HOST = 'X-Forwarded-Host';
    public const X_FORWARDED_PROTO = 'X-Forwarded-Proto';
    public const X_HTTP_METHOD_OVERRIDE = 'X-Http-Method-Override';
    public const X_POWERED_BY = 'X-Powered-By';
    public const X_REQUEST_ID = 'X-Request-ID';
    public const X_REQUESTED_WITH = 'X-Requested-With';
    public const X_UA_COMPATIBLE = 'X-UA-Compatible';
    public const X_UIDH = 'X-UIDH';
    public const X_XSS_PROTECTION = 'X-XSS-Protection';
    public const X_WAP_PROFILE = 'X-Wap-Profile';
    public const X_WEBKIT_CSP = 'X-WebKit-CSP';

    /** @var string[] */
    private static $exceptions = [
        'et' => 'ET',
        'etag' => 'ETag',
        'te' => 'TE',
        'dnt' => 'DNT',
        'tsv' => 'TSV',
        'x-att-deviceid' => 'X-ATT-DeviceId',
        'x-correlation-id' => 'X-Correlation-ID',
        'x-request-id' => 'X-Request-ID',
        'x-ua-compatible' => 'X-UA-Compatible',
        'x-uidh' => 'X-UIDH',
        'x-xss-protection' => 'X-XSS-Protection',
        'x-webkit-csp' => 'X-WebKit-CSP',
        'www-authenticate' => 'WWW-Authenticate',
    ];

    public static function normalizeName(string $name): string
    {
        $name = strtolower($name);

        return self::$exceptions[$name]
            ?? implode('-', array_map('ucfirst', explode('-', $name)));
    }

    public static function validateValue(string &$value): bool
    {
        $value = self::normalizeName($value);

        return parent::validateValue($value);
    }

    public static function getValueRegexp(): string
    {
        return '(?:X-)?[A-Z][a-z]+(?:[A-Z][a-z]+)*|' . implode('|', self::$exceptions);
    }

}
