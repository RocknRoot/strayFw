<?php

namespace RocknRoot\StrayFw\Http;

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
     * @param  string     $name key
     * @return null|mixed
     */
    public static function get(string $name)
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
    public static function has(string $name): bool
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
    public static function set(string $name, string $value, int $expire = 0, ?string $path = null): void
    {
        if ($path === null) {
            \setcookie($name, $value, $expire);
        } else {
            \setcookie($name, $value, $expire, $path);
        }
    }

    /**
     * Unset a cookie.
     *
     * @static
     * @param string $name key
     */
    public static function delete(string $name): void
    {
        \setcookie($name, '', \time() - 1);
    }

    /**
     * Clear all cookies.
     *
     * @static
     */
    public static function clear(): void
    {
        $keys = \array_keys($_COOKIE);
        foreach ($keys as $key) {
            \setcookie($key, '', \time() - 1);
        }
    }
}
