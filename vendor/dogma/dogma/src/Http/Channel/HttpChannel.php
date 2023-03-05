<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http\Channel;

use Dogma\Http\Curl\CurlHelper;
use Dogma\Http\HttpRequest;
use Dogma\Http\HttpResponse;
use Dogma\StrictBehaviorMixin;
use function abs;
use function array_keys;
use function array_shift;
use function count;
use function curl_multi_add_handle;
use function curl_multi_getcontent;
use function is_array;
use function is_int;
use function is_string;
use function range;
use function time;

/**
 * HTTP channel for multiple similar requests.
 */
class HttpChannel
{
    use StrictBehaviorMixin;

    /** @var HttpChannelManager */
    private $manager;

    /** @var HttpRequest */
    private $requestPrototype;

    /** @var int */
    private $priority = 1;

    /** @var int */
    private $threadLimit = 10;

    /** @var int */
    private $lastIndex = 0;

    /** @var bool */
    private $initiated = false;

    /** @var bool */
    private $stopped = false;

    /** @var int|bool */
    private $paused = false;

    /** @var string[]|string[][] (int|string $name => $data) */
    private $queue = [];

    /** @var string[]|string[][] (int|string $name => $data) */
    private $running = [];

    /** @var HttpResponse[] */
    private $finished = [];

    /** @var mixed[] (int|string $name => $context) */
    private $contexts = [];

    /** @var callable|null */
    private $responseHandler;

    /** @var callable|null */
    private $redirectHandler;

    /** @var callable|null */
    private $errorHandler;

    public function __construct(HttpRequest $requestPrototype, ?HttpChannelManager $manager = null)
    {
        $this->requestPrototype = $requestPrototype;

        if ($manager === null) {
            $manager = new HttpChannelManager();
            $manager->addChannel($this);
        }
        $this->manager = $manager;

        if ($requestPrototype->getHeaderParser() === null) {
            $requestPrototype->setHeaderParser($manager->getHeaderParser());
        }
    }

    public function getRequestPrototype(): HttpRequest
    {
        return $this->requestPrototype;
    }

    /**
     * Set callback handler for every response (even an error)
     * @param callable $responseHandler (Response $response, Channel $channel, string $name)
     */
    public function setResponseHandler(callable $responseHandler): void
    {
        $this->responseHandler = $responseHandler;
    }

    /**
     * Set separate callback handler for redirects. ResponseHandler will no longer handle these.
     * @param callable $redirectHandler (Response $response, Channel $channel, string $name)
     */
    public function setRedirectHandler(callable $redirectHandler): void
    {
        $this->redirectHandler = $redirectHandler;
    }

