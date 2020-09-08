<?php

namespace RocknRoot\StrayFw\Debug;

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

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
     * True if class has already been initialized.
     *
     * @static
     */
    private static bool $isInit = false;

    /**
     * Whoops error page handler.
     *
     * @static
     */
    protected static ?\Whoops\Handler\PrettyPageHandler $prettyPageHandler = null;

    /**
     * Init Whoops handlers.
     * You souldn't call it yourself.
     * Don't call this in production environment.
     *
     * @static
     */
    public static function init() : void
    {
        if (self::$isInit === false) {
            self::$prettyPageHandler = new PrettyPageHandler();
            self::$prettyPageHandler->setPageTitle('I just broke a string... - strayFw');
            $whoops = new Run();
            $whoops->pushHandler(new JsonResponseHandler());
            $whoops->pushHandler(self::$prettyPageHandler);
            $whoops->register();
            self::$isInit = true;
        }
    }

    /**
     * Add additionnal info in case of error page is displayed.
     *
     * @static
     * @param string $title data group title
     * @param mixed[]  $data  data that will be displayed
     */
    public static function addData(string $title, array $data) : void
    {
        if (self::$isInit === true && self::$prettyPageHandler) {
            self::$prettyPageHandler->AddDataTable($title, $data);
        }
    }
}
