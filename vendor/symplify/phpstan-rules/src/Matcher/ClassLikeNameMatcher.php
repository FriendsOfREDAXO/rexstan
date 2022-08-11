<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Matcher;

use Nette\Utils\Strings;

/**
 * @see \Symplify\PHPStanRules\Tests\Matcher\ClassLikeNameMatcherTest
 */
final class ClassLikeNameMatcher
{
    /**
     * @see https://regex101.com/r/uIS9Je/1
     * @var string
     */
    private const REGEX_FOR_WILDCARD_TO_REGEX = '#\*{1,2}|\?|[\\\^$.[\]|():+{}=!<>\-\#]#';

    public function isClassLikeNameMatchedAgainstPattern(string $classLikeName, string $namespaceWildcardPattern): bool
    {
        $regex = Strings::replace($namespaceWildcardPattern, self::REGEX_FOR_WILDCARD_TO_REGEX, static function (array $matches) : string {
            switch ($matches[0]) {
                case '**':
                    return '.*';
                case '*':
                    return '[^\\\\]*';
                case '?':
                    return '[^\\\\]';
                default:
                    return '\\' . $matches[0];
            }
        });

        return (bool) Strings::match($classLikeName, '#^' . $regex . '$#s');
    }
}
