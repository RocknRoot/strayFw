<?php

namespace RocknRoot\StrayFw\Database\Postgres\Mutation;

use RocknRoot\StrayFw\Database\Database;
use RocknRoot\StrayFw\Database\Helper;
use RocknRoot\StrayFw\Database\Postgres\Query\Mutation as MutationQuery;

/**
 * Representation for foreign key addition operations.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class AddForeignKey extends Mutation
{
    /**
     * Prepare and return according PDO statement.
     *
     * @param  Database      $database         database
     * @param  array         $definition       schema definition
     * @param  string        $modelName        model name
     * @param  string        $tableName        table real name
     * @param  string        $foreignName      foreign key name
     * @param  string        $foreignTableName foreign table real name
     * @return MutationQuery $statement prepared query
     */
    public static function statement(Database $database, array $definition, string $modelName, string $tableName, string $foreignName, string $foreignTableName) : MutationQuery
    {
        $tableDefinition = $definition[$modelName];
        $foreignDefinition = $tableDefinition['links'][$foreignName];
        $foreignTableDefinition = $definition[$foreignDefinition['model']];
        $from = array();
        $to = array();
        foreach ($foreignDefinition['fields'] as $field => $linked) {
            if (isset($tableDefinition['fields'][$field]['name']) === true) {
                $from[] = $tableDefinition['fields'][$field]['name'];
            } else {
                $from[] = Helper::codifyName($modelName) . '_' . Helper::codifyName($field);
            }
            if (isset($foreignTableDefinition['fields'][$linked]['name']) === true) {
                $to[] = $foreignTableDefinition['fields'][$linked]['name'];
            } else {
                $to[] = Helper::codifyName($foreignDefinition['model']) . '_' . Helper::codifyName($linked);
            }
        }
        $sql = 'ALTER TABLE ' . $tableName . ' ADD CONSTRAINT fk_' . $foreignName . ' FOREIGN KEY (' . implode(', ', $from) . ') REFERENCES ' . $foreignTableName . '(' . implode(', ', $to) . ')';
        if (isset($foreignDefinition['update']) === true) {
            $sql .= ' ON UPDATE ' . strtoupper($foreignDefinition['update']);
        }
        if (isset($foreignDefinition['delete']) === true) {
            $sql .= ' ON DELETE ' . strtoupper($foreignDefinition['delete']);
        }
        $statement = $database->getMasterLink()->prepare($sql);

        return new MutationQuery($database->getAlias(), $statement);
    }
}
