<?php

namespace ErrantWorks\StrayFw\Console;

use ErrantWorks\StrayFw\Console\Request;
use ErrantWorks\StrayFw\Exception\InvalidDirectory;

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
     * Current request.
     *
     * @var Request
     */
    protected static $request;

    /**
     * Registed routes.
     *
     * @var array[]
     */
    protected static $routes;

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
            self::registerRoutes(__DIR__, 'console.yml');
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
            $class = self::$request->getClass();
            $action = self::$request->getAction() . 'Action';
            $object = new $class();
            $object->$action(self::$request);
        }
    }

    /**
     * Add routes to be considered.
     *
     * @static
     * @throws InvalidDirectory if directory can't be identified
     * @param  string           $dir  application root directory
     * @param  string           $file routes file name
     */
    public static function registerRoutes($dir, $file)
    {
        if (self::$isInit === true) {
            if (is_dir($dir) === false) {
                throw new InvalidDirectory('directory "' . $dir . '" can\'t be identified');
            }
            self::$routes[] = array(
                'dir' => $dir,
                'file' => $file
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
