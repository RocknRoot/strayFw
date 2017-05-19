<?php

namespace RocknRoot\StrayFw\Database\Postgres\Mutation;

use RocknRoot\StrayFw\Database\Database;

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
     * @param  Database     $database database
     * @param  string       $table    table name
     * @return \PDOStatement $statement prepared query
     */
    public static function statement(Database $database, $table)
    {
        $statement = $database->getMasterLink()->prepare('DROP TABLE IF EXISTS ' . $table . ' CASCADE');

        return $statement;
    }
}
