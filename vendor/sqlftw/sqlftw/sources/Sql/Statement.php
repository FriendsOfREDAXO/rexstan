<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql;

abstract class Statement implements SqlSerializable
{

    protected ?string $delimiter = null;

    /** @var list<string> */
    protected array $commentsBefore = [];

    public function setDelimiter(string $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    public function getDelimiter(): ?string
    {
        return $this->delimiter;
    }

    /**
     * @param list<string> $comments
     */
    public function setCommentsBefore(array $comments): void
    {
        $this->commentsBefore = $comments;
    }

    /**
     * @return list<string>
     */
    public function getCommentsBefore(): array
    {
        return $this->commentsBefore;
    }

}
