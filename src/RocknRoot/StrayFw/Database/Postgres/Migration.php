<?php

namespace RocknRoot\StrayFw\Database\Postgres;

use RocknRoot\StrayFw\Config;
use RocknRoot\StrayFw\Database\Database;
use RocknRoot\StrayFw\Database\Helper;
use RocknRoot\StrayFw\Database\Postgres\Query\Delete;
use RocknRoot\StrayFw\Database\Postgres\Query\Insert;
use RocknRoot\StrayFw\Database\Postgres\Query\Select;
use RocknRoot\StrayFw\Database\Provider\Migration as ProviderMigration;
use RocknRoot\StrayFw\Exception\Exception;

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
     * @return array import, up and down code
     */
    public static function generate(array $mapping, string $mappingName, string $name) : array
    {
        $import = [];
        $up = [];
        $down = [];
        $oldSchema = Config::get(rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'schema.yml');
        $migrations = Config::get(rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'migrations.yml');
        $imax = count($migrations);
        for ($i = 0; $i < $imax; $i++) {
            if ($migrations[$i]['name'] == $name) {
                break;
            }
        }
        if ($i < $imax - 1) {
            $schema = Config::get(rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ucfirst($migrations[$i + 1]['name']) . DIRECTORY_SEPARATOR . 'schema.yml');
        } else {
            $schema = Config::get($mapping['config']['schema']);
        }

        $newKeys = array_diff_key($schema, $oldSchema);
        foreach ($newKeys as $key => $table) {
            $tableName = null;
            if (isset($table['name']) === true) {
                $tableName = $table['name'];
            } else {
                $tableName = Helper::codifyName($mappingName) . '_' . Helper::codifyName($key);
            }
            if (isset($table['type']) === false || $table['type'] == 'model') {
                $import[] = 'AddTable';
                $import[] = 'DeleteTable';
                $up[] = 'AddTable::statement($this->database, $this->nextSchema, $this->mapping, \'' . $tableName . '\', \'' . $key . '\')';
                $down[] = 'DeleteTable::statement($this->database, \'' . $tableName . '\')';
                echo 'AddTable: ' . $key . PHP_EOL;
            } else {
                echo 'TODO AddEnum: ' . $key . PHP_EOL;
            }
        }

        $oldKeys = array_diff_key($oldSchema, $schema);
        foreach ($oldKeys as $key => $table) {
            $tableName = null;
            if (isset($table['name']) === true) {
                $tableName = $table['name'];
            } else {
                $tableName = Helper::codifyName($mappingName) . '_' . Helper::codifyName($key);
            }
            if (isset($table['type']) === false || $table['type'] == 'model') {
                $import[] = 'AddTable';
                $import[] = 'DeleteTable';
                $up[] = 'DeleteTable::statement($this->database, \'' . $tableName . '\')';
                $down[] = 'AddTable::statement($this->database, $this->prevSchema, $this->mapping, \'' . $tableName . '\', \'' . $key . '\')';
                echo 'RemoveTable: ' . $key . PHP_EOL;
            } else {
                echo 'TODO RemoveEnum: ' . $key . PHP_EOL;
            }
        }

        $keys = array_intersect_key($oldSchema, $schema);
        foreach ($keys as $modelName => $model) {
            $tableName = null;
            if (isset($table['name']) === true) {
                $tableName = $table['name'];
            } else {
                $tableName = Helper::codifyName($mappingName) . '_' . Helper::codifyName($modelName);
            }
            if (isset($table['type']) === false || $table['type'] == 'model') {
                $newFields = array_diff_key($schema[$modelName]['fields'], $model['fields']);
                foreach ($newFields as $fieldName => $fieldDefinition) {
                    $import[] = 'AddColumn';
                    $import[] = 'DeleteColumn';
                    $up[] = 'AddColumn::statement($this->database, $this->nextSchema, self::MAPPING, \'' . $modelName . '\', \'' . $tableName . '\', \'' . $fieldName . '\')';
                    $down[] = 'DeleteColumn::statement($this->database, $this->nextSchema, \'' . $modelName . '\', \'' . $tableName . '\', \'' . $fieldName . '\')';
                    echo 'AddColumn: ' . $modelName . '.' . $fieldName . PHP_EOL;
                }
                $oldFields = array_diff_key($model['fields'], $schema[$modelName]['fields']);
                foreach ($oldFields as $fieldName => $fieldDefinition) {
                    $import[] = 'AddColumn';
                    $import[] = 'DeleteColumn';
                    $up[] = 'DeleteColumn::statement($this->database, $this->prevSchema, \'' . $modelName . '\', \'' . $tableName . '\', \'' . $fieldName . '\')';
                    $down[] = 'AddColumn::statement($this->database, $this->prevSchema, self::MAPPING, \'' . $modelName . '\', \'' . $tableName . '\', \'' . $fieldName . '\')';
                    echo 'DeleteColumn: ' . $modelName . '.' . $fieldName . PHP_EOL;
                }
                $fields = array_intersect_key($model['fields'], $schema[$modelName]['fields']);
                foreach ($fields as $fieldName => $fieldDefinition) {
                    echo 'TODO compare fields' . PHP_EOL;
                }
            } else {
                echo 'TODO Compare Enum values' . PHP_EOL;
            }
        }

        return [
            'import' => array_unique($import),
            'up' => $up,
            'down' => $down,
        ];
    }

    /**
     * Ensure the migrations table exist for specified mapping.
     *
     * @param array $mapping mapping definition
     * @return bool true if successful
     */
    public static function ensureTable(array $mapping) : bool
    {
        $database = Database::get($mapping['config']['database']);
        $statement = 'CREATE TABLE IF NOT EXISTS _stray_migration (';
        $statement .= 'date TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, ';
        $statement .= 'migration VARCHAR(255)';
        $statement .= ')';
        $statement = $database->getMasterLink()->prepare($statement);
        if ($statement->execute() === false) {
            echo 'Can\'t create _stray_migration (' . $statement->errorInfo()[2] . ')' . PHP_EOL;
            return false;
        }
        $select = new Select($mapping['config']['database'], true);
        $select->select('COUNT(*) as count')
            ->from('_stray_migration');
        if ($select->execute() === false) {
            echo 'Can\'t fetch from _stray_migration (' . $select->getErrorMessage() . ')' . PHP_EOL;
            return false;
        }
        if ($select->fetch()['count'] == 0) {
            $insert = new Insert($mapping['config']['database']);
            $insert->into('_stray_migration');
            if ($insert->execute() === false) {
                echo 'Can\'t insert into _stray_migration (' . $insert->getErrorMessage() . ')' . PHP_EOL;
                return false;
            }
        }
        return true;
    }

    /**
     * Run migration code for specified mapping.
     *
     * @param array $mapping mapping definition
     */
    public static function migrate(array $mapping)
    {
        $database = Database::get($mapping['config']['database']);
        $database->beginTransaction();
        if (self::ensureTable($mapping) === false) {
            $database->rollBack();
            return;
        }
        $migrations = Config::get(rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'migrations.yml');
        $select = new Select($mapping['config']['database'], true);
        $select->select('*')
            ->from('_stray_migration')
            ->orderBy('date DESC')
            ->limit(1);
        if ($select->execute() === false) {
            echo 'Can\'t fetch from _stray_migration (' . $select->getErrorMessage() . ')' . PHP_EOL;
            $database->rollBack();
            return;
        }
        $last = $select->fetch();
        $last['date'] = new \DateTime($last['date']);
        $last['date'] = $last['date']->getTimestamp();
        $migrations = array_values(array_filter($migrations, function (array $m) use ($last) {
            return (int) $m['timestamp'] > $last['date'];
        }));
        usort($migrations, function (array $a, array $b) {
            return $a['timestamp'] > $b['timestamp'];
        });
        $imax = count($migrations);
        for ($i = 0; $i < $imax; $i++) {
            echo 'Run ' . $migrations[$i]['name'] . PHP_EOL;
            $cl = '\\' . ltrim(rtrim($mapping['config']['migrations']['namespace'], '\\'), '\\') . '\\' . ucfirst($migrations[$i]['name']) . '\\' . ucfirst($migrations[$i]['name']);
            if ($i < $imax - 1) {
                $schema = Config::get(rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ucfirst($migrations[$i + 1]['name']) . DIRECTORY_SEPARATOR . 'schema.yml');
            } else {
                $schema = Config::get($mapping['config']['schema']);
            }
            $n = new $cl($schema, rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ucfirst($migrations[$i]['name']) . DIRECTORY_SEPARATOR);
            try {
                $n->up();
            } catch (Exception $e) {
                $database->rollBack();
                throw $e;
            }
            $insert = new Insert($mapping['config']['database']);
            $insert->into('_stray_migration');
            $insert->bind('migration', $migrations[$i]['name']);
            $insert->values([ 'migration' => ':migration' ]);
            if ($insert->execute() === false) {
                echo 'Can\'t insert into _stray_migration (' . $insert->getErrorMessage() . ')' . PHP_EOL;
                $database->rollBack();
                return;
            }
        }
        $database->commit();
    }

    /**
     * Run reversed migration code for specified mapping.
     *
     * @param array $mapping mapping definition
     */
    public static function rollback(array $mapping)
    {
        $database = Database::get($mapping['config']['database']);
        $database->beginTransaction();
        if (self::ensureTable($mapping) === false) {
            $database->rollBack();
            return;
        }
        $migrations = Config::get(rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'migrations.yml');
        $select = new Select($mapping['config']['database'], true);
        $select->select('*')
            ->where('migration IS NOT NULL')
            ->from('_stray_migration')
            ->orderBy('date DESC')
            ->limit(1);
        if ($select->execute() === false) {
            echo 'Can\'t fetch from _stray_migration (' . $select->getErrorMessage() . ')' . PHP_EOL;
            $database->rollBack();
            return;
        }
        $last = $select->fetch();
        if (!$last) {
            echo 'There is no executed migration.' . PHP_EOL;
            $database->rollBack();
            return;
        }
        echo 'Last executed migration is: ' . $last['migration'] . '.' . PHP_EOL;
        $last['date'] = new \DateTime($last['date']);
        $last['date'] = $last['date']->getTimestamp();
        $migration = null;
        $i = 0;
        $imax = count($migrations);
        while ($i < $imax) {
            if ($migrations[$i]['name'] == $last['migration']) {
                $migration = $migrations[$i];
                break;
            }
            $i++;
        }
        if ($migration == null) {
            echo 'Can\'t find migration in migrations.yml.' . PHP_EOL;
            return;
        }
        $cl = '\\' . ltrim(rtrim($mapping['config']['migrations']['namespace'], '\\'), '\\') . '\\' . ucfirst($migration['name']) . '\\' . ucfirst($migration['name']);
        if ($i < $imax - 1) {
            $schema = Config::get(rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ucfirst($migrations[$i + 1]['name']) . DIRECTORY_SEPARATOR . 'schema.yml');
        } else {
            $schema = Config::get($mapping['config']['schema']);
            echo'last';
        }
        $n = new $cl($schema, rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ucfirst($migration['name']) . DIRECTORY_SEPARATOR);
        try {
            $n->down();
        } catch (Exception $e) {
            $database->rollBack();
            throw $e;
        }
        $delete = new Delete($mapping['config']['database']);
        $delete->from('_stray_migration');
        $delete->bind('migration', $migration['name']);
        $delete->where([ 'migration' => ':migration' ]);
        if ($delete->execute() === false) {
            echo 'Can\'t delete from _stray_migration (' . $delete->getErrorMessage() . ')' . PHP_EOL;
            $database->rollBack();
            return;
        }
        $database->commit();
    }
}
