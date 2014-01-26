<?php

namespace ErrantWorks\StrayFw\Database\Postgres\Mutation;

use ErrantWorks\StrayFw\Database\Database;
use ErrantWorks\StrayFw\Database\Postgres\Mutation\Mutation;

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
        $statement = $database->getLink()->prepare('CREATE TYPE ' . $type . ' AS ENUM(:values)');
        $statement->bindValue('values', '\'' . implode('\', \'', $values) . '\'');

        return $statement;
    }
}
