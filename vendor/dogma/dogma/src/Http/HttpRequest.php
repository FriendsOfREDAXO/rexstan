<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

use CurlHandle;
use Dogma\Http\Curl\CurlHelper;
use Dogma\InvalidValueException;
use Dogma\Io\FileMode;
use Dogma\NonSerializableMixin;
use Dogma\Re;
use Dogma\StrictBehaviorMixin;
use RuntimeException;
use const CURLAUTH_ANYSAFE;
use const CURLOPT_BINARYTRANSFER;
use const CURLOPT_CONNECTTIMEOUT;
use const CURLOPT_CONNECTTIMEOUT_MS;
use const CURLOPT_COOKIE;
use const CURLOPT_COOKIEFILE;
use const CURLOPT_COOKIEJAR;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_HEADERFUNCTION;
use const CURLOPT_HTTPAUTH;
use const CURLOPT_HTTPGET;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_INFILE;
use const CURLOPT_INFILESIZE;
use const CURLOPT_MAXREDIRS;
use const CURLOPT_NOBODY;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_PROXY;
use const CURLOPT_PROXYPORT;
use const CURLOPT_PROXYUSERPWD;
use const CURLOPT_PUT;
use const CURLOPT_REFERER;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_SSL_VERIFYHOST;
use const CURLOPT_SSL_VERIFYPEER;
use const CURLOPT_SSLKEY;
use const CURLOPT_SSLKEYPASSWD;
use const CURLOPT_SSLKEYTYPE;
use const CURLOPT_TIMEOUT;
use const CURLOPT_TIMEOUT_MS;
use const CURLOPT_URL;
use const CURLOPT_USERAGENT;
use const CURLOPT_USERPWD;
use const PREG_SET_ORDER;
use function array_keys;
use function array_merge;
use function count;
use function curl_copy_handle;
use function curl_errno;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt;
use function error_clear_last;
use function error_get_last;
use function fopen;
use function implode;
use function is_array;
use function is_int;
use function is_string;
use function preg_match_all;
use function strlen;
use function strtolower;
use function substr;
use function trim;
use function urlencode;

/**
 * HTTP request. Holds a CURL resource.
 */
class HttpRequest
{
    use StrictBehaviorMixin;
    use NonSerializableMixin;

    /** @var CurlHandle|resource */
    private $curl;

    /** @var callable|null */
    private $init;

    /** @var string */
    private $url;

    /** @var string */
    private $method = HttpMethod::GET;

    /** @var string[] */
    private $headers = [];

    /** @var string[] */
    private $cookies = [];

    /** @var mixed[] */
    private $variables = [];

    /** @var string */
    private $content;

    /** @var HttpHeaderParser|null */
    protected $headerParser;

    /** @var mixed */
    protected $context;

    /** @var string[] */
    protected $responseHeaders = [];

    public function __construct(?string $url = null, ?string $method = null)
    {
        error_clear_last();
        /** @var resource|false $curl */
        $curl = curl_init();
        if ($curl === false) {
            /** @var string[] $error */
            $error = error_get_last();

            throw new HttpRequestException('Cannot initialize curl. Error: ' . $error['message']);
        }
        $this->curl = $curl;

        if ($url !== null) {
            $this->setUrl($url);
        }
        if ($method !== null) {
            $this->setMethod($method);
        }

        $this->setHeaderFunction();
    }

    /**
     * Called by Channel.
     * @internal
     */
    public function init(): void
    {
        if ($this->init !== null) {
            if (!($this->init)($this)) {
                throw new HttpRequestException('Request initialization failed!');
            }
            $this->init = null;
        }
    }

    public function setInit(callable $init): void
    {
        $this->init = $init;
    }

    public function setHeaderParser(HttpHeaderParser $headerParser): void
    {
        $this->headerParser = $headerParser;
    }

    public function getHeaderParser(): ?HttpHeaderParser
    {
        return $this->headerParser;
    }

