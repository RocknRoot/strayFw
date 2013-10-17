<?php

namespace ErrantWorks\StrayFw\Http;

use ErrantWorks\StrayFw\Exception\BadUse;
use ErrantWorks\StrayFw\Exception\InvalidDirectory;
use ErrantWorks\StrayFw\Exception\NotARender;
use ErrantWorks\StrayFw\Http\RawRequest;
use ErrantWorks\StrayFw\Http\Session;
use ErrantWorks\StrayFw\Render\RenderInterface;

/**
 * Bootstrapping class for HTTP requests.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Http
{
    /**
     * True if class has already been initialized.
     *
     * @static
     * @var bool
     */
    private static $isInit = false;

    /**
     * Current raw request.
     *
     * @var RawRequest
     */
    protected static $rawRequest;

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
     * Initialize inner states according to current HTTP request.
     *
     * @static
     */
    public static function init()
    {
        if (self::$isInit === false) {
            self::$rawRequest = new RawRequest();
            self::$routes = array();
            self::$isInit = true;
            Session::init();
        }
    }

    /**
     * Launch the logic stuff. Http need to be initialized beforehand.
     *
     * @static
     * @throws BadUse     if http isn't initialized
     * @throws NotARender if object returned by action doesn't implement RenderInterface
     */
    public static function run()
    {
        if (self::$isInit === false) {
            throw new BadUse('http doesn\'t seem to have been initialized');
        }
        self::$request = new Request(self::$rawRequest, self::$routes);
        $class = self::$request->getClass();
        $action = self::$request->getAction() . 'Action';
        try {
            ob_start();
            $object = new $class();
            $render = $object->$action(self::$request);
            if (!($render instanceof RenderInterface)) {
                throw new NotARender('"' . $class . '.' . $action . '" returned a non RenderInterface implementing object');
            }
            echo $render->render();
            ob_end_flush();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Add routes to be considered.
     *
     * @static
     * @throws BadUse           if http isn't initialized
     * @throws InvalidDirectory if directory can't be identified
     * @param  string           $dir  application root directory
     * @param  string           $file routes file name
     */
    public static function registerRoutes($dir, $file)
    {
        if (self::$isInit === false) {
            throw new BadUse('http doesn\'t seem to have been initialized');
        }
        if (is_dir($dir) === false) {
            throw new InvalidDirectory('directory "' . $dir . '" can\'t be identified');
        }
        self::$routes[] = array(
            'dir' => $dir,
            'file' => $file
        );
    }

    /**
     * Get current raw request.
     *
     * @static
     * @return RawRequest
     */
    public static function getRawRequest()
    {
        return self::$rawRequest;
    }

    /**
     * Get current request.
     *
     * @static
     * @return Request
     */
    public static function getRequest()
    {
        return self::$request;
    }
}
