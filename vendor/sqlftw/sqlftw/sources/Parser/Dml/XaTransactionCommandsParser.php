<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dml;

use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Dml\XaTransaction\XaCommitCommand;
use SqlFtw\Sql\Dml\XaTransaction\XaEndCommand;
use SqlFtw\Sql\Dml\XaTransaction\XaPrepareCommand;
use SqlFtw\Sql\Dml\XaTransaction\XaRecoverCommand;
use SqlFtw\Sql\Dml\XaTransaction\XaRollbackCommand;
use SqlFtw\Sql\Dml\XaTransaction\XaStartCommand;
use SqlFtw\Sql\Dml\XaTransaction\XaStartOption;
use SqlFtw\Sql\Dml\XaTransaction\XaTransactionCommand;
use SqlFtw\Sql\Dml\XaTransaction\Xid;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\HexadecimalLiteral;
use SqlFtw\Sql\Expression\UintLiteral;
use SqlFtw\Sql\Keyword;
use const PHP_INT_MAX;

class XaTransactionCommandsParser
{

    /**
     * XA {START|BEGIN} xid [JOIN|RESUME]
     *
     * XA END xid [SUSPEND [FOR MIGRATE]]
     *
     * XA PREPARE xid
     *
     * XA COMMIT xid [ONE PHASE]
     *
     * XA ROLLBACK xid
     *
     * XA RECOVER [CONVERT XID]
     */
    public function parseXa(TokenList $tokenList): XaTransactionCommand
    {
        $tokenList->expectKeyword(Keyword::XA);
        $second = $tokenList->expectKeyword();
        switch ($second) {
            case Keyword::START:
            case Keyword::BEGIN:
                $xid = $this->parseXid($tokenList);
                $option = $tokenList->getKeywordEnum(XaStartOption::class);

                return new XaStartCommand($xid, $option);
            case Keyword::END:
                $xid = $this->parseXid($tokenList);
                $suspend = $tokenList->hasKeyword(Keyword::SUSPEND);
                $forMigrate = $suspend && $tokenList->hasKeywords(Keyword::FOR, Keyword::MIGRATE);

                return new XaEndCommand($xid, $suspend, $forMigrate);
            case Keyword::PREPARE:
                $xid = $this->parseXid($tokenList);

                return new XaPrepareCommand($xid);
            case Keyword::COMMIT:
                $xid = $this->parseXid($tokenList);
                $onePhase = $tokenList->hasKeywords(Keyword::ONE, Keyword::PHASE);

                return new XaCommitCommand($xid, $onePhase);
            case Keyword::ROLLBACK:
                $xid = $this->parseXid($tokenList);

                return new XaRollbackCommand($xid);
            case Keyword::RECOVER:
                $convertXid = $tokenList->hasKeywords(Keyword::CONVERT, Keyword::XID);

                return new XaRecoverCommand($convertXid);
            default:
                $tokenList->missingAnyKeyword(
                    Keyword::START,
                    Keyword::BEGIN,
                    Keyword::END,
                    Keyword::PREPARE,
                    Keyword::COMMIT,
                    Keyword::ROLLBACK,
                    Keyword::RECOVER
                );
        }
    }

    /**
     * xid: gtrid [, bqual [, formatID ]]
     */
    private function parseXid(TokenList $tokenList): Xid
    {
        $transactionId = $tokenList->expectStringValue();
        $tokenList->validateName(EntityType::XA_TRANSACTION, $transactionId->getValue());

        $branch = $format = null;
        if ($tokenList->hasSymbol(',')) {
            $branch = $tokenList->expectStringValue();
            if ($tokenList->hasSymbol(',')) {
                $format = $tokenList->expectIntLike();
                if (!$format instanceof UintLiteral && !$format instanceof HexadecimalLiteral) {
                    throw new ParserException('Transaction format id must be unsigned int or hexadecimal literal.', $tokenList);
                }
                if ($format instanceof UintLiteral && (float) $format->getValue() >= (float) PHP_INT_MAX) {
                    throw new ParserException('Transaction format id is too big.', $tokenList);
                }
            }
        }

        return new Xid($transactionId, $branch, $format);
    }

}
