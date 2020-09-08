<?php

namespace RocknRoot\StrayFw\Database\Postgres\Query;

use RocknRoot\StrayFw\Exception\AppException;
use RocknRoot\StrayFw\Logger;

/**
 * Representation class for PostgreSQL schema mutation queries.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Mutation extends Query
{
    /**
     * Construct a new mutation query.
     *
     * @param string        $database  database name
     * @param \PDOStatement $statement PDO statement
     */
    public function __construct(string $database, \PDOStatement $statement)
    {
        parent::__construct($database);
        $this->statement = $statement;
    }

    /**
     * Execute the query.
     *
     * @throws AppException if statement is unset
     * @throws AppException on SQL error
     * @return bool         true if the query is successfully executed
     */
    public function execute() : bool
    {
        if (!$this->statement) {
            throw new AppException('Database/Postgres/Query/Mutation.execute: statement is unset');
        }
        $result = $this->statement->execute();
        $this->errorInfo = $this->statement->errorInfo();
        if ($this->getErrorState() != '00000') {
            Logger::get()->error('mutation query failed : ' . $this->getErrorMessage() . ' (' . $this->toSql() . ')');
            if (\constant('STRAY_ENV') === 'development') {
                throw new AppException('mutation query failed : ' . $this->getErrorMessage() . ' (' . $this->toSql() . ')');
            }
        }

        return $result;
    }

    /**
     * Extract the corresponding SQL code.
     *
     * @throws AppException if statement is unset
     * @return string       generated SQL code
     */
    public function toSql() : string
    {
        if (!$this->statement) {
            throw new AppException('Database/Postgres/Query/Mutation.execute: statement is unset');
        }
        return $this->statement->queryString;
    }
}
