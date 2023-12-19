<?php

namespace RocknRoot\StrayFw\Database\Postgres\Query;

use InvalidArgumentException;
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
     */
    protected bool $isCritical;

    /**
     * Select clause.
     */
    protected ?string $select = null;

    /**
     * From clause.
     */
    protected ?string $from = null;

    /**
     * Where clause.
     */
    protected ?\RocknRoot\StrayFw\Database\Postgres\Query\Condition $where = null;

    /**
     * Group by clause.
     */
    protected ?string $groupBy = null;

    /**
     * Having clause.
     */
    protected ?\RocknRoot\StrayFw\Database\Postgres\Query\Condition $having = null;

    /**
     * Order by clause.
     */
    protected ?string $orderBy = null;

    /**
     * Distinct clause.
     */
    protected ?string $distinct = null;

    /**
     * Limit clause.
     */
    protected ?int $limit = null;

    /**
     * Offset clause.
     */
    protected ?int $offset = null;

    /**
     * Inner join table clause.
     *
     * @var array[]
     */
    protected array $innerJoins = array();

    /**
     * Left outer join table clause.
     *
     * @var array[]
     */
    protected array $leftOuterJoins = array();

    /**
     * Right outer join table clause.
     *
     * @var array[]
     */
    protected array $rightOuterJoins = array();

    /**
     * Full outer join table clause.
     *
     * @var array[]
     */
    protected array $fullOuterJoins = array();

    /**
     * Construct a new empty select query.
     *
     * @param string $database database name
     * @param bool   $critical if true, will be executed on write server
     */
    public function __construct(string $database, bool $critical = false)
    {
        parent::__construct($database);
        $this->isCritical = $critical;
    }

    /**
     * Execute the constructed query.
     *
     * @throws AppException on SQL error
     * @return bool         true if the query is successfully executed
     */
    public function execute(): bool
    {
        if ($this->statement == null) {
            $this->statement = Database::get($this->database)->{($this->isCritical === true ? 'getMasterLink' : 'getLink')}()->prepare($this->toSql());
        }
        foreach ($this->parameters as $name => $value) {
            $type = \PDO::PARAM_STR;
            if (\is_int($value) === true) {
                $type = \PDO::PARAM_INT;
            } elseif (\is_bool($value) === true) {
                $type = \PDO::PARAM_BOOL;
            } elseif (\is_null($value) === true) {
                $type = \PDO::PARAM_NULL;
            }
            $this->statement->bindValue($name, $value, $type);
        }
        $result = $this->statement->execute();
        $this->errorInfo = $this->statement->errorInfo();
        if ($this->getErrorState() != '00000') {
            Logger::get()->error('select query failed : ' . $this->getErrorMessage() . ' (' . $this->toSql() . ')');
            if (\constant('STRAY_ENV') === 'development') {
                throw new AppException('select query failed : ' . $this->getErrorMessage() . ' (' . $this->toSql() . ')');
            }
        }
        return $result;
    }

    /**
     * Get the next result row.
     *
     * @throws BadUse if statement is null or after SQL error
     * @return mixed  result data or false if something went wrong
     */
    public function fetch()
    {
        if ($this->statement == null) {
            throw new BadUse('Database\Postgres/Query/Select.fetchAll: statement is null');
        }
        if ($this->getErrorState() != '00000') {
            throw new BadUse('Database\Postgres/Query/Select.fetchAll: cannot fetch results after a SQL error');
        }
        return $this->statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all result rows.
     *
     * @throws BadUse  if statement is null or after SQL error
     * @return mixed[] results data
     */
    public function fetchAll(): array
    {
        if ($this->statement == null) {
            throw new BadUse('Database\Postgres/Query/Select.fetchAll: statement is null');
        }
        if ($this->getErrorState() != '00000') {
            throw new BadUse('Database\Postgres/Query/Select.fetchAll: cannot fetch results after a SQL error');
        }

        $res = $this->statement->fetchAll(\PDO::FETCH_ASSOC);
        if (!$res) {
            return [];
        }
        return $res;
    }

    /**
     * Extract the corresponding SQL code.
     *
     * @throws BadUse if from clause has not been defined
     * @return string generated SQL code
     */
    public function toSql(): string
    {
        $sql = 'SELECT ';
        if ($this->distinct != null) {
            $sql .= 'DISTINCT ON (' . $this->distinct . ') ';
        }
        $sql .= ($this->select != null ? $this->select : '*') . ' ';
        if (empty($this->from) === true) {
            throw new BadUse('from clause has not been defined (' . \print_r($this, true) . ')');
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
     * @param  array<string, string>|string $select select clause
     * @return Select                       this
     */
    public function select($select): self
    {
        if (\is_array($select) === true) {
            $this->select = '';
            foreach ($select as $key => $elem) {
                $this->select .= $elem;
                if (\is_numeric($key) === false) {
                    $this->select .= ' AS ' . $key;
                }
                $this->select .= ', ';
            }
            $this->select = \substr($this->select, 0, -2);
        } elseif (!\is_string($select)) {
            throw new InvalidArgumentException(\sprintf(
                'Argument 1 passed to %s must be an array or string!',
                __METHOD__
            ));
        } else {
            $this->select = $select;
        }
        return $this;
    }

    /**
     * Set from clause.
     *
     * @param  string $from table real name
     * @return Select this
     */
    public function from(string $from): self
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
    public function where($where): self
    {
        $this->where = ($where instanceof Condition ? $where : new Condition($where));
        return $this;
    }

    /**
     * Set group by clause.
     *
     * @param  string|string[] $groupBy group by clause
     * @return Select          this
     */
    public function groupBy($groupBy): self
    {
        if (\is_array($groupBy) === true) {
            $this->groupBy = \implode(', ', $groupBy);
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
    public function having($having): self
    {
        $this->having = ($having instanceof Condition ? $having : new Condition($having));
        return $this;
    }

    /**
     * Set order by clause.
     *
     * @param  string|string[] $orderBy order by clause
     * @return Select          this
     */
    public function orderBy($orderBy): self
    {
        if (\is_array($orderBy) === true) {
            $this->orderBy = '';
            foreach ($orderBy as $key => $elem) {
                $this->orderBy .= $key . ' ' . $elem . ', ';
            }
            $this->orderBy = \substr($this->orderBy, 0, -2);
        } else {
            $this->orderBy = $orderBy;
        }
        return $this;
    }

    /**
     * Set distinct on clause.
     *
     * @param  string|string[] $distinct distinct on clause
     * @return Select          this
     */
    public function distinct($distinct): self
    {
        if (\is_array($distinct) === true) {
            $this->distinct = \implode(', ', $distinct);
        } else {
            $this->distinct = $distinct;
        }
        return $this;
    }

    /**
     * Set limit clause.
     *
     * @param  null|int $limit limit clause
     * @return Select   this
     */
    public function limit(int $limit = null): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set offset clause.
     *
     * @param  null|int $offset offset clause
     * @return Select   this
     */
    public function offset(int $offset = null): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Add an inner join.
     *
     * @param  string                   $table foreign table real name
     * @param  Condition|mixed[]|string $on    join condition
     * @return Select                   this
     */
    public function addInnerJoin(string $table, $on): self
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
     * @param  string                   $table foreign table real name
     * @param  Condition|mixed[]|string $on    join condition
     * @return Select                   $this
     */
    public function addLeftOuterJoin(string $table, $on): self
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
     * @param  string                   $table foreign table real name
     * @param  Condition|mixed[]|string $on    join condition
     * @return Select                   $this
     */
    public function addRightOuterJoin(string $table, $on): self
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
     * @param  string                   $table foreign table real name
     * @param  Condition|mixed[]|string $on    join condition
     * @return Select                   $this
     */
    public function addFullOuterJoin(string $table, $on): self
    {
        $this->fullOuterJoins[] = array(
            'table' => $table,
            'on' => ($on instanceof Condition ? $on : new Condition($on))
        );
        return $this;
    }
}