    /**
     * @param mixed $data
     */
    public function setContext($data): void
    {
        $this->context = $data;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    // basic operations ------------------------------------------------------------------------------------------------

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function appendUrl(string $url): void
    {
        $this->setUrl($this->url . $url);
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        if ($data !== null) {
            $this->dispatch($data);
        }
    }

    /**
     * @param string|mixed[] $data
     */
    protected function dispatch($data): void
    {
        if (is_string($data)) {
            $this->setContent($data);

        } elseif (is_array($data)) {
            $this->setVariables($data);

        } else {
            throw new HttpRequestException('Job data may be only a string or array!');
        }
    }

    public function setContent(string $data): void
    {
        if ($this->method === HttpMethod::POST || $this->method === HttpMethod::PUT) {
            $this->content = $data;
        } else {
            $this->appendUrl($data);
        }
    }

    /**
     * Set URL or POST variables. Can be called repeatedly.
     * @param mixed[] $variables
     */
    public function setVariables(array $variables): void
    {
        $this->variables = array_merge($this->variables, $variables);
    }

    public function setMethod(string $method): void
    {
        $this->method = strtolower($method);

        switch ($this->method) {
            case HttpMethod::GET:
                $this->setOption(CURLOPT_HTTPGET, true);
                break;
            case HttpMethod::HEAD:
                $this->setOption(CURLOPT_NOBODY, true);
                break;
            case HttpMethod::POST:
                $this->setOption(CURLOPT_POST, true);
                break;
            case HttpMethod::PUT:
                $this->setOption(CURLOPT_PUT, true);
                break;
            case HttpMethod::PATCH:
            case HttpMethod::DELETE:
            case HttpMethod::TRACE:
            case HttpMethod::OPTIONS:
            case HttpMethod::CONNECT:
                $this->setOption(CURLOPT_CUSTOMREQUEST, $this->method);
                break;
            default:
                throw new HttpRequestException("Unknown method '$this->method'!");
        }
    }

    /**
     * @param string|int $name option name or CURLOPT_ constant
     * @param mixed $value
     */
    public function setOption($name, $value): void
    {
        if (is_string($name)) {
            $number = CurlHelper::getCurlOptionNumber($name);
            if ($number === null) {
                throw new HttpRequestException("Unknown CURL option '$name'!");
            }
        } elseif (!is_int($name)) {
            throw new HttpRequestException('Option name must be a string or a CURLOPT_* constant!');
        } else {
            $number = $name;
        }

        if (!curl_setopt($this->curl, $number, $value)) {
            throw new HttpRequestException('Invalid CURL option.');
        }
    }

    // connection options ----------------------------------------------------------------------------------------------

    /**
     * @param float|int $timeout
     * @param float|int $connectTimeout
     */
    public function setTimeout($timeout, $connectTimeout = null): void
    {
        if ($timeout < 1) {
            $this->setOption(CURLOPT_TIMEOUT_MS, (int) ($timeout / 1000));
        } else {
            $this->setOption(CURLOPT_TIMEOUT, (int) $timeout);
        }

        if ($connectTimeout === null) {
            return;
        }

        if ($connectTimeout < 1) {
            $this->setOption(CURLOPT_CONNECTTIMEOUT_MS, (int) ($connectTimeout / 1000));
        } else {
            $this->setOption(CURLOPT_CONNECTTIMEOUT, (int) $connectTimeout);
        }
    }

    public function setFollowRedirects(bool $follow = true, ?int $max = null): void
    {
        $this->setOption(CURLOPT_FOLLOWLOCATION, $follow);
        if ($max !== null) {
            $this->setOption(CURLOPT_MAXREDIRS, $max);
        }
    }

    public function addHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    /**
     * @param string[] $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function setReferrer(string $url): void
    {
        $this->setOption(CURLOPT_REFERER, $url);
    }

    public function setUserAgent(string $string): void
    {
        $this->setOption(CURLOPT_USERAGENT, $string);
    }

    // cookies, authentication & secure connection ---------------------------------------------------------------------

    public function setCookieFile(string $inputFile, ?string $outputFile = null): void
    {
        if ($inputFile) {
            $this->setOption(CURLOPT_COOKIEFILE, $inputFile);
        }
        if ($outputFile) {
            $this->setOption(CURLOPT_COOKIEJAR, $outputFile);
        }
    }

    /**
     * @param string[] $cookies
     */
    public function setCookies(array $cookies): void
    {
        $this->cookies = $cookies;
    }

    public function addCookie(string $name, string $value): void
    {
        $this->cookies[$name] = $value;
    }

    public function setCredentials(string $userName, string $password, int $method = CURLAUTH_ANYSAFE): void
    {
        $this->setOption(CURLOPT_USERPWD, $userName . ':' . $password);
        $this->setOption(CURLOPT_HTTPAUTH, $method);
    }

    public function setSslKey(string $keyFile, string $password, string $keyType = 'PEM'): void
    {
        $this->setOption(CURLOPT_SSLKEY, $keyFile);
        $this->setOption(CURLOPT_SSLKEYPASSWD, $password);
        $this->setOption(CURLOPT_SSLKEYTYPE, $keyType);
    }

    public function setVerifySslCertificates(bool $verifyPeer = true, bool $verifyHost = true): void
    {
        $this->setOption(CURLOPT_SSL_VERIFYPEER, $verifyPeer);
        $this->setOption(CURLOPT_SSL_VERIFYHOST, $verifyHost);
    }

    public function setProxy(?string $proxy = null, int $port = 3128, ?string $userName = null, ?string $password = null): void
    {
        if ($proxy === null) {
            $this->setOption(CURLOPT_PROXY, null);
            $this->setOption(CURLOPT_PROXYPORT, null);
            $this->setOption(CURLOPT_PROXYUSERPWD, null);
        } else {
            $this->setOption(CURLOPT_PROXY, $proxy);
            $this->setOption(CURLOPT_PROXYPORT, $port);
            $this->setOption(CURLOPT_PROXYUSERPWD, ($userName && $password) ? $userName . ':' . $password : null);
        }
    }

    // output handling -------------------------------------------------------------------------------------------------

    public function execute(): HttpResponse
    {
        $this->init();
        $this->prepare();
        /** @var string|false $response */
        $response = curl_exec($this->curl);
        $error = curl_errno($this->curl);
        if ($response === false) {
            $response = null;
        }

        return $this->createResponse($response, $error);
    }

    /**
     * Called by RequestManager.
     * @internal
     */
    public function prepare(): void
    {
        $params = $this->analyzeUrl();
        if ($params || $this->variables || $this->content) {
            $this->prepareData($params);
        }

        $this->setOption(CURLOPT_URL, $this->url);
        $this->setOption(CURLOPT_RETURNTRANSFER, true);

        if ($this->headers) {
            $this->prepareHeaders();
        }
        if ($this->cookies) {
            $this->prepareCookies();
        }
    }

    /**
     * Called by RequestManager.
     * @internal
     * @return CurlHandle|resource
     */
    public function getHandler()
    {
        return $this->curl;
    }

    /**
     * Called by RequestManager.
     * @internal
     * @return HttpResponse
     */
    public function createResponse(?string $response, int $error): HttpResponse
    {
        $info = $this->getInfo();
        $status = $this->getResponseStatus($error, $info);

        return new HttpResponse($status, $response, $this->responseHeaders, $info, $this->context, $this->headerParser);
    }

    // internals -------------------------------------------------------------------------------------------------------

    /**
     * @return mixed[]
     */
    protected function getInfo(): array
    {
        $info = curl_getinfo($this->curl);
        if ($info === false) {
            throw new HttpRequestException('Info cannot be obtained from CURL.');
        }

        return $info;
    }

    /**
     * @param mixed[] $info
     * @return HttpOrCurlStatus
     */
    protected function getResponseStatus(int $error, array $info): HttpOrCurlStatus
    {
        if ($error !== 0) {
            $status = HttpOrCurlStatus::get($error);
        } else {
            try {
                $status = HttpOrCurlStatus::get($info['http_code']);
            } catch (InvalidValueException $e) {
                $status = HttpOrCurlStatus::get(HttpOrCurlStatus::UNKNOWN_RESPONSE_CODE);
            }
        }
        if ($status->isFatalError()) {
            throw new HttpRequestException("Fatal error occurred during request execution: {$status->getConstantName()}", $status->getValue());
        }

        return $status;
    }

    private function prepareHeaders(): void
    {
        $headers = [];
        foreach ($this->headers as $key => $value) {
            if (is_int($key)) {
                $headers[] = $value;
            } else {
                $headers[] = $key . ': ' . $value;
            }
        }

        $this->setOption(CURLOPT_HTTPHEADER, $headers);
    }

    private function prepareCookies(): void
    {
        $cookie = '';
        foreach ($this->cookies as $name => $value) {
            $cookie .= "; $name=$value";
        }

        $this->setOption(CURLOPT_COOKIE, substr($cookie, 2));
    }

    /**
     * @param mixed[] $variables
     */
    private function prepareData(array $variables): void
    {
        if ($variables) {
            $this->fillUrlVariables($variables);
        }

        if ($this->content !== null && $this->variables !== []) {
            throw new HttpRequestException('Both data content and variables are set. Only one at a time can be sent!');
        }

        if ($this->content) {
            $this->prepareUpload();
        }

        if ($this->variables === []) {
            return;
        }

        if ($this->method === HttpMethod::POST) {
            $this->preparePost();
        } else {
            $names = array_keys($this->variables);
            throw new HttpRequestException(
                'Redundant request variable' . (count($names) > 1 ? 's' : '') . ' \'' . implode("', '", $names) . '\' in request data.'
            );
        }
    }

    private function prepareUpload(): void
    {
        $this->setOption(CURLOPT_BINARYTRANSFER, true);

        if ($this->content[0] === '@') {
            $fileName = substr($this->content, 1);
            $file = fopen($fileName, FileMode::OPEN_READ);
            if (!$file) {
                throw new HttpRequestException("Could not open file $fileName.");
            }

            $this->setOption(CURLOPT_INFILE, $file);
            $this->setOption(CURLOPT_INFILESIZE, strlen($this->content));

        } else {
            $this->setOption(CURLOPT_POSTFIELDS, $this->content);
        }
    }

    private function preparePost(): void
    {
        foreach ($this->variables as $name => $value) {
            if ($value === null) {
                throw new HttpRequestException("POST parameter '$name' must be filled.");
            }
        }
        $this->setOption(CURLOPT_POSTFIELDS, $this->variables);
        $this->variables = [];
    }

    /**
     * @return mixed[]
     */
    private function analyzeUrl(): array
    {
        $params = [];

        if (preg_match_all('/\\W([0-9A-Za-z_]+)=%[^0-9A-Fa-f]/', $this->url, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $m) {
                $params[$m[1]] = true;
            }
        }
        if (preg_match_all('/{%([0-9A-Za-z_]+)}/', $this->url, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $m) {
                $params[$m[1]] = false;
            }
        }

        return $params;
    }

    /**
     * @param mixed[] $vars
     */
    private function fillUrlVariables(array $vars): void
    {
        foreach ($vars as $name => $short) {
            if (!isset($this->variables[$name])) {
                throw new HttpRequestException("URL variable '$name' is missing in request data.");
            }

            $value = urlencode($this->variables[$name]);
            if ($short) {
                $this->url = Re::replace($this->url, "/(?<=\\W$name=)%%(?=[^0-9A-Fa-f])/", $value);
            } else {
                $this->url = Re::replace($this->url, "/{%%$name}/", $value);
            }

            unset($this->variables[$name]);
        }
    }

    final public function __clone()
    {
        $handle = curl_copy_handle($this->curl);
        if ($handle === false) {
            throw new RuntimeException('Could not copy Curl handle.');
        }
        $this->curl = $handle;

        // need to set the closure to $this again
        $this->setHeaderFunction();
    }

    private function setHeaderFunction(): void
    {
        $this->setOption(CURLOPT_HEADERFUNCTION, function ($curl, string $header) {
            $length = strlen($header);
            $header = trim($header);
            if ($header !== '') {
                $this->responseHeaders[] = $header;
            }

            return $length;
        });
    }

}
