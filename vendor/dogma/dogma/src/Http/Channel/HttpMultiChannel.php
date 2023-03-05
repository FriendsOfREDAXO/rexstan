<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http\Channel;

use Dogma\Http\HttpResponse;
use Dogma\StrictBehaviorMixin;
use function array_keys;
use function count;
use function is_array;
use function is_int;
use function is_string;
use function range;
use function spl_object_hash;

class HttpMultiChannel
{
    use StrictBehaviorMixin;

    /** @var HttpChannel[] */
    private $channels;

    /** @var string[] */
    private $channelIds;

    /** @var int */
    private $lastIndex = -1;

    /** @var array<array<int|string>> ($subJobName => ($channelName => $jobName)) */
    private $queue = [];

    /** @var array<array<HttpResponse>> ($jobName => ($channelName => $response)) */
    private $finished = [];

    /** @var callable|null */
    private $responseHandler;

    /** @var callable|null */
    private $redirectHandler;

    /** @var callable|null */
    private $errorHandler;

    /** @var callable|null */
    private $dispatcher;

    /**
     * @param HttpChannel[] $channels
     */
    public function __construct(array $channels)
    {
        $this->channels = $channels;

        foreach ($channels as $channelName => $channel) {
            $this->channelIds[spl_object_hash($channel)] = $channelName;
            $channel->setResponseHandler(function (HttpResponse $response, HttpChannel $channel, string $subJobName): void {
                $this->responseHandler($response, $channel, $subJobName);
            });
        }
    }

    public function responseHandler(HttpResponse $response, HttpChannel $channel, string $subJobName): void
    {
        $channelId = spl_object_hash($channel);
        $channelName = $this->channelIds[$channelId];
        $jobName = $this->queue[$subJobName][$channelName];
        $this->finished[$jobName][$channelName] = $response;

        unset($this->queue[$subJobName][$channelName]);
        if (empty($this->queue[$subJobName])) {
            unset($this->queue[$subJobName]);
        }

        if (count($this->finished[$jobName]) === count($this->channels)) {
            $this->jobFinished($jobName);
        }
    }

    /**
     * @param string|int $jobName
     */
    private function jobFinished($jobName): void
    {
        $error = $redirect = false;
        foreach ($this->finished[$jobName] as $response) {
            if ($response->getStatus()->isError()) {
                $error = true;
            }
            if ($response->getStatus()->isRedirect()) {
                $redirect = true;
            }
        }

        if ($this->errorHandler !== null && $error) {
            ($this->errorHandler)($this->finished[$jobName], $this);
            unset($this->finished[$jobName]);

        } elseif ($this->redirectHandler !== null && $redirect) {
            ($this->redirectHandler)($this->finished[$jobName], $this);
            unset($this->finished[$jobName]);

        } elseif ($this->responseHandler !== null) {
            ($this->responseHandler)($this->finished[$jobName], $this);
            unset($this->finished[$jobName]);
        }
    }

    /**
     * @return HttpChannel[]
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * Set callback handler for every response (even an error)
     * @param callable $responseHandler (Response $response, HttpChannel $channel, string $name)
     */
    public function setResponseHandler(callable $responseHandler): void
    {
        $this->responseHandler = $responseHandler;
    }

    /**
     * Set separate callback handler for redirects. ResponseHandler will no longer handle these.
     * @param callable $redirectHandler (Response $response, HttpChannel $channel, string $name)
     */
    public function setRedirectHandler(callable $redirectHandler): void
    {
        $this->redirectHandler = $redirectHandler;
    }

