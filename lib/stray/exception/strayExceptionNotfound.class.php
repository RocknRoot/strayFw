<?php
/**
 * @brief This is the not found error exception class.
 * @author nekith@gmail.com
 */

class strayExceptionNotfound extends strayException
{
  const NOTFOUND_APP = 1;
  const NOTFOUND_WIDGET = 2;
  const NOTFOUND_ACTION = 3;
  const NOTFOUND_SCRIPT = 4;

  /**
   * Not found type.
   * @var int
   */
  protected $type;

  /**
   * Construct.
   * @param int $type not found type
   * @param string $message see Exception
   * @param int $code see Exception
   * @param strayException $previous see Exception
   */
  public function __construct($type, $message = '', $code = 0, strayException $previous = null)
  {
    $this->_type = $type;
    parent::__construct($message, $code, $previous);
  }

  /**
   * Get not found error type.
   * @return int type
   */
  public function GetType()
  {
    return $this->_type;
  }

  /**
   * Get the exception message.
   * @return string message
   * @final
   */
  final public function Display()
  {
    return 'Notfound: ' . parent::getMessage();
  }
}
