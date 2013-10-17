<?php

namespace ErrantWorks\StrayFw\Http;

/**
 * Wrapper class for cookies.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Cookie
{
    /**
     * Get a cookie value by its key.
     *
     * @static
     * @param  string $name key
     * @return mixed
     */
    public static function get($name)
    {
        if (isset($_COOKIE[$name]) === false) {
            return null;
        }

        return $_COOKIE[$name];
    }

    /**
     * Check if a cookie is set.
     *
     * @static
     * @param  string $name key
     * @return bool
     */
    public static function has($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Set a cookie.
     *
     * @static
     * @param string $name   key
     * @param string $value  new value
     * @param int    $expire expiration timestamp
     * @param string $path   cookie path
     */
    public static function set($name, $value, $expire = 0, $path = null)
    {
        if ($path === null) {
            setcookie($name, $value, $expire);
        } else {
            setcookie($name, $value, $expire, $path);
        }
    }

    /**
     * Unset a cookie.
     *
     * @static
     * @param string $name key
     */
    public static function delete($name)
    {
        setcookie($name, null, time() - 1);
    }

    /**
     * Clear all cookies.
     *
     * @static
     */
    public static function clear()
    {
        $keys = array_keys($_COOKIE);
        for ($i = 0; $i < count($keys); $i++) {
            setcookie($keys[$i], null, time() - 1);
        }
    }
}
