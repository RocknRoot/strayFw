<?php

namespace RocknRoot\StrayFw\Database\Postgres\Query;

/**
 * Representation class for PostgreSQL condition expressions.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Condition
{
    /**
     * Logic condition tree.
     *
     * @var mixed[]
     */
    protected array $tree;

    /**
     * Generated SQL code.
     */
    protected string $sql;

    /**
     * Construct a new condition expression.
     *
     * @param array|string $where where clause
     */
    public function __construct($where)
    {
        if (\is_array($where) === true) {
            $this->tree = $where;
        } else {
            $this->sql = $where;
        }
    }

    /**
     * Extract the corresponding SQL code.
     *
     * @return string generated SQL code
     */
    public function toSql() : string
    {
        if (\is_string($this->sql) === false) {
            $this->sql = $this->toSqlLevel($this->tree);
        }

        return $this->sql;
    }

    /**
     * Extract the corresponding SQL code, depth level by level.
     *
     * @param  mixed[] $tree one tree level
     * @return string  generated SQL code for this level
     */
    protected function toSqlLevel(array $tree) : string
    {
        if (\count($tree) == 0) {
            return '';
        }
        $sql = '(';
        \reset($tree);
        if (\is_numeric(\key($tree)) === true) {
            foreach ($tree as $elem) {
                $sql .= $elem . ' AND ';
            }
            $sql = \substr($sql, 0, -5);
        } elseif (\key($tree) === 'OR') {
            foreach ($tree as $value) {
                if (\is_array($value) === true) {
                    $sql .= $this->toSqlLevel($value);
                } else {
                    $sql .= $value;
                }
                $sql .= ' OR ';
            }
            $sql = \substr($sql, 0, -4);
        } elseif (\key($tree) === 'AND') {
            foreach ($tree as $value) {
                if (\is_array($value) === true) {
                    $sql .= $this->toSqlLevel($value);
                } else {
                    $sql .= $value;
                }
                $sql .= ' AND ';
            }
            $sql = \substr($sql, 0, -5);
        } else {
            foreach ($tree as $key => $value) {
                $sql .= $key . ' = ' . $value . ' AND ';
            }
            $sql = \substr($sql, 0, -5);
        }
        $sql .= ')';

        return $sql;
    }
}
