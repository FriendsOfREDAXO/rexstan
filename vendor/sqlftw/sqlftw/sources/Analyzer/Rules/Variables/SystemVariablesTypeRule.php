<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Analyzer\Rules\Variables;

use SqlFtw\Analyzer\AnalyzerResult;
use SqlFtw\Analyzer\AnalyzerResultSeverity;
use SqlFtw\Analyzer\SimpleContext;
use SqlFtw\Analyzer\SimpleRule;
use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dal\Set\SetVariablesCommand;
use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\Expression\DefaultLiteral;
use SqlFtw\Sql\Expression\ExpressionNode;
use SqlFtw\Sql\Expression\KeywordLiteral;
use SqlFtw\Sql\Expression\OnOffLiteral;
use SqlFtw\Sql\Expression\Placeholder;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\Expression\SystemVariable;
use SqlFtw\Sql\Expression\Value;
use SqlFtw\Sql\MysqlVariable;
use SqlFtw\Sql\SqlMode;
use SqlFtw\Sql\Statement;
use function count;
use function get_class;
use function gettype;
use function implode;
use function is_array;
use function is_int;
use function is_numeric;
use function is_object;
use function str_replace;

class SystemVariablesTypeRule implements SimpleRule
{

    /**
     * @return list<AnalyzerResult>
     */
    public function process(Statement $statement, SimpleContext $context, int $flags): array
    {
        if ($statement instanceof SetVariablesCommand) {
            return $this->processSet($statement, $context);
        }

        return [];
    }

    /**
     * @return list<AnalyzerResult>
     */
    private function processSet(SetVariablesCommand $command, SimpleContext $context): array
    {
        $mode = $context->getSession()->getMode();
        $strict = $mode->containsAny(SqlMode::STRICT_ALL_TABLES);

        $results = [];
        foreach ($command->getAssignments() as $assignment) {
            $variable = $assignment->getVariable();
            if (!$variable instanceof SystemVariable) {
                continue;
            }

            $name = $variable->getName();
            $var = MysqlVariable::getInfo($name);
            $type = $var->type;
            if ($type === BaseType::UNSIGNED && !$strict && ($var->clamp || $var->clampMin)) {
                $type = BaseType::SIGNED;
            }
            $expression = $assignment->getExpression();
            $value = $context->getResolver()->resolve($expression);

            if (is_array($value)) {
                if (count($value) === 1) {
                    /** @var scalar|ExpressionNode|null $value */
                    $value = $value[0];
                } else {
                    $results[] = new AnalyzerResult("System variable {$name} can not be set to non-scalar value.");
                    continue;
                }
            }
            if ($value instanceof DefaultLiteral) {
                if (!MysqlVariable::hasDefault($name)) {
                    $results[] = new AnalyzerResult("System variable {$name} can not be set to default value.");
                }
                continue;
            }
            if ($value === null) {
                if (!$var->nullable) {
                    $results[] = new AnalyzerResult("System variable {$name} is not nullable.");
                }
                continue;
            }

            if ($value instanceof Placeholder) {
                continue;
            }

            if ($value instanceof SimpleName) {
                $value = $value->getName();
            } elseif ($value instanceof KeywordLiteral && !$value instanceof OnOffLiteral) {
                $value = $value->getValue();
            }

            if ($value instanceof ExpressionNode && !$value instanceof Value) {
                // not resolved
                // todo: insert real static type analysis here : ]
                $formatter = new Formatter($context->getPlatform(), $context->getSession());
                $expressionString = str_replace("\n", "", $expression->serialize($formatter));
                $expressionType = get_class($expression);
                $message = "System variable {$name} assignment with expression \"{$expressionString}\" ({$expressionType}) was not checked.";
                $results[] = new AnalyzerResult($message, AnalyzerResultSeverity::SKIP_NOTICE);
            } else {
                if ($var->nonEmpty && $value === '') {
                    $results[] = new AnalyzerResult("System variable {$name} can not be set to an empty value.");
                }
                if ($var->nonZero && $value === 0) {
                    $results[] = new AnalyzerResult("System variable {$name} can not be set to zero.");
                }
                if (!$context->getTypeChecker()->canBeCastedTo($value, $type, $var->values, $context->getResolver()->cast())) {
                    if ($var->values !== null) {
                        $type .= '(' . implode(',', $var->values) . ')';
                    }
                    $realType = is_object($value) ? get_class($value) : gettype($value);
                    $results[] = new AnalyzerResult("System variable {$name} only accepts {$type}, but {$realType} given.");
                    continue;
                }

                // validate bounds
                if (!is_numeric($value)) {
                    continue;
                }
                if ($var->min === null || $var->max === null) {
                    continue;
                } elseif ($value < $var->min && ($strict || (!$var->clamp && !$var->clampMin))) {
                    $results[] = new AnalyzerResult("System variable {$name} value must be between {$var->min} and {$var->max}.");
                } elseif ($value > $var->max && ($strict || !$var->clamp)) {
                    $results[] = new AnalyzerResult("System variable {$name} value must be between {$var->min} and {$var->max}.");
                }
                if ($var->increment === null) {
                    continue;
                } elseif (($strict || !$var->clamp) && (!is_int($value) || ($value % $var->increment) !== 0)) {
                    $results[] = new AnalyzerResult("System variable {$name} value must be multiple of {$var->increment}.");
                }
            }
        }

        return $results;
    }

}
