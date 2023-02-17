<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Dom;

use Dogma\Re;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\InvalidDateTimeException;
use DOMAttr;
use DOMCdataSection;
use DOMComment;
use DOMDocument;
use DOMElement;
use DOMException;
use DOMNode;
use DOMNodeList;
use DOMProcessingInstruction;
use DOMText;
use DOMXPath;
use function count;
use function in_array;
use function is_array;
use function is_numeric;
use function is_scalar;
use function is_string;
use function strtoupper;
use function substr;

/**
 * Simple query engine based on XPath 1.0
 */
class QueryEngine
{
    use StrictBehaviorMixin;

    /** @var DOMXPath */
    private $xpath;

    /** @var string[] (string $pattern => string $replacement) */
    private $translations = [
        // index: [n]
        '/\\[([0-9]+)..([0-9]+)\\]/' => '[position() >= $1 and position() <= $2]', // [m..n]
        '/\\[..([0-9]+)\\]/' => '[position() <= $1]', // [..n]
        '/\\[([0-9]+)..\\]/' => '[position() >= $1]', // [n..]
        '/\\[-([0-9]+)\\]/' => '[position() = last() + 1 - $1]', // nth from end: [-n]
        '/\\[:first\\]/' => '[1]', // [:first]
        '/\\[:last\\]/' => '[last()]', // [:last]
        '/\\[:even\\]/' => '[position() mod 2]', // [:even]
        '/\\[:odd\\]/' => '[not(position() mod 2)]', // [:odd]
        '/\\[:only\\]/' => '[position() = 1 and position() = last()]', // [:only]

        // class: [.foo]
        '/\\[\\.([A-Za-z0-9_-]+)\\]/' => '[contains(concat(" ", normalize-space(@class), " "), " $1 ")]',

        // id: [#foo]
        '/\\[#([A-Za-z0-9_-]+)\\]/' => '[@id = "$1"]',

        // name: ["foo"]
        '/\\["([^"]+)"\\]/' => '[@name = "$1"]',
        "/\\['([^']+)'\\]/" => "[@name = '$1']",

        // content equals: [="foo"]
        '/\\[="([^"]+)"\\]/' => '[string() = "$1"]',
        "/\\[='([^']+)'\\]/" => "[string() = '$1']",

        // content matches: [~"/foo/i"]
        '/\\[~"([^"]+)"\\]/' => "[php:functionString('Dogma\\Dom\\QueryEngine::match', string(), \"$1\")]",
        "/\\[~'([^']+)'\\]/" => "[php:functionString('Dogma\\Dom\\QueryEngine::match', string(), '$1')]",

        // label: [label("foo")]
        '/\\[label\\("([^"]+)"\\)\\]/' => '[
                (ancestor::label[normalize-space() = "$1"]) or
                (@id = ancestor::form/descendant::label[normalize-space() = "$1"]/@for) or
                ((@type = "submit" or @type = "reset" or @type = "button") and @value = "$1") or
                (@type = "button" and normalize-space() = "$1")]',
        "/\\[label\\('([^']+)'\\)\\]/" => '[
                (ancestor::label[normalize-space() = \'$1\']) or
                (@id = ancestor::form/descendant::label[normalize-space() = \'$1\']/@for) or
                ((@type = "submit" or @type = "reset" or @type = "button") and @value = \'$1\') or
                (@type = "button" and normalize-space() = \'$1\')]',

        // axes 'next' and 'previous'
        '#/previous::([A-Za-z0-9_-]+)#' => '/preceding-sibling::$1[last()]',
        '#/next::([A-Za-z0-9_-]+)#' => '/following-sibling::$1[1]',

        // table shortcuts
        '/:headrow/' => "tr[name(..) = 'thead' or (name(..) = 'table' and not(../thead) and position() = 1)]",
        '/:bodyrow/' => "tr[name(..) = 'tbody' or (name(..) = 'table' and not(../tbody) and (../thead or position() != 1))]",
        '/:footrow/' => "tr[name(..) = 'tfoot' or (name(..) = 'table' and not(../tfoot) and position() = last()]",
        '/:cell/' => "*[name() = 'td' or name() = 'th']",

        // function shortcuts
        '/int\\(/' => 'number(.//',
        '/float\\(/' => 'number(.//',
        '/bool\\(/' => 'php:functionString("Dogma\\Dom\\QueryEngine::bool", .//',
        '/date\\(/' => 'php:functionString("Dogma\\Dom\\QueryEngine::date", .//',
        '/datetime\\(/' => 'php:functionString("Dogma\\Dom\\QueryEngine::datetime", .//',
        '/match\\(/' => 'php:functionString("Dogma\\Dom\\QueryEngine::match", .//',
        '/replace\\(/' => 'php:functionString("Dogma\\Dom\\QueryEngine::replace", .//',

        // jQuery-like shortcuts
        '/:input/' => "*[name() = 'input' or name() = 'textarea' or name() = 'select' or name() = 'button']",
        '/:file/' => "input[@type = 'file']",
        '/:button/' => "*[name() = 'button' or (name() = 'input' and @type = 'button')]",
        '/:submit/' => "input[@type = 'submit']",
        '/:reset/' => "input[@type = 'reset']",
        '/:image/' => "input[@type = 'image']",
        '/:radio/' => "input[@type = 'radio']",
        '/:checkbox/' => "input[@type = 'checkbox']",
        '/:text/' => "*[name() = 'textarea'
                or (name() = 'input' and (@type = 'text' or @type= 'hidden' or not(@type)))]",
        '/:password/' => "input[@type = 'password']",

        '/:header/' => "*[name() = 'h1' or name() = 'h2' or name() = 'h3' or name() = 'h4' or name() = 'h5' or name() = 'h6']",
        '/:link/' => "a[@href]",
        '/:anchor/' => "*[@id or (name() = 'a' and @name)]",
    ];

    /** @var string[] */
    private $nativeFunctions = [
        'position',
        'last',
        'count',
        'id',
        'name',
        'local-name',
        'namespace-uri',

        'string',
        'concat',
        'starts-with',
        'contains',
        'substring',
        'substring-before',
        'substring-after',
        'string-length',
        'normalize-space',
        'translate',

        'boolean',
        'not',
        'true',
        'false',
        'lang',
        'number',
        'floor',
        'ceiling',
        'round',
        'sum',

        'function',
        'functionString',

        'match',
        'replace',
        'date',
        'datetime',
        'bool',
    ];

    /** @var string[] */
    private $userFunctions = [
        'Dogma\\Dom\\QueryEngine::match',
        'Dogma\\Dom\\QueryEngine::replace',
        'Dogma\\Dom\\QueryEngine::date',
        'Dogma\\Dom\\QueryEngine::datetime',
        'Dogma\\Dom\\QueryEngine::bool',
    ];

    public function __construct(DOMDocument $dom)
    {
        $this->xpath = new DOMXPath($dom);

        $this->xpath->registerNamespace('php', 'http://php.net/xpath');
        $this->xpath->registerPhpFunctions($this->userFunctions);
    }

    public function registerFunction(string $name, string $alias = '', bool $expectNode = false): void
    {
        if (!$alias) {
            $alias = $name;
        }
        if (in_array($alias, $this->nativeFunctions, true)) {
            throw new QueryEngineException("Function '$alias' is already registered.");
        }

        if ($expectNode) {
            $this->translations['/' . $alias . '\\(/'] = "php:function('$name', .//";
        } else {
            $this->translations['/' . $alias . '\\(/'] = "php:functionString('$name', .//";
        }
        $this->nativeFunctions[] = $alias;
        $this->userFunctions[] = $name;

        $this->xpath->registerPhpFunctions($this->userFunctions);
    }

    public function registerNamespace(string $prefix, string $uri): void
    {
        $this->xpath->registerNamespace($prefix, $uri);
    }

    /**
     * Find nodes
     * @param Element|DOMNode|null $context
     * @return NodeList
     */
    public function find(string $query, $context = null): NodeList
    {
        $path = $this->translateQuery($query, (bool) $context);
        if ($context !== null) {
            if ($context instanceof Element) {
                $context = $context->getElement();
            }
            /** @var DOMNodeList<DOMNode>|false $list */
            $list = $this->xpath->query($path, $context);
        } else {
            /** @var DOMNodeList<DOMNode>|false $list */
            $list = $this->xpath->query($path);
        }
        if ($list === false) {
            throw new QueryEngineException("Invalid XPath query: '$path', translated from: '$query'.");
        }

        return new NodeList($list, $this);
    }

    /**
     * Find one node
     * @param Element|DOMNode|null $context
     * @return DOMNode|Element|null
     */
    public function findOne(string $query, $context = null)
    {
        $path = $this->translateQuery($query, (bool) $context);
        if ($context !== null) {
            if ($context instanceof Element) {
                $context = $context->getElement();
            }
            /** @var DOMNodeList<DOMNode>|false $list */
            $list = $this->xpath->query($path, $context);
        } else {
            /** @var DOMNodeList<DOMNode>|false $list */
            $list = $this->xpath->query($path);
        }
        if ($list === false) {
            throw new QueryEngineException("Invalid XPath query: '$path', translated from: '$query'.");
        }

        if (!count($list)) {
            return null;
        }

        /** @var DOMNode $item */
        $item = $list->item(0);

        return $this->wrap($item);
    }

    /**
     * Evaluate a query
     * @param Element|DOMNode|null $context
     * @return string|int|float|bool|Date|DateTime|null
     */
    public function evaluate(string $query, $context = null)
    {
        $path = $this->translateQuery($query);

        if ($context !== null) {
            if ($context instanceof Element) {
                $context = $context->getElement();
            }
            $value = $this->xpath->evaluate($path, $context);
        } else {
            $value = $this->xpath->evaluate($path);
        }

        if ($value === false) {
            throw new QueryEngineException("Invalid XPath query: '$path', translated from: '$query'.");
        }

        if (substr($query, 0, 5) === 'date(') {
            return $value ? new Date($value) : null;
        } elseif (substr($query, 0, 9) === 'datetime(') {
            return $value ? new DateTime($value) : null;
        } elseif (substr($query, 0, 4) === 'int(') {
            if (!is_numeric($value)) {
                return null;
            }

            return (int) $value;
        } elseif ($value !== null && substr($query, 0, 5) === 'bool(') {
            if ($value === '') {
                return null;
            }

            return (bool) $value;
        } else {
            return $value;
        }
    }

    /**
     * Extract values from paths defined by one or more queries
     * @param string|string[]|string[][] $queries
     * @param Element|DOMNode|null $context
     * @return int|float|bool|string|Date|DateTime|mixed[]|null
     */
    public function extract($queries, $context = null)
    {
        if (is_string($queries)) {
            return $this->extractPath($queries, $context);
        }

        $value = [];
        foreach ($queries as $i => $query) {
            if (is_array($query)) {
                $value[$i] = $this->extract($query, $context);
            } else {
                $value[$i] = $this->extractPath($query, $context);
            }
        }
        return $value;
    }

    // internals -------------------------------------------------------------------------------------------------------

    /**
     * @param Element|DOMNode|null $context
     * @return int|float|bool|string|Date|DateTime|null
     */
    private function extractPath(string $query, $context = null)
    {
        if (Re::match($query, '/^[a-zA-Z0-9_-]+\\(/')) {
            $node = $this->evaluate($query, $context);
        } else {
            $node = $this->findOne($query, $context);
        }

        if (is_scalar($node) || $node instanceof Date || $node instanceof DateTime) {
            return $node;
        } elseif (!$node) {
            return null;
        } elseif ($node instanceof DOMAttr) {
            return $node->value;
        } elseif ($node instanceof DOMText) {
            return $node->wholeText;
        } elseif ($node instanceof DOMCdataSection || $node instanceof DOMComment || $node instanceof DOMProcessingInstruction) {
            return $node->data;
        } else {
            return $node->textContent;
        }
    }

    private function translateQuery(string $query, bool $context = false): string
    {
        if ($context === true) {
            if ($query[0] === '/') {
                $query = '.' . $query;
            } elseif ($query[0] !== '.') {
                $query = './/' . $query;
            }
        } elseif ($context === false) {
            if ($query[0] !== '/') {
                $query = '//' . $query;
            }
        }

        $query = Re::replace($query, $this->translations);

        // adding ".//" before element names
        $query = Re::replace($query, '@(?<=\\()([0-9A-Za-z_:]+)(?!\\()@', './/$1');

        // fixing ".//" before function names
        $query = Re::replace($query, '@\\.//([0-9A-Za-z_:-]+)\\(@', '$1(');

        $nativeFunctions = $this->nativeFunctions;
        $userFunctions = $this->userFunctions;
        $query = Re::replace(
            $query,
            '/(?<![A-Za-z0-9_-])([A-Za-z0-9_-]+)\\(/',
            static function ($match) use ($nativeFunctions, $userFunctions) {
                if (in_array($match[1], $nativeFunctions, true)) {
                    return $match[1] . '(';
                } elseif (in_array($match[1], $userFunctions, true)) {
                    return "php:functionString('{$match[1]}', ";
                } else {
                    throw new DOMException("XPath compilation failure: Functions '{$match[1]}' is not enabled.");
                }
            }
        );

        return $query;
    }

    /**
     * Wrap element in DomElement object
     * @return Element|DOMNode
     */
    private function wrap(DOMNode $node)
    {
        if ($node instanceof DOMElement) {
            return new Element($node, $this);
        } else {
            return $node;
        }
    }

    // extension functions ---------------------------------------------------------------------------------------------

    /**
     * Test with regular expression and return matching string
     * @return string|null
     */
    public static function match(string $string, string $pattern): ?string
    {
        $match = Re::match($string, $pattern);
        if ($match) {
            return $match[0];
        }

        return null;
    }

    public static function replace(string $string, string $pattern, string $replacement): string
    {
        return Re::replace($string, $pattern, $replacement);
    }

    public static function date(string $string, string $format = 'Y-m-d'): string
    {
        if ($string === '') {
            return '';
        }

        try {
            $date = DateTime::createFromFormat($format, $string);
        } catch (InvalidDateTimeException $e) {
            throw new QueryEngineException("Cannot create DateTime object from '$string' using format '$format'.", 0, $e);
        }

        return $date->format('Y-m-d');
    }

    public static function datetime(string $string, string $format = 'Y-m-d H:i:s'): string
    {
        if ($string === '') {
            return '';
        }

        try {
            $date = DateTime::createFromFormat($format, $string);
        } catch (InvalidDateTimeException $e) {
            throw new QueryEngineException("Cannot create DateTime object from '$string' using format '$format'.", 0, $e);
        }

        return $date->format('Y-m-d H:i:s');
    }

    public static function bool(string $string, string $true = 'true', string $false = 'false'): ?bool
    {
        $string = strtoupper($string);
        if ($string === $false) {
            return false;
        }
        if ($string === $true) {
            return true;
        }

        return null;
    }

}
