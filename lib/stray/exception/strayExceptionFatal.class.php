<?php
/**
 * @brief This is the fatal exception class.
 * @author nekith@gmail.com
 */

class strayExceptionFatal extends strayException
{
  /**
   * Get the exception message.
   * @return string message
   * @final
   */
  final public function Display()
  {
    return 'Fatal error: ' . parent::getMessage();
  }
}
