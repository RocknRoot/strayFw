<?php

namespace RocknRoot\StrayFw\Database\Provider;

use RocknRoot\StrayFw\Config;
use RocknRoot\StrayFw\Database\Database;

/**
 * Representation parent class for migrations.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Migration
{
    /**
     * Mapping definition.
     *
     * @var array
     */
    protected $mapping;

    /**
     * Database.
     *
     * @var Database
     */
    protected $database;

    /**
     * New schema definition, current one or next migration's one.
     *
     * @var array
     */
    protected $newSchema;

    /**
     * Old schema definition, current migration's one.
     *
     * @var array
     */
    protected $oldSchema;

    /**
     * Constructor.
     *
     * @param array $schema new schema definition
     */
    public function __construct(array $schema)
    {
        $this->mapping = Mapping::get(static::MAPPING);
        $this->database = Database::get($this->mapping['config']['database']);
        $this->newSchema = $schema;
        $this->oldSchema = Config::get(__DIR__ . '/schema.yml');
    }

    /**
     * Perform migration.
     */
    abstract public function up();

    /**
     * Rollback migration.
     */
    abstract public function down();
}
