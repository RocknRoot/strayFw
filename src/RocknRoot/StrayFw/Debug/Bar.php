<?php

namespace RocknRoot\StrayFw\Debug;

use DebugBar\StandardDebugBar;

/**
 * Maximebf\DebugBar wrapper.
 * Isn't initialized in production environement.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Bar
{
    /**
     * True if class has already been initialized.
     *
     * @static
     * @var bool
     */
    private static $isInit = false;

    /**
     * DebugBar instance.
     *
     * @static
     * @var \DebugBar\StandardDebugBar
     */
    protected static $debugBar;

    /**
     * Init DebugBar.
     * You souldn't call it yourself.
     * Don't call this in production environment.
     *
     * @static
     */
    public static function init()
    {
        if (self::$isInit === false) {
            self::$debugBar = new StandardDebugBar();
            self::$debugBar->getJavascriptRenderer()->setBaseUrl('/media/stray');
            self::$isInit = true;
        }
    }

    /**
     * Add additionnal info in the debug bar.
     *
     * @static
     * @param string $message info message
     */
    public static function addMessage($message)
    {
        if (self::$isInit === true) {
            self::$debugBar["messages"]->addMessage($message);
        }
    }

    /**
     * Display the debug bar HTML body.
     *
     * @static
     * @return string
     */
    public static function getBody()
    {
        if (self::$isInit === true) {
            return self::$debugBar->getJavascriptRenderer()->render();
        }
    }

    /**
     * Display the debug bar HTML head.
     *
     * @static
     * @return string
     */
    public static function getHead()
    {
        if (self::$isInit === true) {
            $files = [ 'debugbar', 'openhandler', 'widgets' ];
            $res = null;
            array_walk($files, function ($name) use (&$res) {
                $res .= '<link rel="stylesheet" type="text/css" href="/css/_debug/' . $name . '.css">' . PHP_EOL;
                $res .= '<script type="text/javascript" src="/js/_debug/' . $name . '.js"></script>' . PHP_EOL;
            });

            return $res;
        }
    }
}
