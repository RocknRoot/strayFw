<?php
/**
 * @brief This is the reroute exception class.
 * @author nekith@gmail.com
 */

class strayExceptionReroute extends strayException
{
  /**
   * Destination URI.
   * @var string
   */
  protected $_uri;
  /**
   * Destination HTTP method.
   * @var string
   */
  protected $_method;

  /**
   * Construct.
   * @param string $uri URI
   * @param string $method HTTP method
   * @param string $message see Exception
   * @param int $code see Exception
   * @param strayException $previous see Exception
   */
  public function __construct($uri, $method = 'GET', $message = '', $code = 0, strayException $previous = null)
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

  /**
   * Get destination HTTP method.
   * @return string HTTP method
   */
  public function GetMethod()
  {
    return $this->_method;
  }
}
