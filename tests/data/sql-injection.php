<?php

namespace RexSqlInjection;

use rex;
use rex_i18n;
use rex_sql;

/**
 * @return void
 */
function safeArray($_id, string $langID)
{
    $select = rex_sql::factory();
    $select->setTable('article');
    $select->setWhere(['id' => $_id, 'clang_id' => $langID]);
}

/**
 * @param numeric-string  $numericS
 * @return void
 */
function safeScalars($mixed, string $s, $numericS, int $i, float $f, bool $b, array $arr)
{
    $select = rex_sql::factory();
    $select->setTable('article');
    $select->setWhere('id = ' . $select->escape($s));
    $select->setWhere('id = ' . $select->escape($mixed));
    $select->setWhere($select->escapeIdentifier($s). ' = ' . $select->escape($mixed));
    $select->setWhere($select->escapeIdentifier($s). ' LIKE "' . $select->escapeLikeWildcards($mixed) . '"');
    $select->setWhere('id = ' . $numericS);
    $select->setWhere('id = ' . $i);
    $select->setWhere('id = ' . $f);
    $select->setWhere('id = ' . $b);
    $select->setQuery('SELECT * FROM rex_article WHERE id = ' . $i);
    $select->setQuery('INSERT INTO '.rex::getTablePrefix() . 'media_manager_type (status, name, description) SELECT 0, CONCAT(name, \' '.rex_i18n::msg('media_manager_type_name_copy').'\'), description FROM '.rex::getTablePrefix() . 'media_manager_type WHERE id = ?', [$i]);

    // query via variable
    $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'metainfo_type WHERE id=' . $i . ' LIMIT 2';
    $select->getArray($qry);

    $select->setQuery('select * from ' . rex::getTablePrefix() . "article where path like '%|$i|%'");

    $parentIds = $select->in($arr);
    $select->setQuery('SELECT COUNT(*) as rowCount FROM rex_article WHERE id IN (' . $parentIds . ')');
}

function injection($_id, string $langID, array $arr): void
{
    $select = rex_sql::factory();
    $select->setTable('article');
    $select->setWhere('id = ' . $_id);
    $select->setWhere('id = ' . $langID);
    $select->setQuery('SELECT * FROM rex_article WHERE id = ' . $_id);
    $select->getArray('SELECT * FROM rex_article WHERE id = ' . $_id);
    $select->getDBArray('SELECT * FROM rex_article WHERE id = ' . $_id);

    // query via variable
    $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'metainfo_type WHERE id = ' . $_id;
    $select->getArray($qry);

    $select->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . implode(',',$arr) . ')');
}

class Good {
    protected const ORDER_ASC = 'ASC';
    protected const ORDER_DESC = 'DESC';

    /**
     * @var string
     */
    private $query;

    public function __construct(string $q) {
        $this->query = $q;
    }

    /**
     * @psalm-param self::ORDER_* $orderDirection
     */
    protected static function getSlicesWhere(array $params = [], string $orderDirection = 'ASC', ?int $limit = null)
    {
        $sql = rex_sql::factory();
        $query = '
            SELECT *
            FROM '.rex::getTable('article_slice').'
            ORDER BY ctype_id '.$orderDirection.', priority '.$orderDirection;

        if (null !== $limit) {
            $query .= ' LIMIT '.$limit;
        }

        $sql->setQuery($query, $params);
    }

    protected function propertyIsIgnored() {
        $sql = rex_sql::factory();
        $sql->setQuery($this->query);
    }
}
