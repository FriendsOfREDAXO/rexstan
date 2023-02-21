<?php declare(strict_types = 1);

namespace SqlFtw\Tests;

use Dogma\Debug\Callstack;
use Dogma\Debug\Debugger;
use Dogma\Re;
use Dogma\Tester\Assert as DogmaAssert;
use SqlFtw\Formatter\Formatter;
use SqlFtw\Parser\AnalyzerException;
use SqlFtw\Parser\InvalidCommand;
use SqlFtw\Parser\Lexer;
use SqlFtw\Parser\LexerException;
use SqlFtw\Parser\Parser;
use SqlFtw\Parser\Token;
use SqlFtw\Parser\TokenList;
use SqlFtw\Parser\TokenType;
use SqlFtw\Platform\ClientSideExtension;
use SqlFtw\Platform\Platform;
use SqlFtw\Session\Session;
use SqlFtw\Sql\Command;
use function class_exists;
use function gettype;
use function implode;
use function iterator_to_array;
use function preg_replace;
use function sprintf;
use function str_replace;

/**
 * @phpstan-import-type PhpBacktraceItem from Callstack
 */
class Assert extends DogmaAssert
{

    /**
     * @return array<Token>
     */
    public static function tokens(string $sql, int $count, ?string $mode = null, ?int $extensions = null): array
    {
        $platform = Platform::get(Platform::MYSQL, '5.7');
        if ($extensions === null) {
            $extensions = ClientSideExtension::ALLOW_NUMBERED_QUESTION_MARK_PLACEHOLDERS | ClientSideExtension::ALLOW_NAMED_DOUBLE_COLON_PLACEHOLDERS;
        }
        $session = new Session($platform, null, null, null, $extensions);
        if ($mode !== null) {
            $session->setMode($session->getMode()->add($mode));
        }
        $lexer = new Lexer($session, true, true);

        $tokens = iterator_to_array($lexer->tokenize($sql));

        self::count($tokens, $count);

        return $tokens;
    }

    public static function token(Token $token, int $type, ?string $value = null, ?int $position = null): void
    {
        if ($type !== $token->type) {
            $actualDesc = implode('|', TokenType::getByValue($token->type)->getConstantNames());
            $typeDesc = implode('|', TokenType::getByValue($type)->getConstantNames());
            parent::fail(sprintf('Type of token "%s" is %s (%d) and should be %s (%d).', $token->value, $actualDesc, $token->type, $typeDesc, $type));
        }
        if ($value !== $token->value) {
            parent::fail(sprintf('Token value is "%s" (%s) and should be "%s" (%s).', $token->value, gettype($token->value), $value, gettype($value)));
        }
        if ($position !== null && $position !== $token->position) {
            parent::fail(sprintf('Token starting position is %s and should be %s.', $token->position, $position));
        }
    }

    public static function invalidToken(Token $token, int $type, string $messageRegexp, ?int $position = null): void
    {
        if ($type !== $token->type) {
            $actualDesc = implode('|', TokenType::getByValue($token->type)->getConstantNames());
            $typeDesc = implode('|', TokenType::getByValue($type)->getConstantNames());
            parent::fail(sprintf('Type of token "%s" is %s (%d) and should be %s (%d).', $token->value, $actualDesc, $token->type, $typeDesc, $type));
        }
        if (!$token->exception instanceof LexerException) {
            parent::fail(sprintf('Token value is %s (%d) and should be a LexerException.', $token->value, gettype($token->value)));
        } else {
            $message = $token->exception->getMessage();
            if (Re::match($message, $messageRegexp) === null) {
                parent::fail(sprintf('Token exception message is "%s" and should match "%s".', $message, $messageRegexp));
            }
        }
        if ($position !== null && $position !== $token->position) {
            parent::fail(sprintf('Token starting position is %s and should be %s.', $token->position, $position));
        }
    }

    public static function tokenList(string $sql): TokenList
    {
        $session = new Session(Platform::get(Platform::MYSQL, '5.7'));
        $lexer = new Lexer($session, true, true);

        return iterator_to_array($lexer->tokenizeLists($sql))[0];
    }

    public static function parseSerialize(
        string $query,
        ?string $expected = null,
        ?int $version = null,
        ?string $delimiter = null
    ): void {
        /** @var string $query */
        $query = preg_replace('/\\s+/', ' ', $query);
        $query = str_replace(['( ', ' )'], ['(', ')'], $query);

        if ($expected !== null) {
            /** @var string $expected */
            $expected = preg_replace('/\\s+/', ' ', $expected);
            $expected = str_replace(['( ', ' )'], ['(', ')'], $expected);
        } else {
            $expected = $query;
        }

        $parser = ParserHelper::getParserFactory(null, $version, $delimiter)->getParser();
        $formatter = new Formatter($parser->getSession());

        $results = iterator_to_array($parser->parse($query));
        if (count($results) > 1) {
            self::fail('More than one command found in given SQL code.');
        }
        [$command, $tokenList] = $results[0];

        if ($command instanceof InvalidCommand) {
            if (class_exists(Debugger::class)) {
                Debugger::dump($tokenList);
            }
            $exception = $command->getException();
            $message = '';
            if ($exception instanceof AnalyzerException) {
                foreach ($exception->getResults() as $failure) {
                    $message .= "\n - " . $failure->getMessage();
                }
            }
            self::fail($exception->getMessage() . $message);
        }

        $actual = $command->serialize($formatter);

        /** @var string $actual */
        $actual = preg_replace('/\\s+/', ' ', $actual);
        $actual = str_replace(['( ', ' )'], ['(', ')'], $actual);

        self::same($actual, $expected);
    }

    public static function validCommand(string $query, ?Parser $parser = null): Command
    {
        $parser = $parser ?? ParserHelper::getParserFactory()->getParser();

        $results = iterator_to_array($parser->parse($query));
        if (count($results) > 1) {
            self::fail('More than one command found in given SQL code.');
        }
        [$command, $tokenList] = $results[0];

        if ($command instanceof InvalidCommand) {
            if (class_exists(Debugger::class)) {
                Debugger::dump($tokenList);
            }
            throw $command->getException();
        }

        self::true(true);

        return $command;
    }

    public static function validCommands(string $sql, ?Parser $parser = null): void
	{
        $parser = $parser ?? ParserHelper::getParserFactory()->getParser();

        try {
            /** @var Command $command */
            /** @var TokenList $tokenList */
            foreach ($parser->parse($sql) as [$command, $tokenList]) {
                if ($command instanceof InvalidCommand) {
                    throw $command->getException();
                }

                self::true(true);
            }
        } catch (LexerException $e) {
            throw $e;
        }

        self::true(true);
    }

}
