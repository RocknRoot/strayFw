<?php
/**
 * @brief This is the exception class.
 * @author nekith@gmail.com
 */

class strayException extends Exception
{
  /**
   * Get the exception message.
   * @return string message
   */
  public function Display()
  {
    return 'Exception: ' . parent::getMessage();
  }
}
