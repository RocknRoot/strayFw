<?php

namespace RocknRoot\StrayFw\Database\Provider;

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
     * Perform migration.
     */
    abstract public function up();

    /**
     * Rollback migration.
     */
    abstract public function down();
}
