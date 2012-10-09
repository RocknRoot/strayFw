<?php
/**
 * @brief This is the not implemented error exception class.
 * @author nekith@gmail.com
 */

class strayExceptionNotImplemented extends strayException
{
  /**
   * Construct.
   * @param string $message see Exception
   * @param int $code see Exception
   * @param strayException $previous see Exception
   */
  public function __construct($message = '', $code = 0, strayException $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }

  /**
   * Get the exception message.
   * @return string message
   * @final
   */
  final public function Display()
  {
    return 'NotImplemented: ' . parent::getMessage();
  }
}
