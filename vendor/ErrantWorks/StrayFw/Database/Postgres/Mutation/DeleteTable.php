<?php

namespace ErrantWorks\StrayFw\Database\Postgres\Mutation;

use ErrantWorks\StrayFw\Database\Database;
use ErrantWorks\StrayFw\Database\Postgres\Mutation\Mutation;

/**
 * Representation for table deletion operations.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class DeleteTable extends Mutation
{
    /**
     * Prepare and return according PDO statement.
     *
     * @param Database $database database
     * @param string $table table name
     * @return PDOStatement $statement prepared query
     */
    public static function statement(Database $database, $table)
    {
        $statement = $database->getLink()->prepare('DROP TABLE IF EXISTS ' . $table . ' CASCADE');
        return $statement;
    }
}
