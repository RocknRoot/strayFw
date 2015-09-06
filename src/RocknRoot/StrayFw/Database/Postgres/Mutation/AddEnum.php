<?php

namespace RocknRoot\StrayFw\Database\Postgres\Mutation;

use RocknRoot\StrayFw\Database\Database;

/**
 * Representation for enum type addition operations.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class AddEnum extends Mutation
{
    /**
     * Prepare and return according PDO statement.
     *
     * @param  Database     $database database
     * @param  string       $type     type name
     * @param  array        $values   enum values
     * @return PDOStatement $statement prepared query
     */
    public static function statement(Database $database, $type, array $values)
    {
        $statement = $database->getLink()->prepare('CREATE TYPE ' . $type . ' AS ENUM(\'' . implode('\', \'', $values) . '\')');

        return $statement;
    }
}
