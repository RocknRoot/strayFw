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
     * @var array|null
     */
    protected $errorInfo;

    /**
     * Construct a new empty query.
     *
     * @param string $database database name
     */
    public function __construct($database)
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
    abstract public function execute();

    /**
     * Extract the corresponding SQL code.
     *
     * @abstract
     * @return string generated SQL code
     */
    abstract public function toSql();

    /**
     * Bind a parameter.
     *
     * @param string $name  parameter name
     * @param string $value parameter value
     */
    public function bind($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Get PDO statement.
     *
     * @return \PDOStatement statement
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * Get PDO error state.
     *
     * @return string|null error state
     */
    public function getErrorState()
    {
        if (is_array($this->errorInfo) === false) {
            return null;
        }

        return $this->errorInfo[0];
    }

    /**
     * Get PDO error message.
     *
     * @return string|null error message
     */
    public function getErrorMessage()
    {
        if (is_array($this->errorInfo) === false) {
            return null;
        }

        return $this->errorInfo[2];
    }
}
