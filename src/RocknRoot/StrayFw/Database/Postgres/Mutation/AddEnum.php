<?php

namespace RocknRoot\StrayFw\Database\Postgres\Mutation;

use RocknRoot\StrayFw\Database\Database;
use RocknRoot\StrayFw\Database\Postgres\Query\Mutation as MutationQuery;

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
     * @param  Database      $database database
     * @param  string        $type     type name
     * @param  string[]      $values   enum values
     * @return MutationQuery $statement prepared query
     */
    public static function statement(Database $database, string $type, array $values): MutationQuery
    {
        $statement = $database->getMasterLink()->prepare('CREATE TYPE ' . $type . ' AS ENUM(\'' . \implode('\', \'', $values) . '\')');
        return new MutationQuery($database->getAlias(), $statement);
    }
}
