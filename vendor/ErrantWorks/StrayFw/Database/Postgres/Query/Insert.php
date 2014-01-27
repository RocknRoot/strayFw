<?php

namespace ErrantWorks\StrayFw\Database\Postgres\Query;

use ErrantWorks\StrayFw\Database\Database;
use ErrantWorks\StrayFw\Database\Postgres\Query\Query;
use ErrantWorks\StrayFw\Exception\BadUse;
use ErrantWorks\StrayFw\Logger;

/**
 * Representation class for PostgreSQL insert queries.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Insert extends Query
{
    /**
     * Into clause.
     *
     * @var string
     */
    protected $into;

    /**
     * Values clause.
     *
     * @var string
     */
    protected $values;

    /**
     * Returning clause.
     *
     * @var string
     */
    protected $returning;

    /**
     * Execute the constructed query.
     *
     * @return bool true if the query is successfully executed
     */
    public function execute()
    {
        if ($this->statement == null) {
            $this->statement = Database::get($this->database)->getMasterLink()->prepare($this->toSql());
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
            Logger::get()->error('insert query failed : ' . $this->getErrorMessage() . ' (' . $this->toSql() . ')');
        }

        return $result;
    }

    /**
     * Extract the corresponding SQL code.
     *
     * @throws BadUse if into clause has not been defined
     * @return string generated SQL code
     */
    public function toSql()
    {
        if (empty($this->into) === true) {
            throw new BadUse('into clause has not been defined (' . print_r($this, true) . ')');
        }
        $sql = 'INSERT INTO ' . $this->into . ' ';

        if ($this->values[0] != null) {
            $sql .= '(' . $this->values[0] . ') ';
        }
        if ($this->values[1] != null) {
            $sql .= 'VALUES (' . $this->values[1] . ') ';
        } else {
            $sql .= 'DEFAULT VALUES ';
        }

        if ($this->returning != null) {
            $sql .= 'RETURNING ' . $this->returning;
        }

        return $sql;
    }

    /**
     * Set into clause.
     *
     * @param  string $table table real name
     * @return Insert this
     */
    public function into($table)
    {
        $this->into = $table;

        return $this;
    }

    /**
     * Set values clause.
     *
     * @param  array|string $values values clause
     * @return Insert       this
     */
    public function values($values)
    {
        if (is_array($values) === true) {
            if (is_numeric(key($values)) === true) {
                $this->values = array(
                    null,
                    implode(', ', $values)
                );
            } else {
                $this->values = array(null, null);
                foreach ($values as $key => $value) {
                    if (stripos($key, '.') !== false) {
                        $key = substr($key, stripos($key, '.') + 1);
                    }
                    $this->values[0] .= $key . ', ';
                    $this->values[1] .= $value . ', ';
                }
                $this->values[0] = substr($this->values[0], 0, -2);
                $this->values[1] = substr($this->values[1], 0, -2);
            }
        } else {
            $this->values = array(
                null,
                $values
            );
        }

        return $this;
    }

    /**
     * Set returning clause.
     *
     * @param  array|string $returning returning clause
     * @return Insert       this
     */
    public function returning($returning)
    {
        if (is_array($returning) === true) {
            $this->returning = implode(', ', $returning);
        } else {
            $this->returning = $returning;
        }

        return $this;
    }
}
