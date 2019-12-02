<?php

namespace RocknRoot\StrayFw\Database\Postgres\Query;

/**
 * Parent class for PostgreSQL queries.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Query
{
    /**
     * Associated database name.
     *
     * @var string
     */
    protected $database;

    /**
     * Query parameters.
     *
     * @var mixed[]
     */
    protected $parameters;

    /**
     * PDO statement.
     *
     * @var \PDOStatement
     */
    protected $statement;

    /**
     * PDO error info.
     *
     * @var null|array
     */
    protected $errorInfo;

    /**
     * Construct a new empty query.
     *
     * @param string $database database name
     */
    public function __construct(string $database)
    {
        $this->database = $database;
        $this->parameters = array();
    }

    /**
     * Execute the constructed query.
     *
     * @abstract
     * @return bool true if the query is successfully executed
     */
    abstract public function execute() : bool;

    /**
     * Extract the corresponding SQL code.
     *
     * @abstract
     * @return string generated SQL code
     */
    abstract public function toSql() : string;

    /**
     * Bind a parameter.
     *
     * @param string $name  parameter name
     * @param string $value parameter value
     */
    public function bind(string $name, string $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Get PDO statement.
     *
     * @return \PDOStatement statement
     */
    public function getStatement() : \PDOStatement
    {
        return $this->statement;
    }

    /**
     * Get PDO error state.
     *
     * @return null|string error state
     */
    public function getErrorState() : ?string
    {
        if (is_array($this->errorInfo) === false) {
            return null;
        }

        return $this->errorInfo[0];
    }

    /**
     * Get PDO error message.
     *
     * @return null|string error message
     */
    public function getErrorMessage() : ?string
    {
        if (is_array($this->errorInfo) === false) {
            return null;
        }

        return $this->errorInfo[2];
    }
}
