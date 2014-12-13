<?php

namespace RocknRoot\StrayFw\Database\Provider;

/**
 * Interface for provider database class.
 *
 * @interface
 *
 * @author Nekith <nekith@errant-works.com>
 */
interface Database
{
    /**
     * Get DSN string for PDO according to specified info.
     *
     * @param  array  $info database info
     * @return string DSN string
     */
    public function getDsn(array $info);

    /**
     * Begin transaction if applicable.
     *
     * @param mixed link info
     * @return bool true if successful
     */
    public function beginTransaction($link);

    /**
     * Commit the current transaction if applicable.
     *
     * @param mixed link info
     * @return bool true if successful
     */
    public function commit($link);

    /**
     * Roll back the current transaction if applicable.
     *
     * @param mixed link info
     * @return bool true if successful
     */
    public function rollBack($link);

    /**
     * Create a new save point if applicable.
     *
     * @param mixed link info
     * @param string save point name
     * @return bool true if successful
     */
    public function savePoint($link, $name);

    /**
     * Release a save point if applicable.
     *
     * @param mixed link info
     * @param string save point name
     * @return bool true if successful
     */
    public function releaseSavePoint($link, $name);

    /**
     * Roll back a save point if applicable.
     *
     * @param mixed link info
     * @param string save point name
     * @return bool true if successful
     */
    public function rollBackSavePoint($link, $name);
}
