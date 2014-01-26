<?php

namespace ErrantWorks\StrayFw\Database\Postgres\Mutation;

use ErrantWorks\StrayFw\Database\Database;
use ErrantWorks\StrayFw\Database\Postgres\Mutation\Mutation;

/**
 * Representation for enum type deletion operations.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class DeleteEnum extends Mutation
{
    /**
     * Prepare and return according PDO statement.
     *
     * @param Database $database database
     * @param string $type type name
     * @return PDOStatement $statement prepared query
     */
    public static function statement(Database $database, $type)
    {
        $statement = $database->getLink()->prepare('DROP TYPE IF EXISTS t_' . $type);
        return $statement;
    }
}
