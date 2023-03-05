<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Printer;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\PrettyPrinter\Standard;

final class DuplicatedClassMethodPrinter
{
    /**
     * @var string
     * @see https://regex101.com/r/cJZZgC/1
     */
    private const VARIABLE_REGEX = '#\$\w+[^\s]#';
    /**
     * @readonly
     * @var \PhpParser\PrettyPrinter\Standard
     */
    private $standard;

    public function __construct(Standard $standard)
    {
        $this->standard = $standard;
    }

    public function printClassMethod(ClassMethod $classMethod): string
    {
        $content = $this->standard->prettyPrint((array) $classMethod->stmts);
        return Strings::replace($content, self::VARIABLE_REGEX, static function (array $match) : string {
            return '$a';
        });
    }
}
