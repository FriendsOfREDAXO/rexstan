<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Dom;

use Dogma\NotImplementedException;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use DOMDocument;
use DOMElement;
use DOMNode;
use ReturnTypeWillChange;
use function libxml_clear_errors;
use function libxml_get_last_error;
use function libxml_use_internal_errors;
use function preg_match;
use function trim;

class Document extends DOMDocument
{

    /** @var QueryEngine */
    private $engine;

    /**
     * XML or HTML content or file path prefixed with '@'
     */
    public function __construct(?string $document = null, string $version = '1.0', string $encoding = 'utf-8')
    {
        parent::__construct($version, $encoding);

        if (!$document) {
            return;
        }

        if ($document[0] === '@') {
            /// from file
            throw new NotImplementedException('File ');
        } else {
            if (preg_match('/<!DOCTYPE\\s+HTML/i', $document)) {
                $this->loadHtml($document);
            } elseif (preg_match('/\\s*<\\?xml/i', $document)) {
                $this->loadXml($document);
            } else {
                $this->loadHtml($document);
            }
        }

        $this->engine = new QueryEngine($this);
    }

    public function setQueryEngine(QueryEngine $engine): void
    {
        $this->engine = $engine;
    }

    public function getQueryEngine(): QueryEngine
    {
        return $this->engine;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param string $source
     * @param int $options
     * @return true
     */
    public function loadXml($source, $options = 0): bool
    {
        $previousState = libxml_use_internal_errors(true);
        libxml_clear_errors();
        if (!parent::loadXML($source, $options)) {
            $this->error('Cannot load XML document', $previousState);
        }
        libxml_use_internal_errors($previousState);

        return true;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param string $source
     * @param int $options
     * @return true
     */
    public function loadHtml($source, $options = 0): bool
    {
        $previousState = libxml_use_internal_errors(true);
        libxml_clear_errors();
        if (!parent::loadHTML($source, $options)) {
            $this->error('Cannot load XML document', $previousState);
        }
        libxml_use_internal_errors($previousState);

        return true;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param string $fileName
     * @param int $options
     * @return true
     */
    public function loadHtmlFile($fileName, $options = 0): bool
    {
        $previousState = libxml_use_internal_errors(true);
        libxml_clear_errors();
        if (!parent::loadHTMLFile($fileName)) {
            $this->error('Cannot load XML document', $previousState);
        }
        libxml_use_internal_errors($previousState);

        return true;
    }

    private function error(string $message, bool $previousState): void
    {
        $error = libxml_get_last_error();
        libxml_use_internal_errors($previousState);
        if ($error !== false) {
            throw new DomException($message . ': ' . trim($error->message) . ' on line #' . $error->line, $error->code);
        } else {
            throw new DomException($message . ': unknown error', 0);
        }
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param string $id
     * @return Element|DOMNode|null
     */
    #[ReturnTypeWillChange]
    public function getElementById($id)
    {
        $element = parent::getElementById($id);

        return $element ? $this->wrap($element) : null;
    }

    public function find(string $xpath): NodeList
    {
        return $this->engine->find($xpath);
    }

    /**
     * @return Element|DOMNode|null
     */
    public function findOne(string $xpath)
    {
        return $this->engine->findOne($xpath);
    }

    /**
     * @return int|float|bool|string|Date|DateTime|null
     */
    public function evaluate(string $xpath)
    {
        return $this->engine->evaluate($xpath);
    }

    /**
     * @param string|string[] $target
     * @return int|float|bool|string|Date|DateTime|mixed[]|null
     */
    public function extract($target)
    {
        return $this->engine->extract($target);
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function dump(): void
    {
        Dumper::dump($this);
    }

    /**
     * @return Element|DOMNode
     */
    private function wrap(DOMNode $node)
    {
        if ($node instanceof DOMElement) {
            return new Element($node, $this->engine);
        } else {
            return $node;
        }
    }

}
