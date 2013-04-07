<?php
/**
 * @brief This is the redirection exception class.
 * @author nekith@gmail.com
 */

class strayExceptionRedirect extends strayException
{
  /**
   * Destination URI.
   * @var string
   */
  protected $_uri;

  /**
   * Construct.
   * @param string $uri URI
   * @param string $message see Exception
   * @param int $code see Exception
   * @param strayException $previous see Exception
   */
  public function __construct($uri, $message = '', $code = 0, strayException $previous = null)
  {
    $this->_uri = $uri;
    parent::__construct($message, $code, $previous);
  }

  /**
   * Get destination URI.
   * @return string URI
   */
  public function GetUri()
  {
    return $this->_uri;
  }
}
