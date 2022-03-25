<?php

namespace RocknRoot\StrayFw\Database\Postgres\Query;

use RocknRoot\StrayFw\Database\Database;
use RocknRoot\StrayFw\Exception\AppException;
use RocknRoot\StrayFw\Exception\BadUse;
use RocknRoot\StrayFw\Logger;

/**
 * Representation class for PostgreSQL delete queries.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Delete extends Query
{
    /**
     * From clause.
     */
    protected ?string $from = null;

    /**
     * Where clause.
     */
    protected ?\RocknRoot\StrayFw\Database\Postgres\Query\Condition $where = null;

    /**
     * Order by clause.
     */
    protected ?string $orderBy = null;

    /**
     * Limit clause.
     */
    protected ?string $limit = null;

    /**
     * Using clause.
     */
    protected ?string $using = null;

    /**
     * Execute the constructed query.
     *
     * @throws AppException on SQL error
     * @return bool         true if the query is successfully executed
     */
    public function execute(): bool
    {
        if ($this->statement == null) {
            $this->statement = Database::get($this->database)->getMasterLink()->prepare($this->toSql());
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
            Logger::get()->error('delete query failed : ' . $this->getErrorMessage() . ' (' . $this->toSql() . ')');
            if (\constant('STRAY_ENV') === 'development') {
                throw new AppException('delete query failed : ' . $this->getErrorMessage() . ' (' . $this->toSql() . ')');
            }
        }
        return $result;
    }

    /**
     * Extract the corresponding SQL code.
     *
     * @throws BadUse if from clause has not been defined
     * @return string generated SQL code
     */
    public function toSql(): string
    {
        if (empty($this->from) === true) {
            throw new BadUse('from clause has not been defined (' . \print_r($this, true) . ')');
        }

        $sql = 'DELETE FROM ' . $this->from . ' ';
        if ($this->using != null) {
            $sql .= 'USING ' . $this->using . ' ';
        }
        if ($this->where != null) {
            $sql .= 'WHERE ' . $this->where->toSql() . ' ';
        }
        if ($this->orderBy != null) {
            $sql .= 'ORDER BY ' . $this->orderBy . ' ';
        }
        if ($this->limit != null) {
            $sql .= 'LIMIT ' . $this->limit . ' ';
        }
        return $sql;
    }

    /**
     * Set from clause.
     *
     * @param  string $table table real name
     * @return Delete this
     */
    public function from(string $table): self
    {
        $this->from = $table;
        return $this;
    }

    /**
     * Set where clause.
     *
     * @param  mixed[]|\RocknRoot\StrayFw\Database\Postgres\Query\Condition|string $where where clause
     * @return Delete                                                              this
     */
    public function where($where): self
    {
        $this->where = ($where instanceof Condition ? $where : new Condition($where));
        return $this;
    }

    /**
     * Set order by clause.
     *
     * @param  array<string, string>|string $orderBy order by clause
     * @return Delete                       this
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
     * Set limit clause.
     *
     * @param  string $limit limit clause
     * @return Delete this
     */
    public function limit(string $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set using clause.
     *
     * @param  array<string, string>|string $using using clause
     * @return Delete                       this
     */
    public function using(string|array $using): self
    {
        if (\is_array($using) === true) {
            $this->using = '';
            foreach ($using as $key => $elem) {
                $this->using .= $key . ' ' . $elem . ', ';
            }
            $this->using = \substr($this->using, 0, -2);
        } else {
            $this->using = $using;
        }
        return $this;
    }
}
