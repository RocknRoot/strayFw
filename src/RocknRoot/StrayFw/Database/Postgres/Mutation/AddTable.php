<?php

namespace RocknRoot\StrayFw\Database\Postgres\Mutation;

use RocknRoot\StrayFw\Database\Database;
use RocknRoot\StrayFw\Database\Helper;
use RocknRoot\StrayFw\Database\Postgres\Column;
use RocknRoot\StrayFw\Database\Postgres\Query\Mutation as MutationQuery;
use RocknRoot\StrayFw\Exception\InvalidSchemaDefinition;
use RocknRoot\StrayFw\Logger;

/**
 * Representation for table addition operations.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class AddTable extends Mutation
{
    /**
     * Prepare and return according PDO statement.
     *
     * @param  Database                $database  database
     * @param  array<string, mixed>    $schema    schema definition
     * @param  string                  $mapping   mapping name
     * @param  string                  $tableName table real name
     * @param  string                  $modelName model name
     * @throws InvalidSchemaDefinition if a model has no field
     * @return MutationQuery           $statement prepared query
     */
    public static function statement(Database $database, array $schema, string $mapping, string $tableName, string $modelName): MutationQuery
    {
        $tableDefinition = $schema[$modelName];
        if (isset($tableDefinition['fields']) === false) {
            throw new InvalidSchemaDefinition('model "' . $modelName . '" has no field');
        }
        $sql = 'CREATE TABLE ' . $tableName . ' (';
        $primary = array();
        foreach ($tableDefinition['fields'] as $fieldName => $fieldDefinition) {
            $fieldRealName = null;
            if (isset($fieldDefinition['name']) === true) {
                $fieldRealName = $fieldDefinition['name'];
            } else {
                $fieldRealName = Helper::codifyName($modelName) . '_' . Helper::codifyName($fieldName);
            }
            $sql .= Column::generateDefinition($schema, $mapping, $fieldRealName, $fieldDefinition);
            if (isset($fieldDefinition['primary']) === true && $fieldDefinition['primary'] === true) {
                $primary[] = $fieldRealName;
            }
            $sql .= ', ';
        }
        if (\count($primary) > 0) {
            $sql .= 'CONSTRAINT pk_' . $tableName . ' PRIMARY KEY (' . \implode(', ', $primary) . '), ';
        } else {
            Logger::get()->warning('table "' . $tableName . '" has no primary key');
        }
        $sql = \substr($sql, 0, -2) . ')';
        $statement = $database->getMasterLink()->prepare($sql);
        return new MutationQuery($database->getAlias(), $statement);
    }
}
