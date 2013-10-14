<?php

namespace ErrantWorks\StrayFw\Debug;

/**
 * Handle error page displayed if an error is raised or uncaught exception is thrown.
 * Isn't initialized in production environement.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class ErrorPage
{
    /**
     * True if debug error page has already been initialized.
     *
     * @var bool
     */
    private static $isInit = false;

    /**
     * Whoops error page handler.
     *
     * @var \Whoops\Handler\PrettyPageHandler
     */
    protected static $prettyPageHandler;

    /**
     * Init Whoops handlers.
     * Don't call this in production environment.
     */
    public static function init()
    {
        if (self::$isInit === false) {
            self::$prettyPageHandler = new \Whoops\Handler\PrettyPageHandler();
            self::$prettyPageHandler->setPageTitle('I just broke a string... - strayFw');
            $whoops = new \Whoops\Run();
            $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
            $whoops->pushHandler(self::$prettyPageHandler);
            $whoops->register();
            self::$isInit = true;
        }
    }

    /**
     * Add additionnal info in case of error page is displayed.
     *
     * @param string $title data group title
     * @param array $data data that will be displayed
     */
    public static function addData($title, array $data)
    {
        if (self::$isInit === true) {
            self::$prettyPageHandler->AddDataTable($title, $data);
        }
    }
}
