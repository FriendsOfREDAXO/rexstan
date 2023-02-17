<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Replication;

use Dogma\Arr;
use LogicException;
use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Expression\QualifiedName;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Util\TypeChecker;
use function implode;

class ReplicationFilter implements SqlSerializable
{

    /** @var ReplicationFilterType::* */
    private string $type;

    /** @var array<string, string>|list<string|ObjectIdentifier> */
    private array $items;

    /**
     * @param ReplicationFilterType::* $type
     * @param array<string, string>|list<string|ObjectIdentifier> $items
     */
    public function __construct(string $type, array $items)
    {
        TypeChecker::check($items, ReplicationFilterType::getItemType($type));

        $this->type = $type;
        $this->items = $items;
    }

    /**
     * @return ReplicationFilterType::*
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, string>|list<string|ObjectIdentifier>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function serialize(Formatter $formatter): string
    {
        if ($this->items === []) {
            return $this->type . ' = ()';
        } else {
            $itemsType = ReplicationFilterType::getItemType($this->type);
            switch ($itemsType) {
                case BaseType::CHAR . '[]':
                    /** @var non-empty-list<string> $items */
                    $items = $this->items;
                    if ($this->type === ReplicationFilterType::REPLICATE_DO_DB || $this->type === ReplicationFilterType::REPLICATE_IGNORE_DB) {
                        return $this->type . ' = (' . $formatter->formatNamesList($items) . ')';
                    } else {
                        return $this->type . ' = (' . $formatter->formatStringList($items) . ')';
                    }
                case ObjectIdentifier::class . '[]':
                    // phpcs:ignore SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.MissingVariable
                    /** @var non-empty-list<QualifiedName> $items2 */
                    $items2 = $this->items;

                    return $this->type . ' = (' . $formatter->formatSerializablesList($items2) . ')';
                case BaseType::CHAR . '{}':
                    return $this->type . ' = (' . implode(', ', Arr::mapPairs($this->items, static function (string $key, string $value) use ($formatter) {
                        return '(' . $formatter->formatName($key) . ', ' . $formatter->formatName($value) . ')';
                    })) . ')';
                default:
                    throw new LogicException("Unknown items type: {$itemsType}");
            }
        }
    }

}
