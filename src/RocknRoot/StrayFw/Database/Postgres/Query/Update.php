<?php

namespace RocknRoot\StrayFw\Database\Postgres\Query;

use RocknRoot\StrayFw\Database\Database;
use RocknRoot\StrayFw\Exception\AppException;
use RocknRoot\StrayFw\Exception\BadUse;
use RocknRoot\StrayFw\Logger;

/**
 * Representation class for PostgreSQL update queries.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Update extends Query
{
    /**
     * Update clause.
     */
    protected ?string $update = null;

    /**
     * Set clause.
     */
    protected ?string $set = null;

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
     * Execute the constructed query.
     *
     * @throws AppException on SQL error
     * @return bool         true if the query is successfully executed
     */
    public function execute() : bool
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
            Logger::get()->error('update query failed : ' . $this->getErrorMessage() . ' (' . $this->toSql() . ')');
            if (\constant('STRAY_ENV') === 'development') {
                throw new AppException('update query failed : ' . $this->getErrorMessage() . ' (' . $this->toSql() . ')');
            }
        }

        return $result;
    }

    /**
     * Extract the corresponding SQL code.
     *
     * @throws BadUse if update clause has not been defined
     * @return string generated SQL code
     */
    public function toSql() : string
    {
        if (empty($this->update) === true) {
            throw new BadUse('update clause has not been defined (' . \print_r($this, true) . ')');
        }
        $sql = 'UPDATE ' . $this->update . ' ';
        $sql .= 'SET ' . $this->set . ' ';

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
     * Set update clause.
     *
     * @param  string $table table real name
     * @return Update this
     */
    public function update(string $table) : self
    {
        $this->update = $table;

        return $this;
    }

    /**
     * Set set clause.
     *
     * @param  array<string, mixed>|string $set set clause
     * @return Update                      this
     */
    public function set($set) : self
    {
        if (\is_array($set) === true) {
            $this->set = '';
            foreach ($set as $name => $value) {
                $pos = \stripos($name, '.');
                if ($pos !== false) {
                    $this->set .= \substr($name, $pos + 1);
                } else {
                    $this->set .= $name;
                }
                $this->set .= ' = ' . $value . ', ';
            }
            $this->set = \substr($this->set, 0, -2);
        } else {
            $this->set = $set;
        }

        return $this;
    }

    /**
     * Set where clause.
     *
     * @param  array<string, mixed>|\RocknRoot\StrayFw\Database\Postgres\Query\Condition|string $where where clause
     * @return Update                                                                           this
     */
    public function where($where) : self
    {
        $this->where = ($where instanceof Condition ? $where : new Condition($where));

        return $this;
    }

    /**
     * Set order by clause.
     *
     * @param  string|string[] $orderBy order by clause
     * @return Update          this
     */
    public function orderBy($orderBy) : self
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
     * @return Update this
     */
    public function limit(string $limit) : self
    {
        $this->limit = $limit;

        return $this;
    }
}
