<?php

namespace RocknRoot\StrayFw\Http;

use RocknRoot\StrayFw\Config;

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
     */
    private static bool $isInit = false;

    /**
     * Initialize session.
     *
     * @static
     */
    public static function init() : void
    {
        if (self::$isInit === false) {
            if (\session_id() == null) {
                global $_SERVER;
                $settings = Config::getSettings();
                if (isset($settings['session']) === true) {
                    if (isset($settings['session']['name']) === true) {
                        \session_name($settings['session']['name']);
                    }
                    if (isset($settings['session']['cookie_domain']) === true) {
                        \session_set_cookie_params([
                            'domain' => $settings['session']['cookie_domain'],
                        ]);
                    }
                    if (isset($settings['session']['lifetime']) === true) {
                        ini_set('session.cookie_lifetime', $settings['session']['lifetime']);
                        ini_set('session.gc_maxlifetime', $settings['session']['lifetime']);
                    }
                }
                \session_start();
            }
            self::$isInit = true;
        }
    }

    /**
     * Get a session variable value by its key.
     *
     * @static
     * @param  string     $name key
     * @return null|mixed
     */
    public static function get(string $name)
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
     * @param string $name key
     */
    public static function has(string $name): bool
    {
        return isset($_SESSION[$name]);
    }

    /**
     * Set a session variable.
     *
     * @static
     * @param string $name  key
     * @param mixed  $value new value
     */
    public static function set(string $name, $value) : void
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Unset a session variable.
     *
     * @static
     * @param string $name key
     */
    public static function delete(string $name) : void
    {
        unset($_SESSION[$name]);
    }

    /**
     * Clear all session states.
     *
     * @static
     */
    public static function clear() : void
    {
        \session_unset();
        \session_destroy();
    }
}
