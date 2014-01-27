<?php

namespace ErrantWorks\StrayFw\Database\Postgres;

use ErrantWorks\StrayFw\Database\Provider\Database as ProviderDatabase;

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
    public function getDsn(array $info)
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
     * @param mixed link info
     * @return bool true if successful
     */
    public function beginTransaction($link)
    {
        return $link->beginTransaction();
    }

    /**
     * Commit the current transaction if applicable.
     *
     * @param mixed link info
     * @return bool true if successful
     */
    public function commit($link)
    {
        return $link->commit();
    }

    /**
     * Roll back the current transaction if applicable.
     *
     * @param mixed link info
     * @return bool true if successful
     */
    public function rollBack($link)
    {
        return $link->rollBack();
    }

    /**
     * Create a new save point if applicable.
     *
     * @param mixed link info
     * @param string save point name
     * @return bool true if successful
     */
    public function savePoint($link, $name)
    {
        return $link->exec('SAVEPOINT ' . $name);
    }

    /**
     * Release a save point if applicable.
     *
     * @param mixed link info
     * @param string save point name
     * @return bool true if successful
     */
    public function releaseSavePoint($link, $name)
    {
        return $link->exec('RELEASE SAVEPOINT ' . $name);
    }

    /**
     * Roll back a save point if applicable.
     *
     * @param mixed link info
     * @param string save point name
     * @return bool true if successful
     */
    public function rollBackSavePoint($link, $name)
    {
        return $link->exec('ROLLBACK TO SAVEPOINT ' . $name);
    }
}
