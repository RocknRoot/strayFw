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
    public function getDsn(array $info) : string;

    /**
     * Begin transaction if applicable.
     *
     * @param  mixed $link link info
     * @return bool  true if successful
     */
    public function beginTransaction($link) : bool;

    /**
     * Commit the current transaction if applicable.
     *
     * @param  mixed $link link info
     * @return bool  true if successful
     */
    public function commit($link) : bool;

    /**
     * Roll back the current transaction if applicable.
     *
     * @param  mixed $link link info
     * @return bool  true if successful
     */
    public function rollBack($link) : bool;

    /**
     * Create a new save point if applicable.
     *
     * @param  mixed  $link link info
     * @param  string $name save point name
     * @return bool   true if successful
     */
    public function savePoint($link, string $name) : bool;

    /**
     * Release a save point if applicable.
     *
     * @param  mixed  $link link info
     * @param  string $name save point name
     * @return bool   true if successful
     */
    public function releaseSavePoint($link, string $name) : bool;

    /**
     * Roll back a save point if applicable.
     *
     * @param  mixed  $link link info
     * @param  string $name save point name
     * @return bool   true if successful
     */
    public function rollBackSavePoint($link, string $name) : bool;
}
