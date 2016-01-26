<?php

namespace RocknRoot\StrayFw\Console;

use RocknRoot\StrayFw\Controllers;

/**
 * Bootstrapping class for CLI requests.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Console
{
    /**
     * True if class has already been initialized.
     *
     * @static
     * @var bool
     */
    private static $isInit = false;

    /**
     * Current namespace prefix.
     *
     * @var string
     */
    protected static $namespace;

    /**
     * Registed routes.
     *
     * @var array[]
     */
    protected static $routes;

    /**
     * Current request.
     *
     * @var Request
     */
    protected static $request;

    /**
     * Current controllers.
     *
     * @var object[]
     */
    protected static $controllers;

    /**
     * Initialize inner states according.
     *
     * @static
     */
    public static function init()
    {
        if (self::$isInit === false) {
            self::$routes = array();
            self::$isInit = true;
        }
    }

    /**
     * Launch the logic stuff. Console need to be initialized beforehand.
     *
     * @static
     */
    public static function run()
    {
        if (self::$isInit === true) {
            self::$request = new Request(self::$routes);
            self::$controllers = array();
            $before = self::$request->getBefore();
            foreach ($before as $b) {
                $controller = Controllers::get($b['class']);
                $action = $b['action'];
                $controller->$action(self::$request);
            }
            if (self::$request->hasEnded() === false) {
                $controller = Controllers::get(self::$request->getClass());
                $action = self::$request->getAction();
                $controller->$action(self::$request);
                if (self::$request->hasEnded() === false) {
                    $after = self::$request->getAfter();
                    foreach ($after as $a) {
                        $controller = Controllers::get($a['class']);
                        $action = $a['action'];
                        $controller->$action(self::$request);
                    }
                }
            }
        }
    }

    /**
     * Set namespace prefix for incoming routes.
     *
     * @static
     * @param  string           $namespace namespace prefix
     */
    public static function prefix($namespace)
    {
        self::$namespace = $namespace;
    }

    /**
     * Add route to be considered.
     *
     * @static
     * @param  string           $path   route path
     * @param  string           $usage  how to use it, for help screen
     * @param  string           $help   route description, for help screen
     * @param  string           $action class and method to call
     */
    public static function route($path, $usage, $help, $action)
    {
        if (self::$isInit === true) {
            self::$routes[] = array(
                'type' => 'route',
                'path' => $path,
                'usage' => $usage,
                'help' => $help,
                'action' => $action,
                'namespace' => self::$namespace
            );
        }
    }

    /**
     * Add before hook to be considered.
     *
     * @static
     * @param  string           $path   route path
     * @param  string           $usage  how to use it, for help screen
     * @param  string           $help   route description, for help screen
     * @param  string           $action class and method to call
     */
    public static function before($path, $usage, $help, $action)
    {
        if (self::$isInit === true) {
            self::$routes[] = array(
                'type' => 'before',
                'path' => $path,
                'usage' => $usage,
                'help' => $help,
                'action' => $action,
                'namespace' => self::$namespace
            );
        }
    }

    /**
     * Add after hook to be considered.
     *
     * @static
     * @param  string           $path   route path
     * @param  string           $usage  how to use it, for help screen
     * @param  string           $help   route description, for help screen
     * @param  string           $action class and method to call
     */
    public static function after($path, $usage, $help, $action)
    {
        if (self::$isInit === true) {
            self::$routes[] = array(
                'type' => 'after',
                'path' => $path,
                'usage' => $usage,
                'help' => $help,
                'action' => $action,
                'namespace' => self::$namespace
            );
        }
    }

    /**
     * Get all registered routes.
     *
     * @return array[] all routes
     */
    public static function getRoutes()
    {
        return self::$routes;
    }
}
