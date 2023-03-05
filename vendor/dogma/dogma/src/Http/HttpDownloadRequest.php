<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

use Dogma\Io\File;
use const CURLOPT_BINARYTRANSFER;
use const CURLOPT_FILE;

/**
 * File download request.
 */
class HttpDownloadRequest extends HttpRequest
{

    /** @var File */
    private $file;

    /**
     * @return HttpFileResponse
     */
    public function execute(): HttpResponse
    {
        return parent::execute();
    }

    /**
     * Called by Channel.
     * @internal
     */
    public function prepare(): void
    {
        parent::prepare();

        $this->file = File::createTemporaryFile();

        $this->setOption(CURLOPT_FILE, $this->file->getHandle());
        $this->setOption(CURLOPT_BINARYTRANSFER, true);
    }

    /**
     * Called by Channel.
     * @internal
     * @return HttpFileResponse
     */
    public function createResponse(?string $response, int $error): HttpResponse
    {
        $info = $this->getInfo();
        $status = $this->getResponseStatus($error, $info);

        return new HttpFileResponse($status, $this->file, $this->responseHeaders, $info, $this->context, $this->headerParser);
    }

}
