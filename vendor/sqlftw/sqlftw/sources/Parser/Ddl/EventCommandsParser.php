<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Ddl;

use SqlFtw\Parser\ExpressionParser;
use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\RoutineBodyParser;
use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Ddl\Event\AlterEventCommand;
use SqlFtw\Sql\Ddl\Event\CreateEventCommand;
use SqlFtw\Sql\Ddl\Event\DropEventCommand;
use SqlFtw\Sql\Ddl\Event\EventDefinition;
use SqlFtw\Sql\Ddl\Event\EventSchedule;
use SqlFtw\Sql\Ddl\Event\EventState;
use SqlFtw\Sql\Expression\FunctionCall;
use SqlFtw\Sql\Expression\Subquery;
use SqlFtw\Sql\Expression\TimeIntervalExpression;
use SqlFtw\Sql\Expression\TimeIntervalLiteral;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\Routine\RoutineType;

class EventCommandsParser
{

    private RoutineBodyParser $routineBodyParser;

    private ExpressionParser $expressionParser;

    public function __construct(RoutineBodyParser $routineBodyParser, ExpressionParser $expressionParser)
    {
        $this->routineBodyParser = $routineBodyParser;
        $this->expressionParser = $expressionParser;
    }

    /**
     * ALTER
     *   [DEFINER = { user | CURRENT_USER }]
     *   EVENT event_name
     *   [ON SCHEDULE schedule]
     *   [ON COMPLETION [NOT] PRESERVE]
     *   [RENAME TO new_event_name]
     *   [ENABLE | DISABLE | DISABLE ON SLAVE]
     *   [COMMENT 'comment']
     *   [DO event_body]
     */
    public function parseAlterEvent(TokenList $tokenList): AlterEventCommand
    {
        $tokenList->expectKeyword(Keyword::ALTER);
        $definer = $schedule = $preserve = $newName = $comment = $body = null;

        if ($tokenList->hasKeyword(Keyword::DEFINER)) {
            $tokenList->expectOperator('=');
            $definer = $this->expressionParser->parseUserExpression($tokenList);
        }
        $tokenList->expectKeyword(Keyword::EVENT);
        $name = $tokenList->expectObjectIdentifier();

        if ($tokenList->hasKeywords(Keyword::ON, Keyword::SCHEDULE)) {
            $schedule = $this->parseSchedule($tokenList);
        }
        if ($tokenList->hasKeywords(Keyword::ON, Keyword::COMPLETION)) {
            $preserve = !$tokenList->hasKeyword(Keyword::NOT);
            $tokenList->expectKeyword(Keyword::PRESERVE);
        }
        if ($tokenList->hasKeywords(Keyword::RENAME, Keyword::TO)) {
            $newName = $tokenList->expectObjectIdentifier();
        }

        $state = $tokenList->getMultiKeywordsEnum(EventState::class);

        if ($tokenList->hasKeyword(Keyword::COMMENT)) {
            $comment = $tokenList->expectString();
        }
        if ($tokenList->hasKeyword(Keyword::DO)) {
            $body = $this->routineBodyParser->parseBody($tokenList, RoutineType::EVENT);
        }

        if ($schedule === null && $preserve === null && $newName === null && $state === null && $comment === null && $body === null) {
            throw new ParserException('ALTER EVENT without changes is not allowed.', $tokenList);
        }

        return new AlterEventCommand($name, $schedule, $body, $definer, $state, $preserve, $comment, $newName);
    }

