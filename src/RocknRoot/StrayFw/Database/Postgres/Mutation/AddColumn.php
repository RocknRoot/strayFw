<?php

namespace RocknRoot\StrayFw\Database\Postgres\Mutation;

use RocknRoot\StrayFw\Database\Database;
use RocknRoot\StrayFw\Database\Helper;
use RocknRoot\StrayFw\Database\Postgres\Column;
use RocknRoot\StrayFw\Database\Postgres\Query\Mutation as MutationQuery;

/**
 * Representation for column addition operations.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class AddColumn extends Mutation
{
    /**
     * Prepare and return according PDO statement.
     *
     * @param  Database             $database  database
     * @param  array<string, mixed> $schema    schema definition
     * @param  string               $mapping   mapping name
     * @param  string               $modelName model name
     * @param  string               $tableName table name
     * @param  string               $fieldName field name
     * @return MutationQuery        $statement prepared query
     */
    public static function statement(Database $database, array $schema, string $mapping, string $modelName, string $tableName, string $fieldName) : MutationQuery
    {
        $fieldDefinition = $schema[$modelName]['fields'][$fieldName];
        $fieldRealName = null;
        if (isset($fieldDefinition['name']) === true) {
            $fieldRealName = $fieldDefinition['name'];
        } else {
            $fieldRealName = Helper::codifyName($modelName) . '_' . Helper::codifyName($fieldName);
        }
        $sql = 'ALTER TABLE ' . $tableName . ' ADD COLUMN ';
        $sql .= Column::generateDefinition($schema, $mapping, $fieldRealName, $fieldDefinition);
        $statement = $database->getMasterLink()->prepare($sql);

        return new MutationQuery($database->getAlias(), $statement);
    }
}
