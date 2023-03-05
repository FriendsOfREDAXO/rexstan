<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http\Channel;

use CurlMultiHandle;
use Dogma\Http\Curl\CurlHelper;
use Dogma\Http\HttpHeaderParser;
use Dogma\Http\HttpRequest;
use Dogma\NonCloneableMixin;
use Dogma\NonSerializableMixin;
use Dogma\Obj;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Provider\CurrentTimeProvider;
use const CURLM_CALL_MULTI_PERFORM;
use const PHP_INT_MIN;
use function abs;
use function count;
use function curl_multi_close;
use function curl_multi_exec;
use function curl_multi_info_read;
use function curl_multi_init;
use function curl_multi_remove_handle;
use function curl_multi_select;
use function spl_object_hash;

/**
 * Manages parallel requests over multiple HTTP channels.
 */
class HttpChannelManager
{
    use StrictBehaviorMixin;
    use NonSerializableMixin;
    use NonCloneableMixin;

    /** @var CurlMultiHandle|resource */
    private $handler;

    /** @var int maximum threads for all channels */
    private $threadLimit = 20;

    /** @var float sum of priorities of all channels */
    private $sumPriorities = 0.0;

    /** @var HttpChannel[] */
    private $channels = [];

    /** @var mixed[] (int $resourceId => array($channelId, $jobName, $request)) */
    private $resources = [];

    /** @var HttpHeaderParser|null */
    private $headerParser;

    public function __construct(?HttpHeaderParser $headerParser = null)
    {
        $this->headerParser = $headerParser;
        $handler = curl_multi_init();
        if ($handler === false) {
            throw new HttpChannelException('Cannot initialize CURL multi-request.');
        }
        $this->handler = $handler;
    }

    public function __destruct()
    {
        curl_multi_close($this->handler);
    }

    /**
     * @return CurlMultiHandle|resource
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Set maximum of request to run in parallel.
     */
    public function setThreadLimit(int $threads): void
    {
        $this->threadLimit = abs($threads);
    }

    public function addChannel(HttpChannel $channel): void
    {
        $this->channels[spl_object_hash($channel)] = $channel;
        $this->updatePriorities();
        $this->startJobs();
    }

    private function updatePriorities(): void
    {
        $sum = 0;
        foreach ($this->channels as $channel) {
            $sum += $channel->getPriority();
        }
        $this->sumPriorities = $sum;
    }

    public function read(): void
    {
        $this->waitForResult();
        $this->readResults();
    }

    /**
     * Wait for any request to finish. Blocking. Returns count of available results.
     */
    private function waitForResult(): int
    {
        $run = false;
        foreach ($this->channels as $channel) {
            if (!$channel->isFinished()) {
                $run = true;
            }
        }
        if (!$run) {
            return 0;
        }

        do {
            $active = $this->exec();
            $ready = curl_multi_select($this->handler);

            if ($ready > 0) {
                return $ready;
            }
        } while ($active > 0 && $ready !== -1);

        return 0;
    }

    public function exec(): int
    {
        do {
            $error = curl_multi_exec($this->handler, $active);
            if ($error > 0) {
                throw new HttpChannelException('CURL error when starting jobs: ' . CurlHelper::getCurlMultiErrorName($error), $error);
            }
        } while ($error === CURLM_CALL_MULTI_PERFORM);

        return $active;
    }

    /**
     * Read finished results from CURL.
     */
    private function readResults(): void
    {
        while ($info = curl_multi_info_read($this->handler)) {
            $resourceId = Obj::objectId($info['handle']);
            [$channelId, $name, $request] = $this->resources[$resourceId];
            $channel = &$this->channels[$channelId];

            $error = curl_multi_remove_handle($this->handler, $info['handle']);
            if ($error) {
                throw new HttpChannelException('CURL error when reading results: ' . CurlHelper::getCurlMultiErrorName($error), $error);
            }

            $channel->jobFinished($name, $info, $request);
            unset($this->resources[$resourceId]);
        }
        $this->startJobs();
    }

    /**
     * Start requests according to their priorities.
     */
    public function startJobs(): void
    {
        while ($channelId = $this->selectChannel()) {
            $this->channels[$channelId]->startJob();
        }
        $this->exec();
    }

    /**
     * @return int|string|null
     */
    private function selectChannel()
    {
        if (count($this->resources) >= $this->threadLimit) {
            return null;
        }

        $selected = null;
        $ratio = PHP_INT_MIN;
        foreach ($this->channels as $channelId => &$channel) {
            if ($selected === $channelId) {
                continue;
            }
            if (!$channel->canStartJob()) {
                continue;
            }

            $channelRatio = ($channel->getPriority() / $this->sumPriorities)
                - ($channel->getRunningJobCount() / $this->threadLimit);

            if ($channelRatio > $ratio) {
                $selected = $channelId;
                $ratio = $channelRatio;
            }
        }

        return $selected;
    }

    /**
     * Save data for later use.
     * @param resource $resource
     * @param string|int $name
     */
    public function jobStarted($resource, HttpChannel $channel, $name, HttpRequest $request): void
    {
        $this->resources[Obj::objectId($resource)] = [spl_object_hash($channel), $name, $request];
    }

    public function getHeaderParser(): HttpHeaderParser
    {
        if ($this->headerParser === null) {
            $this->headerParser = new HttpHeaderParser(new CurrentTimeProvider());
        }
        return $this->headerParser;
    }

}
