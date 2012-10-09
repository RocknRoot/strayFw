<?php
/**
 * @brief This is the error exception class.
 * @author nekith@gmail.com
 */

class strayExceptionError extends strayException
{
  /**
   * Get the exception message.
   * @return string message
   * @final
   */
  final public function Display()
  {
    return 'Error: ' . parent::getMessage();
  }
}