    /**
     * Set separate callback handler for errors. ResponseHandler will no longer handle these.
     * @param callable $errorHandler (Response $response, Channel $channel, string $name)
     */
    public function setErrorHandler(callable $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = abs($priority);
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setThreadLimit(int $threads): void
    {
        $this->threadLimit = abs($threads);
    }

    public function getThreadLimit(): int
    {
        return $this->threadLimit;
    }

    // jobs ------------------------------------------------------------------------------------------------------------

    /**
     * Run a new job immediately and wait for the response.
     * @param string|string[] $data
     * @param mixed $context
     * @return HttpResponse|null
     */
    public function fetchJob($data, $context = null): ?HttpResponse
    {
        $name = $this->addJob($data, $context, null, true);

        return $this->fetchByName($name);
    }

    /**
     * Run a new job immediately. Don't wait for response.
     * @param string|string[] $data
     * @param mixed $context
     * @param string|int $name
     * @return string|int
     */
    public function runJob($data, $context = null, $name = null)
    {
        return $this->addJob($data, $context, $name, true);
    }

    /**
     * Add new job to channel queue.
     * @param string|string[]|mixed $data
     * @param mixed $context
     * @param string|int $name
     * @return string|int
     */
    public function addJob($data, $context = null, $name = null, bool $forceStart = false)
    {
        if (!is_string($data) && !is_array($data)) {
            throw new HttpChannelException('Illegal job data. Job data can be either string or array.');
        }

        if (is_string($name) || is_int($name)) {
            $this->queue[$name] = $data;

        } elseif ($name === null) {
            $name = ++$this->lastIndex;
            $this->queue[$name] = $data;

        } else {
            throw new HttpChannelException('Illegal job name. Job name can be only a string or an integer.');
        }

        if ($context !== null) {
            $this->contexts[$name] = $context;
        }

        if ($forceStart) {
            $this->startJob($name);
        } else {
            $this->manager->startJobs();
        }

        return $name;
    }

    /**
     * Add more jobs to a channel. Array indexes are job names if they are strings.
     * @param string[]|string[][] $jobs
     * @param mixed $context
     */
    public function addJobs(array $jobs, $context = null): void
    {
        $useKeys = array_keys($jobs) !== range(0, count($jobs) - 1);

        foreach ($jobs as $name => $data) {
            $this->addJob($data, $context, $useKeys ? $name : null);
        }
    }

    public function getRunningJobCount(): int
    {
        return count($this->running);
    }

    /**
     * Decide if channel can start a job.
     */
    public function canStartJob(): bool
    {
        return !$this->stopped
            && !$this->isPaused()
            && $this->queue !== []
            && count($this->running) < $this->threadLimit;
    }

    /**
     * Start a request in CURL. Called by HttpChannelManager
     * @internal
     * @param string|int|null $name
     */
    public function startJob($name = null): void
    {
        if (!$this->canStartJob()) {
            return;
        }

        if ($name === null) {
            $name = array_keys($this->queue);
            $name = $name[0];
        }

        if (!$this->initiated) {
            $this->requestPrototype->init();
            $this->initiated = true;
        }

        $request = clone $this->requestPrototype;
        $request->setData($this->queue[$name]);

        if (!empty($this->contexts[$name])) {
            $request->setContext($this->contexts[$name]);
            unset($this->contexts[$name]);
        }

        $request->prepare();
        $handler = $request->getHandler();
        $error = curl_multi_add_handle($this->manager->getHandler(), $handler);
        if ($error !== 0) {
            $name = CurlHelper::getCurlMultiErrorName($error);

            throw new HttpChannelException("CURL error when adding a job: $name", $error);
        }

        $this->running[$name] = $this->queue[$name];
        unset($this->queue[$name]);
        $this->manager->jobStarted($handler, $this, $name, $request);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Called by HttpChannelManager.
     * @internal
     * @param string|int $name
     * @param mixed[] $multiInfo
     */
    public function jobFinished($name, array $multiInfo, HttpRequest $request): void
    {
        unset($this->running[$name]);
        $data = curl_multi_getcontent($multiInfo['handle']);

        $response = $request->createResponse($data, $multiInfo['result']);
        $this->finished[$name] = $response;

        if ($this->errorHandler !== null && $response->getStatus()->isError()) {
            ($this->errorHandler)($response, $this, $name);
            unset($this->finished[$name]);

        } elseif ($this->redirectHandler !== null && $response->getStatus()->isRedirect()) {
            ($this->redirectHandler)($response, $this, $name);
            unset($this->finished[$name]);

        } elseif ($this->responseHandler !== null) {
            ($this->responseHandler)($response, $this, $name);
            unset($this->finished[$name]);
        }
    }

    public function fetch(): ?HttpResponse
    {
        if (!empty($this->finished)) {
            return array_shift($this->finished);
        }

        // start one job immediately
        if (empty($this->running)) {
            if (empty($this->queue)) {
                return null;
            }

            $this->startJob();
            $this->manager->read();
        }

        // potentially endless loop, if something goes wrong (always set request timeouts!)
        // todo: add timeout or retries
        while (empty($this->finished)) {
            $this->manager->read();
        }

        return array_shift($this->finished);
    }

    /**
     * @param string|int $name
     * @return HttpResponse
     */
    public function fetchByName($name): HttpResponse
    {
        if (!isset($this->queue[$name]) && !isset($this->running[$name]) && !isset($this->finished[$name])) {
            throw new HttpChannelException("Job named '$name' was not found.");
        }

        if (isset($this->finished[$name])) {
            $response = $this->finished[$name];
            unset($this->finished[$name]);
            return $response;
        }

        // start job immediately
        if (isset($this->queue[$name])) {
            $this->startJob($name);
            $this->manager->read();
        }

        // potentially endless loop, if something goes wrong (always set request timeouts!)
        /// add timeout or retries
        while (!isset($this->finished[$name])) {
            $this->manager->read();
        }

        $response = $this->finished[$name];
        unset($this->finished[$name]);
        return $response;
    }

    /**
     * Check if channel or a job is finished.
     * @param string|int|null $name
     * @return bool
     */
    public function isFinished($name = null): bool
    {
        if ($name === null) {
            return empty($this->running) && empty($this->queue);
        } else {
            return isset($this->finished[$name]);
        }
    }

    /**
     * Wait till all jobs are finished.
     */
    public function finish(): void
    {
        while (!$this->isFinished()) {
            $this->manager->read();
        }
    }

    public function stop(): void
    {
        $this->stopped = true;
    }

    public function isStopped(): bool
    {
        return $this->stopped;
    }

    public function pause(int $seconds = 0): void
    {
        if ($seconds) {
            $this->paused = time() + $seconds;
        } else {
            $this->paused = true;
        }
    }

    public function isPaused(): bool
    {
        if (is_int($this->paused) && $this->paused <= time()) {
            $this->paused = false;
        }

        return (bool) $this->paused;
    }

    public function resume(): void
    {
        $this->stopped = false;
        $this->paused = false;
    }

    public function read(): void
    {
        $this->manager->read();
    }

}
