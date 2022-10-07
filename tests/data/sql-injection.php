<?php

namespace RexSqlInjection;

use rex;
use rex_i18n;
use rex_sql;

function injection($mixed, string $langID, array $arr): void
{
    $select = rex_sql::factory();
    $select->select($mixed);
    $select->setRawValue('id', $mixed);
    $select->setWhere('id = ' . $mixed);
    $select->setWhere('id = ' . $langID);
    $select->prepareQuery($mixed);
    $select->setQuery('SELECT * FROM rex_article WHERE id = ' . $mixed);
    $select->getArray('SELECT * FROM rex_article WHERE id = ' . $mixed);
    $select->setDBQuery('SELECT * FROM rex_article WHERE id = ' . $mixed);
    $select->getDBArray('SELECT * FROM rex_article WHERE id = ' . $mixed);
    $select->setWhere('id LIKE "' . $select->escapeLikeWildcards($mixed) . '"'); // requires an additional ->escape() call

    // query via variable
    $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'metainfo_type WHERE id = ' . $mixed;
    $select->getArray($qry);

    $select->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . implode(',', $arr) . ')');
    $select->setQuery('SELECT COUNT(*) as rowCount FROM rex_article WHERE id IN (' . implode(', ', array_map('strval', $mixed)). ')');
    $select->setQuery(
        'SELECT COUNT(*) as rowCount FROM rex_article WHERE id IN (' .
        implode(', ', array_map(function ($post) { return (string) $post->id; }, $mixed))
        . ')'
    );
}

class DeepConcatError
{
    /**
     * Ermittelt die metainfo felder mit dem Prefix $prefix limitiert auf die Kategorien $restrictions.
     *
     * @param string $prefix          Feldprefix
     * @param string $filterCondition SQL Where-Bedingung zum einschränken der Metafelder
     *
     * @return rex_sql Metainfofelder
     */
    protected static function getSqlFields($prefix, $filterCondition = '')
    {
        // replace LIKE wildcards
        $prefix = str_replace(['_', '%'], ['\_', '\%'], $prefix);

        $qry = 'SELECT
                            *
                        FROM
                            ' . rex::getTablePrefix() . 'metainfo_field p,
                            ' . rex::getTablePrefix() . 'metainfo_type t
                        WHERE
                            `p`.`type_id` = `t`.`id` AND
                            `p`.`name` LIKE "' . $prefix . '%"
                            ' . $filterCondition . '
                            ORDER BY
                            priority';

        $sqlFields = rex_sql::factory();
        //$sqlFields->setDebug();
        $sqlFields->setQuery($qry);

        return $sqlFields;
    }
}

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
 * @param int[] $intArr
 * @return void
 */
function safeScalars($mixed, string $s, $numericS, int $i, float $f, bool $b, array $arr, $intArr)
{
    $select = rex_sql::factory();
    $select->setTable('article');
    $select->setWhere('id = ' . $select->escape($s));
    $select->setWhere('id = ' . $select->escape($mixed));
    $select->setWhere($select->escapeIdentifier($s). ' = ' . $select->escape($mixed));
    $select->setWhere('id LIKE "' . $select->escape($select->escapeLikeWildcards($mixed)) . '"');
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

    $select->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . implode(',', $intArr) . ')');

    $select->setQuery(
        'SELECT COUNT(*) as rowCount FROM rex_article WHERE id IN (' .
        implode(', ', array_map(function ($post) { return (int) $post->id; }, $mixed))
        . ')'
    );
    $select->setQuery('SELECT COUNT(*) as rowCount FROM rex_article WHERE id IN (' . implode(', ', array_map('intval', $mixed)). ')');
    $select->setQuery('OPTIMIZE TABLE ' . implode(', ', array_map([$select, 'escapeIdentifier'], $mixed)));
}

class Good
{
    protected const ORDER_ASC = 'ASC';
    protected const ORDER_DESC = 'DESC';

    /**
     * @var string
     */
    private $query;

    public function __construct(string $q)
    {
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

    protected function propertyIsIgnored()
    {
        $sql = rex_sql::factory();
        $sql->setQuery($this->query);
    }
}
