<?php

namespace ErrantWorks\StrayFw\Database\Postgres\Mutation;

use ErrantWorks\StrayFw\Database\Database;
use ErrantWorks\StrayFw\Database\Helper;
use ErrantWorks\StrayFw\Database\Postgres\Mutation\Mutation;

/**
 * Representation for index addition operations.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class AddIndex extends Mutation
{
    /**
     * Prepare and return according PDO statement.
     *
     * @param  Database     $database        database
     * @param  string       $modelName       model name
     * @param  string       $tableName       table real name
     * @param  array        $tableDefinition table definition
     * @param  string       $indexName       index name
     * @return PDOStatement $statement prepared query
     */
    public static function statement(Database $database, $modelName, $tableName, array $tableDefinition, $indexName)
    {
        $indexDefinition = $tableDefinition['indexes'][$indexName];
        $indexes = array();
        foreach ($indexDefinition as $field) {
            if (isset($tableDefinition['fields'][$field]['name']) === true) {
                $indexes[] = $tableDefinition['fields'][$field]['name'];
            } else {
                $indexes[] = Helper::codifyName($modelName) . '_' . Helper::codifyName($field);
            }
        }
        $statement = $database->getLink()->prepare('CREATE INDEX idx_' . $indexName . ' ON ' . $tableName . ' (' . implode(', ', $indexes) . ')');

        return $statement;
    }
}
