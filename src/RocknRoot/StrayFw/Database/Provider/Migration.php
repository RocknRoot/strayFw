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
     * @var mixed[]
     */
    protected array $mapping;

    /**
     * Database.
     */
    protected \RocknRoot\StrayFw\Database\Database $database;

    /**
     * New schema definition, current one or next migration's one.
     *
     * @var mixed[]
     */
    protected array $nextSchema;

    /**
     * Old schema definition, current migration's one.
     *
     * @var mixed[]
     */
    protected array $prevSchema;

    /**
     * Constructor.
     *
     * @param mixed[] $next new schema definition
     * @param string  $path migration path
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
     * @abstract
     * @return string mapping's name
     */
    abstract public function getMappingName(): string;

    /**
     * Perform migration.
     */
    abstract public function up(): void;

    /**
     * Rollback migration.
     */
    abstract public function down(): void;
}