    /**
     * Set separate callback handler for errors. ResponseHandler will no longer handle these.
     * @param callable $errorHandler (Response $response, HttpChannel $channel, string $name)
     */
    public function setErrorHandler(callable $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param callable $function (mixed $data, HttpChannel[] $channels)
     */
    public function setDispatchFunction(callable $function): void
    {
        $this->dispatcher = $function;
    }

    /**
     * Add new job to channel queue.
     * @param string|mixed[] $data
     * @param mixed $context
     * @param string|int|mixed $name
     * @return string|int
     */
    public function addJob($data, $context = null, $name = null)
    {
        if ($name === null) {
            $name = ++$this->lastIndex;
        } elseif (!is_string($name) && !is_int($name)) {
            throw new HttpChannelException('Illegal job name. Job name can be only a string or an integer.');
        }

        if ($this->dispatcher !== null) {
            $subJobs = ($this->dispatcher)($data, $this->channels);
        } else {
            $subJobs = $this->dispatch($data);
        }

        foreach ($subJobs as $channel => $job) {
            $subJobName = $this->channels[$channel]->addJob($job, $context);
            $this->queue[$subJobName][$channel] = $name;
        }

        return $name;
    }

    /**
     * Add more jobs to a channel. Array indexes are job names if they are strings.
     * @param mixed[] $jobs
     * @param mixed $context
     */
    public function addJobs(array $jobs, $context = null): void
    {
        $useKeys = array_keys($jobs) !== range(0, count($jobs) - 1);

        foreach ($jobs as $name => $data) {
            $this->addJob($data, $context, $useKeys ? $name : null);
        }
    }

    /**
     * Run a new job and wait for the response.
     * @param string|mixed[] $data
     * @param mixed $context
     * @return HttpResponse[]|null
     */
    public function fetchJob($data, $context = null): ?array
    {
        $jobs = $this->dispatch($data);
        foreach ($jobs as $channel => $job) {
            $jobs[$channel] = $this->channels[$channel]->runJob($job, $context, null);
        }

        $responses = [];
        foreach ($jobs as $channel => $subJobName) {
            $responses[$channel] = $this->channels[$channel]->fetchByName($subJobName);
        }

        return $responses;
    }

    /**
     * @param string|int $name
     * @return HttpResponse[]|null
     */
    public function fetch($name = null): ?array
    {
        if ($name !== null) {
            return $this->fetchNamedJob($name);
        }

        if (empty($this->queue) && empty($this->finished)) {
            return null;
        }

        $keys = array_keys($this->channels);
        do {
            $this->channels[$keys[0]]->read();
            foreach ($this->finished as $jobName => $fin) {
                if (count($fin) === count($this->channels)) {
                    unset($this->finished[$jobName]);
                    return $fin;
                }
            }
        } while (true);
    }

    /**
     * @param string|int $name
     * @return HttpResponse[]
     */
    private function fetchNamedJob($name): array
    {
        if (!isset($this->queue[$name]) && !isset($this->finished[$name])) {
            throw new HttpChannelException("Job named '$name' was not found.");
        }

        if (isset($this->finished[$name]) && count($this->finished[$name]) === count($this->channels)) {
            $responses = $this->finished[$name];
            unset($this->finished[$name]);

            return $responses;
        }

        // seek sub-jobs
        foreach ($this->queue as $subJobName => $channel) {
            foreach ($channel as $channelName => $jobName) {
                if ($jobName === $name) {
                    $this->responseHandler($this->channels[$channelName]->fetchByName($subJobName), $this->channels[$channelName], $subJobName);
                }
            }
        }

        $response = $this->finished[$name];
        unset($this->finished[$name]);

        return $response;
    }

    /**
     * Wait till all jobs are finished.
     */
    public function finish(): void
    {
        foreach ($this->channels as $channel) {
            $channel->finish();
        }
    }

    /**
     * Check if all channels or a channel or a job are finished.
     */
    public function isFinished(): bool
    {
        foreach ($this->channels as $channel) {
            if (!$channel->isFinished()) {
                return false;
            }
        }

        return true;
    }

    public function read(): void
    {
        foreach ($this->channels as $channel) {
            $channel->read();
        }
    }

    /**
     * Job data dispatch function. Splits up data for sub-jobs (sub-channels). Override if needed.
     * @param string|mixed[] $data
     * @return mixed[]
     */
    protected function dispatch($data): array
    {
        if (is_string($data)) {
            // default - send copy to all channels
            $jobs = [];
            foreach ($this->channels as $name => $channel) {
                $jobs[$name] = $data;
            }
        } elseif (is_array($data)) {
            // default - array is indexed by channel name
            return $data;
        } else {
            throw new HttpChannelException('Illegal job data. Job data can be either string or array.');
        }

        return $jobs;
    }

}
