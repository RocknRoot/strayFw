<?php

namespace ErrantWorks\StrayFw\Database\Postgres\Mutation;

use ErrantWorks\StrayFw\Logger;
use ErrantWorks\StrayFw\Database\Database;
use ErrantWorks\StrayFw\Database\Helper;
use ErrantWorks\StrayFw\Database\Postgres\Column;
use ErrantWorks\StrayFw\Database\Postgres\Mutation\Mutation;
use ErrantWorks\StrayFw\Exception\InvalidSchemaDefinition;

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
     * @param  string                  $tableName       table real name
     * @param  string                  $modelName       model name
     * @param  array                   $tableDefinition table definition
     * @return PDOStatement            $statement prepared query
     */
    public static function statement(Database $database, $tableName, $modelName, array $tableDefinition)
    {
        $sql = 'CREATE TABLE ' . $tableName . ' (';
        if (isset($tableDefinition['fields']) === false) {
            throw new InvalidSchemaDefinition('model "' . $modelName . '" has no field');
        }
        $primary = array();
        foreach ($tableDefinition['fields'] as $fieldName => $fieldDefinition) {
            $fieldRealName = null;
            if (isset($fieldDefinition['name']) === true) {
                $fieldRealName = $fieldDefinition['name'];
            } else {
                $fieldRealName = Helper::codifyName($modelName) . '_' . Helper::codifyName($fieldName);
            }
            $sql .= Column::generateDefinition($fieldRealName, $fieldDefinition);
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
