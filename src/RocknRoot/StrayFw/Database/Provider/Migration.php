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
    protected $nextSchema;

    /**
     * Old schema definition, current migration's one.
     *
     * @var array
     */
    protected $prevSchema;

    /**
     * Constructor.
     *
     * @param array  $next new schema definition
     * @param string $path migration path
     */
    public function __construct(array $next, string $path)
    {
        $this->mapping = Mapping::get($this->getMappingName());
        $this->database = Database::get($this->mapping['config']['database']);
        $this->nextSchema = $next;
        $this->prevSchema = Config::get($path . 'schema.yml');
    }

    /**
     * Get mapping's name.
     *
     * @ abstract
     * @return string mapping's name
     */
    abstract public function getMappingName();

    /**
     * Perform migration.
     */
    abstract public function up();

    /**
     * Rollback migration.
     */
    abstract public function down();
}
