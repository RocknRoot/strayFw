<?php

namespace RocknRoot\StrayFw\Database\Postgres\Mutation;

use RocknRoot\StrayFw\Database\Database;
use RocknRoot\StrayFw\Database\Postgres\Query\Mutation as MutationQuery;

/**
 * Representation for foreign key deletion operations.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class DeleteForeignKey extends Mutation
{
    /**
     * Prepare and return according PDO statement.
     *
     * @param  Database      $database database
     * @param  string        $table    table name
     * @param  string        $key      foreign key name
     * @return MutationQuery $statement prepared query
     */
    public static function statement(Database $database, string $table, string $key): MutationQuery
    {
        $statement = $database->getMasterLink()->prepare('ALTER TABLE ' . $table . ' DROP CONSTRAINT IF EXISTS fk_' . $key);

        return new MutationQuery($database->getAlias(), $statement);
    }
}
