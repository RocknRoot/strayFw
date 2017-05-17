<?php

namespace RocknRoot\StrayFw\Database\Postgres;

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
    public static function generate(array $mapping, string $name)
    {
        $up = '';
        $down = '';

        return [ $up, $down ];
    }
}
