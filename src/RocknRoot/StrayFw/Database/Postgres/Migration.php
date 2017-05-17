<?php

namespace RocknRoot\StrayFw\Database\Postgres;

use RocknRoot\StrayFw\Config;
use RocknRoot\StrayFw\Database\Helper;
use RocknRoot\StrayFw\Database\Postgres\Mutation\{AddEnum, AddTable};
use RocknRoot\StrayFw\Database\Provider\Migration as ProviderMigration;

/**
 * Representation parent class for PostgreSQL migrations.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Migration extends ProviderMigration
{
    /**
     * Generate code for migration.
     *
     * @param array  $mapping     mapping definition
     * @param string $mappingName mapping name
     * @param string $name        migration name
     * @return array up and down code
     */
    public static function generate(array $mapping, string $mappingName, string $name)
    {
        $up = '';
        $down = '';
        $oldSchema = Config::get(rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'schema.yml');
        $schema = Config::get($mapping['config']['schema']);

        $newKeys = array_diff_key($schema, $oldSchema);
        foreach ($newKeys as $key => $table) {
            $tableName = null;
            if (isset($table['name']) === true) {
                $tableName = $table['name'];
            } else {
                $tableName = Helper::codifyName($mappingName) . '_' . Helper::codifyName($key);
            }
            if (isset($table['type']) === false || $table['type'] == 'model') {
                echo 'AddTable: ' . $tableName . PHP_EOL;
            } else {
                echo 'AddEnum: ' . $tableName . PHP_EOL;
            }
        }

        $oldKeys = array_diff_key($oldSchema, $schema);
        foreach ($oldKeys as $key => $model) {
            $tableName = null;
            if (isset($table['name']) === true) {
                $tableName = $table['name'];
            } else {
                $tableName = Helper::codifyName($mappingName) . '_' . Helper::codifyName($key);
            }
            if (isset($table['type']) === false || $table['type'] == 'model') {
                echo 'RemoveTable: ' . $tableName . PHP_EOL;
            } else {
                echo 'RemoveEnum: ' . $tableName . PHP_EOL;
            }
        }

        $keys = array_intersect_key($oldSchema, $schema);

        return [ $up, $down ];
    }
}
