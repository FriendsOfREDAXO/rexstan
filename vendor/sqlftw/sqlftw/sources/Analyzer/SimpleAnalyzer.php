<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Analyzer;

use SqlFtw\Sql\Statement;

class SimpleAnalyzer
{

    private SimpleContext $context;

    /** @var non-empty-list<SimpleRule> */
    private array $rules;

    /**
     * @param non-empty-list<SimpleRule> $rules
     */
    public function __construct(SimpleContext $context, array $rules)
    {
        $this->context = $context;
        $this->rules = $rules;
    }

    /**
     * @return array<string, AnalyzerResult> ($message => $result)
     */
    public function process(Statement $statement, int $flags = 0): array
    {
        $results = [];
        foreach ($this->rules as $rule) {
            $ruleResults = $rule->process($statement, $this->context, $flags);

            foreach ($ruleResults as $result) {
                $results[$result->getMessage()] = $result->setContext($rule, $statement, $this->context->getSession()->getMode());
            }
        }

        return $results;
    }

}