    /**
     * CREATE
     *     [DEFINER = { user | CURRENT_USER }]
     *     EVENT
     *     [IF NOT EXISTS]
     *     event_name
     *     ON SCHEDULE schedule
     *     [ON COMPLETION [NOT] PRESERVE]
     *     [ENABLE | DISABLE | DISABLE ON SLAVE]
     *     [COMMENT 'comment']
     *     DO event_body
     */
    public function parseCreateEvent(TokenList $tokenList): CreateEventCommand
    {
        $tokenList->expectKeyword(Keyword::CREATE);
        $definer = $preserve = $comment = null;

        if ($tokenList->hasKeyword(Keyword::DEFINER)) {
            $tokenList->passSymbol('=');
            $definer = $this->expressionParser->parseUserExpression($tokenList);
        }
        $tokenList->expectKeyword(Keyword::EVENT);
        $ifNotExists = $tokenList->hasKeywords(Keyword::IF, Keyword::NOT, Keyword::EXISTS);
        $name = $tokenList->expectObjectIdentifier();

        $tokenList->expectKeywords(Keyword::ON, Keyword::SCHEDULE);
        $schedule = $this->parseSchedule($tokenList);

        if ($tokenList->hasKeywords(Keyword::ON, Keyword::COMPLETION)) {
            $preserve = !$tokenList->hasKeyword(Keyword::NOT);
            $tokenList->expectKeyword(Keyword::PRESERVE);
        }

        $state = $tokenList->getMultiKeywordsEnum(EventState::class);

        if ($tokenList->hasKeyword(Keyword::COMMENT)) {
            $comment = $tokenList->expectString();
        }

        $tokenList->expectKeyword(Keyword::DO);
        $body = $this->routineBodyParser->parseBody($tokenList, RoutineType::EVENT);

        $event = new EventDefinition($name, $schedule, $body, $definer, $state, $preserve, $comment);

        return new CreateEventCommand($event, $ifNotExists);
    }

    /**
     * schedule:
     *     AT timestamp [+ INTERVAL interval] ...
     *   | EVERY interval
     *     [STARTS timestamp [+ INTERVAL interval] ...]
     *     [ENDS timestamp [+ INTERVAL interval] ...]
     */
    private function parseSchedule(TokenList $tokenList): EventSchedule
    {
        $at = $every = $startTime = $endTime = null;

        if ($tokenList->hasKeyword(Keyword::AT)) {
            $at = $this->expressionParser->parseExpression($tokenList);
            if ($at instanceof Subquery) {
                throw new ParserException('Select in event schedule is not supported.', $tokenList);
            }
        } elseif ($tokenList->hasKeyword(Keyword::EVERY)) {
            $every = $this->expressionParser->parseInterval($tokenList);
            if ($every instanceof TimeIntervalExpression) {
                $value = $every->getExpression();
                if ($value instanceof FunctionCall || $value instanceof Subquery) {
                    throw new ParserException('Function call or subquery in event schedule is not supported.', $tokenList);
                }
            } elseif ($every instanceof TimeIntervalLiteral && $every->isZero()) {
                throw new ParserException('Event schedule interval must be strictly positive.', $tokenList);
            }
            if ($every->getUnit()->hasMicroseconds()) {
                throw new ParserException('Microseconds in event schedule are not supported.', $tokenList);
            }

            if ($tokenList->hasKeyword(Keyword::STARTS)) {
                $startTime = $this->expressionParser->parseExpression($tokenList);
                if ($startTime instanceof Subquery) {
                    throw new ParserException('Select in event schedule is not supported.', $tokenList);
                }
            }
            if ($tokenList->hasKeyword(Keyword::ENDS)) {
                $endTime = $this->expressionParser->parseExpression($tokenList);
                if ($endTime instanceof Subquery) {
                    throw new ParserException('Select in event schedule is not supported.', $tokenList);
                }
            }
        } else {
            $tokenList->missingAnyKeyword(Keyword::AT, Keyword::EVERY);
        }

        return new EventSchedule($at, $every, $startTime, $endTime);
    }

    /**
     * DROP EVENT [IF EXISTS] event_name
     */
    public function parseDropEvent(TokenList $tokenList): DropEventCommand
    {
        $tokenList->expectKeywords(Keyword::DROP, Keyword::EVENT);
        $ifExists = $tokenList->hasKeywords(Keyword::IF, Keyword::EXISTS);

        $name = $tokenList->expectObjectIdentifier();

        return new DropEventCommand($name, $ifExists);
    }

}
