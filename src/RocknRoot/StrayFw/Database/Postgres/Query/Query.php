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
     */
    protected string $database;

    /**
     * Query parameters.
     *
     * @var array<string, mixed>
     */
    protected array $parameters = [];

    /**
     * PDO statement.
     */
    protected ?\PDOStatement $statement = null;

    /**
     * PDO error info.
     *
     * @var string[]
     */
    protected ?array $errorInfo = null;

    /**
     * Construct a new empty query.
     *
     * @param string $database database name
     */
    public function __construct(string $database)
    {
        $this->database = $database;
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
     * @param mixed  $value parameter value
     */
    public function bind(string $name, $value) : void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Get PDO statement.
     *
     * @return ?\PDOStatement statement
     */
    public function getStatement() : ?\PDOStatement
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
        if (\is_array($this->errorInfo) === false) {
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
        if (\is_array($this->errorInfo) === false) {
            return null;
        }

        return $this->errorInfo[2];
    }
}
