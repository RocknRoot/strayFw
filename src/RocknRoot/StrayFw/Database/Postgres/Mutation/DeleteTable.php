<?php

namespace RocknRoot\StrayFw\Database\Postgres\Mutation;

use RocknRoot\StrayFw\Database\Database;
use RocknRoot\StrayFw\Database\Postgres\Query\Mutation as MutationQuery;

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
     * @param  Database      $database database
     * @param  string        $table    table name
     * @return MutationQuery $statement prepared query
     */
    public static function statement(GlobalDatabase $database, string $table) : MutationQuery
    {
        $statement = $database->getMasterLink()->prepare('DROP TABLE IF EXISTS ' . $table . ' CASCADE');

        return new MutationQuery($database->getAlias(), $statement);
    }
}
