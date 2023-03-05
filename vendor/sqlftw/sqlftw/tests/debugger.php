<?php declare(strict_types = 1);

namespace Test;

use Dogma\Debug\Ansi;
use Dogma\Debug\Dumper;
use Dogma\Debug\FormattersDogma;
use Dogma\Debug\Str;
use SqlFtw\Parser\InvalidTokenException;
use SqlFtw\Parser\Parser;
use SqlFtw\Parser\Token;
use SqlFtw\Parser\TokenList;
use SqlFtw\Parser\TokenType;
use SqlFtw\Platform\Platform;
use SqlFtw\Sql\Dml\TableReference\TableReferenceTable;
use SqlFtw\Sql\Expression\ColumnType;
use SqlFtw\Sql\Expression\FunctionCall;
use SqlFtw\Sql\Expression\IntLiteral;
use SqlFtw\Sql\Expression\QualifiedName;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\Expression\StringLiteral;
use SqlFtw\Sql\Expression\UintLiteral;
use SqlFtw\Sql\Expression\UserVariable;
use SqlFtw\Tests\Assert;
use Tracy\Debugger;
use function count;
use function get_class;
use function implode;

Debugger::$maxDepth = 9;
Debugger::$strictMode = true;
FormattersDogma::register();

Dumper::$hiddenFields[] = 'sql';
Dumper::$hiddenFields[] = 'maxLengths';
Dumper::$doNotTraverse[] = Parser::class;
Dumper::$doNotTraverse[] = Assert::class . '::validCommands';
Dumper::$doNotTraverse[] = InvalidTokenException::class . '::tokens';
Dumper::$namespaceReplacements['~SqlFtw\\\\Parser\\\\(.*)~'] = '..\1';
Dumper::$namespaceReplacements['~SqlFtw\\\\Formatter\\\\(.*)~'] = '..\1';
Dumper::$namespaceReplacements['~SqlFtw\\\\Sql\\\\(.*)~'] = '..\1';
Dumper::$namespaceReplacements['~SqlFtw\\\\Platform\\\\(.*)~'] = '..\1';
Dumper::$traceFilters[] = '~^Amp\\\\~';
Dumper::$traceFilters[] = '~^Opis\\\\Closure~';

// TokenType value
Dumper::$intFormatters = [
    '~tokenType|tokenMask|autoSkip~' => static function (int $int): string {
        $types = implode('|', TokenType::getByValue($int)->getConstantNames());

        return Dumper::int((string) $int) . ' ' . Dumper::info('// TokenType::' . $types);
    },
] + Dumper::$intFormatters;

// Token
$tokenFormatter = static function (Token $token, int $depth = 0): string {
    $oldInfo = Dumper::$showInfo;
    Dumper::$showInfo = false;
    $value = Dumper::dumpValue($token->value, $depth + 1);
    if (($token->type & (TokenType::COMMENT | TokenType::WHITESPACE)) !== 0) {
        $value = Ansi::dgray(Ansi::removeColors($value));
    }
    Dumper::$showInfo = $oldInfo;

    $type = implode('|', TokenType::getByValue($token->type)->getConstantNames());
    $orig = $token->original !== null && $token->original !== $token->value ? ' / ' . Dumper::value($token->original) : '';

    return Dumper::name(get_class($token)) . Dumper::bracket('(') . $value . $orig . ' / '
        . Dumper::value2($type) . ' ' . Dumper::info('at row') . ' ' . $token->row
        . Dumper::bracket(')') . Dumper::objectInfo($token);
};
Dumper::$objectFormatters[Token::class] = $tokenFormatter;
Dumper::$shortObjectFormatters[Token::class] = $tokenFormatter;
unset($tokenFormatter);

// TokenList
Dumper::$shortObjectFormatters[TokenList::class] = static function (TokenList $tokenList): string {
    $limit = 15;
    $tokens = $tokenList->getTokens();
    $count = count($tokens);
    $contents = '';
    foreach (array_slice($tokens, 0, $limit) as $token) {
        $contents .= ctype_space($token->value) ? '·' : $token->value;
    }
    $dots = $count > $limit ? '...' : '';

    return Dumper::name(get_class($tokenList)) . Dumper::bracket('(')
        . Dumper::value($contents . $dots) . ' | ' . Dumper::value2($count . ' tokens, position ' . $tokenList->getPosition())
        . Dumper::bracket(')') . Dumper::objectInfo($tokenList);
};

