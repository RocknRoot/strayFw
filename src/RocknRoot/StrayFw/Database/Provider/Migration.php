<?php

namespace RocknRoot\StrayFw\Database\Provider;

use RocknRoot\StrayFw\Config;
use RocknRoot\StrayFw\Database\Database;
use RocknRoot\StrayFw\Database\Mapping;

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
    protected $schema;

    /**
     * Constructor.
     *
     * @param array  $next new schema definition
     * @param string $path migration path
     */
    public function __construct(array $next, string $path)
    {
        $this->mapping = Mapping::get(static::MAPPING);
        $this->database = Database::get($this->mapping['config']['database']);
        $this->nextSchema = $next;
        $this->schema = Config::get($path . 'schema.yml');
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
