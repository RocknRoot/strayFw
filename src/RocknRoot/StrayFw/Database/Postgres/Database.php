<?php

namespace RocknRoot\StrayFw\Database\Postgres;

use RocknRoot\StrayFw\Database\Provider\Database as ProviderDatabase;

/**
 * PostgreSQL database helper.
 * User code shouldn't use this class directly.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Database implements ProviderDatabase
{
    /**
     * Get DSN string for PDO according to specified info.
     *
     * @param  array  $info database info
     * @return string DSN string
     */
    public function getDsn(array $info) : string
    {
        $dsn = 'pgsql:host=';
        $dsn .= (isset($info['host']) === true ? $info['host'] : 'localhost') . ';';
        if (isset($info['port']) === true) {
            $dsn .= 'port=' . $info['port'] . ';';
        }
        $dsn .= 'dbname=' . $info['name'] . ';';

        return $dsn;
    }

    /**
     * Begin transaction if applicable.
     *
     * @param  mixed $link link info
     * @return bool  true if successful
     */
    public function beginTransaction($link) : bool
    {
        return $link->beginTransaction();
    }

    /**
     * Commit the current transaction if applicable.
     *
     * @param  mixed $link link info
     * @return bool  true if successful
     */
    public function commit($link) : bool
    {
        return $link->commit();
    }

    /**
     * Roll back the current transaction if applicable.
     *
     * @param  mixed $link link info
     * @return bool  true if successful
     */
    public function rollBack($link) : bool
    {
        return $link->rollBack();
    }

    /**
     * Create a new save point if applicable.
     *
     * @param  mixed  $link link info
     * @param  string $name save point name
     * @return bool   true if successful
     */
    public function savePoint($link, string $name) : bool
    {
        return $link->exec('SAVEPOINT ' . $name);
    }

    /**
     * Release a save point if applicable.
     *
     * @param  mixed  $link link info
     * @param  string $name save point name
     * @return bool   true if successful
     */
    public function releaseSavePoint($link, string $name) : bool
    {
        return $link->exec('RELEASE SAVEPOINT ' . $name);
    }

    /**
     * Roll back a save point if applicable.
     *
     * @param  mixed  $link link info
     * @param  string $name save point name
     * @return bool   true if successful
     */
    public function rollBackSavePoint($link, string $name) : bool
    {
        return $link->exec('ROLLBACK TO SAVEPOINT ' . $name);
    }
}
