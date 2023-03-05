<?php declare(strict_types = 1);

// phpcs:disable SlevomatCodingStandard.Functions.RequireSingleLineCall

namespace SqlFtw\Parser;

use SqlFtw\Sql\MysqlVariable;
use SqlFtw\Tests\Assert;
use SqlFtw\Tests\Util\MysqlVariableHelper;

require __DIR__ . '/../bootstrap.php';

foreach (MysqlVariableHelper::getSetVarEnabledVariables() as $variableName) {
    if (!MysqlVariable::isDynamic($variableName)) {
        continue;
    }

    $value = MysqlVariableHelper::getSampleFormattedValue($variableName);
    $code = "SELECT /*+ SET_VAR({$variableName} = {$value}) */ * FROM table1";

    Assert::parseSerialize($code);
}
