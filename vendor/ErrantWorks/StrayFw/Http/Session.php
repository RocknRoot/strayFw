<?php

namespace ErrantWorks\StrayFw\Http;

/**
 * Wrapper class for session variables.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Session
{
    /**
     * True if class has already been initialized.
     *
     * @static
     * @var bool
     */
    private static $isInit = false;

    /**
     * Initialize session.
     *
     * @static
     */
    public static function init()
    {
        if (self::$isInit === false) {
            if (session_id() == null) {
                session_start();
            }
            self::$isInit = true;
        }
    }

    /**
     * Get a session variable value by its key.
     *
     * @static
     * @param  string $name key
     * @return mixed
     */
    public static function get($name)
    {
        if (isset($_SESSION[$name]) === false) {
            return null;
        }

        return $_SESSION[$name];
    }

    /**
     * Check if a session variable is set.
     *
     * @static
     * @param  string $name key
     * @return bool
     */
    public static function has($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * Set a session variable.
     *
     * @static
     * @param string $name  key
     * @param string $value new value
     */
    public static function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Unset a session variable.
     *
     * @static
     * @param string $name key
     */
    public static function delete($name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * Clear all session states.
     *
     * @static
     */
    public static function clear()
    {
        session_unset();
        session_destroy();
    }
}
