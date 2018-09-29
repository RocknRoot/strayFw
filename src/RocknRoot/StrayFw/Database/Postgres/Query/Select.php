<?php

namespace RocknRoot\StrayFw\Database\Postgres\Query;

use RocknRoot\StrayFw\Database\Database;
use RocknRoot\StrayFw\Exception\AppException;
use RocknRoot\StrayFw\Exception\BadUse;
use RocknRoot\StrayFw\Logger;

/**
 * Representation class for PostgreSQL select queries.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Select extends Query
{
    /**
     * If true, will be executed on write server.
     *
     * @var bool
     */
    protected $isCritical;

    /**
     * Select clause.
     *
     * @var string
     */
    protected $select;

    /**
     * From clause.
     *
     * @var string
     */
    protected $from;

    /**
     * Where clause.
     *
     * @var Condition
     */
    protected $where;

    /**
     * Group by clause.
     *
     * @var string
     */
    protected $groupBy;

    /**
     * Having clause.
     *
     * @var Condition
     */
    protected $having;

    /**
     * Order by clause.
     *
     * @var string
     */
    protected $orderBy;

    /**
     * Distinct clause.
     *
     * @var string
     */
    protected $distinct;

    /**
     * Limit clause.
     *
     * @var string
     */
    protected $limit;

    /**
     * Offset clause.
     *
     * @var string
     */
    protected $offset;

    /**
     * Inner join table clause.
     *
     * @var array[]
     */
    protected $innerJoins;

    /**
     * Left outer join table clause.
     *
     * @var array[]
     */
    protected $leftOuterJoins;

    /**
     * Right outer join table clause.
     *
     * @var array[]
     */
    protected $rightOuterJoins;

    /**
     * Full outer join table clause.
     *
     * @var array[]
     */
    protected $fullOuterJoins;

    /**
     * Construct a new empty select query.
     *
     * @param string $database database name
     * @param bool   $critical if true, will be executed on write server
     */
    public function __construct($database, $critical = false)
    {
        parent::__construct($database);
        $this->isCritical = $critical;
        $this->innerJoins = array();
        $this->leftOuterJoins = array();
        $this->rightOuterJoins = array();
        $this->fullOuterJoins = array();
    }

    /**
     * Execute the constructed query.
     *
     * @throws AppException on SQL error
     * @return bool         true if the query is successfully executed
     */
    public function execute()
    {
        if ($this->statement == null) {
            $this->statement = Database::get($this->database)->{($this->isCritical === true ? 'getMasterLink' : 'getLink')}()->prepare($this->toSql());
        }
        foreach ($this->parameters as $name => $value) {
            $type = \PDO::PARAM_STR;
            if (is_int($value) === true) {
                $type = \PDO::PARAM_INT;
            } elseif (is_bool($value) === true) {
                $type = \PDO::PARAM_BOOL;
            } elseif (is_null($value) === true) {
                $type = \PDO::PARAM_NULL;
            }
            $this->statement->bindValue($name, $value, $type);
        }
        $result = $this->statement->execute();
        $this->errorInfo = $this->statement->errorInfo();
        if ($this->getErrorState() != '00000') {
            Logger::get()->error('select query failed : ' . $this->getErrorMessage() . ' (' . $this->toSql() . ')');
            if (constant('STRAY_ENV') === 'development') {
                throw new AppException('select query failed : ' . $this->getErrorMessage() . ' (' . $this->toSql() . ')');
            }
        }

        return $result;
    }

    /**
     * Get the next result row.
     *
     * @return bool|array result data or false if something went wrong
     */
    public function fetch()
    {
        if ($this->statement == null || $this->getErrorState() != '00000') {
            return false;
        }

        return $this->statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all result rows.
     *
     * @return bool|array results data or false if something went wrong
     */
    public function fetchAll()
    {
        if ($this->statement == null || $this->getErrorState() != '00000') {
            return false;
        }

        return $this->statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Extract the corresponding SQL code.
     *
     * @throws BadUse if from clause has not been defined
     * @return string generated SQL code
     */
    public function toSql()
    {
        $sql = 'SELECT ';
        if ($this->distinct != null) {
            $sql .= 'DISTINCT ON (' . $this->distinct . ') ';
        }
        $sql .= ($this->select != null ? $this->select : '*') . ' ';
        if (empty($this->from) === true) {
            throw new BadUse('from clause has not been defined (' . print_r($this, true) . ')');
        }
        $sql .= 'FROM ' . $this->from . ' ';

        foreach ($this->innerJoins as $join) {
            $sql .= 'INNER JOIN ' . $join['table'] . ' ON ' . $join['on']->toSql() . ' ';
        }
        foreach ($this->leftOuterJoins as $join) {
            $sql .= 'LEFT OUTER JOIN ' . $join['table'] . ' ON ' . $join['on']->toSql() . ' ';
        }
        foreach ($this->rightOuterJoins as $join) {
            $sql .= 'RIGHT OUTER JOIN ' . $join['table'] . ' ON ' . $join['on']->toSql() . ' ';
        }
        foreach ($this->fullOuterJoins as $join) {
            $sql .= 'FULL OUTER JOIN ' . $join['table'] . ' ON ' . $join['on']->toSql() . ' ';
        }

        if ($this->where != null) {
            $sql .= 'WHERE ' . $this->where->toSql() . ' ';
        }
        if ($this->groupBy != null) {
            $sql .= 'GROUP BY ' . $this->groupBy . ' ';
        }
        if ($this->having != null) {
            $sql .= 'HAVING ' . $this->having->toSql() . ' ';
        }
        if ($this->orderBy != null) {
            $sql .= 'ORDER BY ' . $this->orderBy . ' ';
        }
        if ($this->limit != null) {
            $sql .= 'LIMIT ' . $this->limit . ' ';
        }
        if ($this->offset != null) {
            $sql .= 'OFFSET ' . $this->offset . ' ';
        }

        return $sql;
    }

    /**
     * Set select clause.
     *
     * @param  array|string $select select clause
     * @return Select       this
     */
    public function select($select)
    {
        if (is_array($select) === true) {
            $this->select = null;
            foreach ($select as $key => $elem) {
                $this->select .= $elem;
                if (is_numeric($key) === false) {
                    $this->select .= ' AS ' . $key;
                }
                $this->select .= ', ';
            }
            $this->select = substr($this->select, 0, -2);
        } else {
            $this->select = (string) $select;
        }

        return $this;
    }

    /**
     * Set from clause.
     *
     * @param  string $from table real name
     * @return Select this
     */
    public function from($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Set where clause.
     *
     * @param  Condition|mixed $where where clause
     * @return Select          this
     */
    public function where($where)
    {
        $this->where = ($where instanceof Condition ? $where : new Condition($where));

        return $this;
    }

    /**
     * Set group by clause.
     *
     * @param  array|string $groupBy group by clause
     * @return Select       this
     */
    public function groupBy($groupBy)
    {
        if (is_array($groupBy) === true) {
            $this->groupBy = implode(', ', $groupBy);
        } else {
            $this->groupBy = $groupBy;
        }

        return $this;
    }

    /**
     * Set having clause.
     *
     * @param  Condition|string $having having clause
     * @return Select           this
     */
    public function having($having)
    {
        $this->having = ($having instanceof Condition ? $having : new Condition($having));

        return $this;
    }

    /**
     * Set order by clause.
     *
     * @param  array|string $orderBy order by clause
     * @return Select       this
     */
    public function orderBy($orderBy)
    {
        if (is_array($orderBy) === true) {
            $this->orderBy = null;
            foreach ($orderBy as $key => $elem) {
                $this->orderBy .= $key . ' ' . $elem . ', ';
            }
            $this->orderBy = substr($this->orderBy, 0, -2);
        } else {
            $this->orderBy = $orderBy;
        }

        return $this;
    }

    /**
     * Set distinct on clause.
     *
     * @param  array|string $distinct distinct on clause
     * @return Select       this
     */
    public function distinct($distinct)
    {
        if (is_array($distinct) === true) {
            $this->distinct = implode(', ', $distinct);
        } else {
            $this->distinct = $distinct;
        }

        return $this;
    }

    /**
     * Set limit clause.
     *
     * @param  string $limit limit clause
     * @return Select this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Set offset clause.
     *
     * @param  string $offset offset clause
     * @return Select this
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Add an inner join.
     *
     * @param  string                 $table foreign table real name
     * @param  Condition|array|string $on    join condition
     * @return Select                 this
     */
    public function addInnerJoin($table, $on)
    {
        $this->innerJoins[] = array(
            'table' => $table,
            'on' => ($on instanceof Condition ? $on : new Condition($on))
        );

        return $this;
    }

    /**
     * Add a left outer join.
     *
     * @param  string                 $table foreign table real name
     * @param  Condition|array|string $on    join condition
     * @return Select                 $this
     */
    public function addLeftOuterJoin($table, $on)
    {
        $this->leftOuterJoins[] = array(
            'table' => $table,
            'on' => ($on instanceof Condition ? $on : new Condition($on))
        );

        return $this;
    }

    /**
     * Add a right outer join.
     *
     * @param  string                 $table foreign table real name
     * @param  Condition|array|string $on    join condition
     * @return Select                 $this
     */
    public function addRightOuterJoin($table, $on)
    {
        $this->rightOuterJoins[] = array(
            'table' => $table,
            'on' => ($on instanceof Condition ? $on : new Condition($on))
        );

        return $this;
    }

    /**
     * Add a full outer join.
     *
     * @param  string                 $table foreign table real name
     * @param  Condition|array|string $on    join condition
     * @return Select                 $this
     */
    public function addFullOuterJoin($table, $on)
    {
        $this->fullOuterJoins[] = array(
            'table' => $table,
            'on' => ($on instanceof Condition ? $on : new Condition($on))
        );

        return $this;
    }
}
