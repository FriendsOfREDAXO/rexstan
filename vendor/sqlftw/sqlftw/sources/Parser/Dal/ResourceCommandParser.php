<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dal;

use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Dal\Resource\AlterResourceGroupCommand;
use SqlFtw\Sql\Dal\Resource\CreateResourceGroupCommand;
use SqlFtw\Sql\Dal\Resource\DropResourceGroupCommand;
use SqlFtw\Sql\Dal\Resource\ResourceGroupType;
use SqlFtw\Sql\Dal\Resource\SetResourceGroupCommand;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Keyword;

class ResourceCommandParser
{

    /**
     * ALTER RESOURCE GROUP group_name
     *     [VCPU [=] vcpu_spec [, vcpu_spec] ...]
     *     [THREAD_PRIORITY [=] N]
     *     [ENABLE|DISABLE [FORCE]]
     *
     * vcpu_spec: {N | M - N}
     */
    public function parseAlterResourceGroup(TokenList $tokenList): AlterResourceGroupCommand
    {
        $tokenList->expectKeywords(Keyword::ALTER, Keyword::RESOURCE, Keyword::GROUP);
        $name = $tokenList->expectName(EntityType::RESOURCE_GROUP);

        [$vcpus, $threadPriority, $enable, $force] = $this->parseResourceGroupOptions($tokenList);

        return new AlterResourceGroupCommand($name, $vcpus, $threadPriority, $enable, $force);
    }

    /**
     * CREATE RESOURCE GROUP group_name
     *     TYPE = {SYSTEM|USER}
     *     [VCPU [=] vcpu_spec [, vcpu_spec] ...]
     *     [THREAD_PRIORITY [=] N]
     *     [ENABLE|DISABLE]
     *
     * vcpu_spec: {N | M - N}
     */
    public function parseCreateResourceGroup(TokenList $tokenList): CreateResourceGroupCommand
    {
        $tokenList->expectKeywords(Keyword::CREATE, Keyword::RESOURCE, Keyword::GROUP);
        $name = $tokenList->expectName(EntityType::RESOURCE_GROUP);

        $tokenList->expectKeyword(Keyword::TYPE);
        $tokenList->passSymbol('=');
        $type = $tokenList->expectKeywordEnum(ResourceGroupType::class);

        [$vcpus, $threadPriority, $enable, $force] = $this->parseResourceGroupOptions($tokenList);

        return new CreateResourceGroupCommand($name, $type, $vcpus, $threadPriority, $enable, $force);
    }

    /**
     * @return array{non-empty-list<array{0: int, 1?: int}>|null, int|null, bool|null, bool}
     */
    private function parseResourceGroupOptions(TokenList $tokenList): array
    {
        $vcpus = null;
        if ($tokenList->hasKeyword(Keyword::VCPU)) {
            $tokenList->passSymbol('=');
            do {
                $start = (int) $tokenList->expectUnsignedInt();
                if ($tokenList->hasSymbol('-')) {
                    $end = (int) $tokenList->expectUnsignedInt();
                    $vcpus[] = [$start, $end];
                } else {
                    $vcpus[] = [$start];
                }
            } while ($tokenList->hasSymbol(','));
        }

        $threadPriority = null;
        if ($tokenList->hasKeyword(Keyword::THREAD_PRIORITY)) {
            $tokenList->passSymbol('=');
            $threadPriority = (int) $tokenList->expectUnsignedInt();
        }

        $enable = null;
        $force = false;
        if ($tokenList->hasKeyword(Keyword::ENABLE)) {
            $enable = true;
        } elseif ($tokenList->hasKeyword(Keyword::DISABLE)) {
            $enable = false;
            if ($tokenList->hasKeyword(Keyword::FORCE)) {
                $force = true;
            }
        }

        return [$vcpus, $threadPriority, $enable, $force];
    }

    /**
     * DROP RESOURCE GROUP group_name [FORCE]
     */
    public function parseDropResourceGroup(TokenList $tokenList): DropResourceGroupCommand
    {
        $tokenList->expectKeywords(Keyword::DROP, Keyword::RESOURCE, Keyword::GROUP);
        $name = $tokenList->expectName(EntityType::RESOURCE_GROUP);
        $force = $tokenList->hasKeyword(Keyword::FORCE);

        return new DropResourceGroupCommand($name, $force);
    }

    /**
     * SET RESOURCE GROUP group_name
     *     [FOR thread_id [, thread_id] ...]
     */
    public function parseSetResourceGroup(TokenList $tokenList): SetResourceGroupCommand
    {
        $tokenList->expectKeywords(Keyword::SET, Keyword::RESOURCE, Keyword::GROUP);
        $name = $tokenList->expectName(EntityType::RESOURCE_GROUP);
        $threadIds = null;
        if ($tokenList->hasKeywords(Keyword::FOR)) {
            do {
                $threadIds[] = (int) $tokenList->expectUnsignedInt();
            } while ($tokenList->hasSymbol(','));
        }

        return new SetResourceGroupCommand($name, $threadIds);
    }

}
