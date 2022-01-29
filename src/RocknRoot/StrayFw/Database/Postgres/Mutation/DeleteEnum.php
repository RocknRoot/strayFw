<?php

namespace RocknRoot\StrayFw\Database\Postgres\Mutation;

use RocknRoot\StrayFw\Database\Database;
use RocknRoot\StrayFw\Database\Postgres\Query\Mutation as MutationQuery;

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
     * @param  Database      $database database
     * @param  string        $type     type name
     * @return MutationQuery $statement prepared query
     */
    public static function statement(Database $database, string $type): MutationQuery
    {
        $statement = $database->getMasterLink()->prepare('DROP TYPE IF EXISTS ' . $type);
        return new MutationQuery($database->getAlias(), $statement);
    }
}
