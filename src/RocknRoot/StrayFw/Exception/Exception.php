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
     * @param string    $message  exception message to throw
     * @param int       $code     exception code
     * @param Exception $previous previous exception used for exception chaining
     */
    public function __construct(string $message = '', int $code = 0, \Exception $previous = null)
    {
        Logger::get()->critical('exception : ' . $message);
        parent::__construct($message, $code, $previous);
    }
}
