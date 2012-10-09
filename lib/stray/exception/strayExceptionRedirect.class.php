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
   * Redirection mode.
   * @var bool
   */
  protected $_httpMode;

  /**
   * Construct.
   * @param string $uri uri
   * @param bool $httpMode HTTP redirection if true
   * @param string $message see Exception
   * @param int $code see Exception
   * @param strayException $previous see Exception
   */
  public function __construct($uri, $httpMode = true, $message = '', $code = 0, strayException $previous = null)
  {
    $this->_uri = $uri;
    $this->_httpMode = $httpMode;
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
   * Check if redirection mode is active.
   * @return bool HTTP redirection if true
   */
  public function IsHttpMode()
  {
    return $this->_httpMode;
  }
}
