<?php

namespace RocknRoot\StrayFw;

/**
 * General class for user's controllers storage.
 * 
 * @author Nekith <nekith@errant-works.com>
 */
class Controllers
{
    /**
     * Existing controllers.
     *
     * @static
     * @var object[]
     */
    protected static $controllers = array();

    /**
     * Get a controller, creating one if it doesn't exist already.
     * 
     * @static
     * @param string $class controller class with namespace
     * @return object controller
     */
    public static function get($class)
    {
        if (isset($controllers[$class]) === false) {
            $controllers[$class] = new $class();
        }
        return $controllers[$class];
    }
}
