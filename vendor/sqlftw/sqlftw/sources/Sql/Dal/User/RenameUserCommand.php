<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\User;

use Dogma\CombineIterator;
use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\FunctionCall;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\StatementImpl;
use SqlFtw\Sql\UserName;
use function array_values;
use function count;
use function rtrim;

class RenameUserCommand extends StatementImpl implements UserCommand
{

    /** @var non-empty-list<UserName|FunctionCall> */
    protected $users;

    /** @var non-empty-list<UserName> */
    private $newUsers;

    /**
     * @param non-empty-list<UserName|FunctionCall> $users
     * @param non-empty-list<UserName> $newUsers
     */
    public function __construct(array $users, array $newUsers)
    {
        if (count($users) !== count($newUsers)) {
            throw new InvalidDefinitionException('Count of old user names and new user names do not match.');
        }

        $this->users = array_values($users);
        $this->newUsers = array_values($newUsers);
    }

    /**
     * @return non-empty-list<UserName|FunctionCall>
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @return non-empty-list<UserName>
     */
    public function getNewUsers(): array
    {
        return $this->newUsers;
    }

    public function getIterator(): CombineIterator
    {
        return new CombineIterator($this->users, $this->newUsers);
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'RENAME USER';
        foreach ($this->users as $i => $user) {
            $result .= ' ' . $user->serialize($formatter) . ' TO ' . $this->newUsers[$i]->serialize($formatter) . ',';
        }

        return rtrim($result, ',');
    }

}
