<?php

namespace RocknRoot\StrayFw\Exception;

use RocknRoot\StrayFw\Logger;

/**
 * Base class for framework specific exceptions.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Exception extends \Exception
{
    /**
     * Exception initialization.
     *
     * @param string $message the exception message to throw
     * @param int    $code    the exception code
     * @param Exception the previous exception used for the exception chaining
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        Logger::get()->critical('exception : ' . $message);
        parent::__construct($message, $code, $previous);
    }
}