// Platform
Dumper::$objectFormatters[Platform::class] = static function (Platform $platform): string {
    return Dumper::name(get_class($platform)) . Dumper::bracket('(')
        . Dumper::value($platform->getName()) . ' ' . Dumper::value2($platform->getVersion()->format())
        . Dumper::bracket(')');
};

// SimpleName
Dumper::$objectFormatters[SimpleName::class] = static function (SimpleName $simpleName): string {
    return Dumper::name(get_class($simpleName)) . Dumper::bracket('(')
        . Dumper::value($simpleName->getName())
        . Dumper::bracket(')');
};

// QualifiedName
Dumper::$objectFormatters[QualifiedName::class] = static function (QualifiedName $qualifiedName): string {
    $name = $qualifiedName->getSchema() . '.' . $qualifiedName->getName();
    if (Str::isBinary($name) !== null) {
        $name = Dumper::string($name);
    } else {
        $name = Dumper::value($name);
    }

    return Dumper::name(get_class($qualifiedName)) . Dumper::bracket('(') . $name . Dumper::bracket(')');
};

// UserVariable
Dumper::$objectFormatters[UserVariable::class] = static function (UserVariable $userVariable): string {
    return Dumper::name(get_class($userVariable)) . Dumper::bracket('(')
        . Dumper::value($userVariable->getName())
        . Dumper::bracket(')');
};

// UintLiteral
Dumper::$objectFormatters[UintLiteral::class] = static function (UintLiteral $uintLiteral): string {
    return Dumper::name(get_class($uintLiteral)) . Dumper::bracket('(')
        . Dumper::value($uintLiteral->getValue())
        . Dumper::bracket(')');
};

// IntLiteral
Dumper::$objectFormatters[IntLiteral::class] = static function (IntLiteral $intLiteral): string {
    return Dumper::name(get_class($intLiteral)) . Dumper::bracket('(')
        . Dumper::value($intLiteral->getValue())
        . Dumper::bracket(')');
};

// StringLiteral
Dumper::$objectFormatters[StringLiteral::class] = static function (StringLiteral $stringLiteral): string {
    if ($stringLiteral->getCharset() !== null || count($stringLiteral->getParts()) > 1) {
        return '';
    }
    return Dumper::name(get_class($stringLiteral)) . Dumper::bracket('(')
        . Dumper::string($stringLiteral->getParts()[0])
        . Dumper::bracket(')');
};

// ColumnType
Dumper::$objectFormatters[ColumnType::class] = static function (ColumnType $columnType): string {
    if ($columnType->isUnsigned() !== false || $columnType->zerofill() !== false || $columnType->getSize() !== null || $columnType->getValues() !== null
        || $columnType->getCharset() !== null || $columnType->getCollation() !== null || $columnType->getSrid() !== null
    ) {
        return '';
    }
    return Dumper::name(get_class($columnType)) . Dumper::bracket('(')
        . Dumper::value($columnType->getBaseType()->getValue())
        . Dumper::bracket(')');
};

// FunctionCall
Dumper::$shortObjectFormatters[FunctionCall::class] = static function (FunctionCall $functionCall): string {
    return Dumper::name(get_class($functionCall)) . Dumper::bracket('(') . ' '
        . Dumper::value($functionCall->getFunction()->getFullName()) . ' ' . Dumper::exceptions('...') . ' '
        . Dumper::bracket(')') . Dumper::objectInfo($functionCall);
};

// TableReferenceTable
Dumper::$shortObjectFormatters[TableReferenceTable::class] = static function (TableReferenceTable $reference): string {
    if ($reference->getPartitions() !== null || $reference->getIndexHints() !== null) {
        return '';
    }
    return Dumper::name(get_class($reference)) . Dumper::bracket('(')
        . Dumper::value($reference->getTable()->getFullName())
        . ($reference->getAlias() !== null ? ' AS ' . Dumper::value2($reference->getAlias()) : '')
        . Dumper::bracket(')') . Dumper::objectInfo($reference);
};
