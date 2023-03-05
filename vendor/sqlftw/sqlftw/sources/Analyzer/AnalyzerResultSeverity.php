<?php declare(strict_types = 1);

namespace SqlFtw\Analyzer;

final class AnalyzerResultSeverity
{

    public const LEXER_ERROR = 1; // critical syntax error on lexer level
    public const PARSER_ERROR = 2; // critical syntax error on parser level
    public const PARSER_WARNING = 3; // parser warning about not parsed features
    public const ERROR = 4; // static analysis error
    public const WARNING = 5; // static analysis issue to consider
    public const NOTICE = 6; // static analysis tips for improvement
    public const SKIP_NOTICE = 7; // static analysis notice about not analysed features

}
