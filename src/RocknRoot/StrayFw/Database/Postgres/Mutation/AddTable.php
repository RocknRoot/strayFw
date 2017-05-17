<?php

namespace RocknRoot\StrayFw\Database\Postgres\Mutation;

use RocknRoot\StrayFw\Database\Database;
use RocknRoot\StrayFw\Database\Helper;
use RocknRoot\StrayFw\Database\Postgres\Column;
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
     * @throws InvalidSchemaDefinition if a model has no field
     * @param  Database                $database        database
     * @param  array                   $schema          schema definition
     * @param  string                  $mapping         mapping name
     * @param  string                  $tableName       table real name
     * @param  string                  $modelName       model name
     * @param  array                   $tableDefinition table definition
     * @return \PDOStatement           $statement       prepared query
     */
    public static function statement(Database $database, array $schema, $mapping, $tableName, $modelName, array $tableDefinition)
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
            $sql .= Column::generateDefinition($schema, $mapping, $fieldName, $fieldRealName, $fieldDefinition);
            if (isset($fieldDefinition['primary']) === true && $fieldDefinition['primary'] === true) {
                $primary[] = $fieldRealName;
            }
            $sql .= ', ';
        }
        if (count($primary) > 0) {
            $sql .= 'CONSTRAINT pk_' . $tableName . ' PRIMARY KEY (' . implode(', ', $primary) . '), ';
        } else {
            Logger::get()->warning('table "' . $tableName . '" has no primary key');
        }
        $sql = substr($sql, 0, -2) . ')';
        $statement = $database->getLink()->prepare($sql);

        return $statement;
    }
}
