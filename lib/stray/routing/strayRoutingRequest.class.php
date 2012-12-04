<?php
/**
 * @brief Routing request container class.
 * @author nekith@gmail.com
 */

class strayRoutingRequest
{
  /**
   * Entire string url.
   * @var string
   */
  private $_url;
  /**
   * HTTP request method.
   * @var string
   */
  private $_method;
  /**
   * App name.
   * @var string
   */
  public $app;
  /**
   * Widget name.
   * @var string
   */
  public $widget;
  /**
   * View name.
   * @var string
   */
  public $view;
  /**
   * Params values.
   * @var array
   */
  public $params;
  /**
   * POST values.
   * @var strayRoutingRequestPost
   */
  public $post;
  /**
   * True if debug mode.
   * @var bool
   */
  private $_debug;

  /**
   * Constructor.
   * @param string $url entire url
   * @param string $method HTTP method
   * @param bool $debug true if debug mode
   */
  public function __construct($url, $method, $debug = false)
  {
    $this->post = new strayRoutingRequestPost();
    $this->params = array();
    $this->_url = $url;
    $this->_method = $method;
    $this->_debug = true === $debug;
  }

  /**
   * Return true if debug mode.
   * @return bool is debug
   */
  public function IsDebug()
  {
    return $this->_debug;
  }

  /**
   * Get complete url.
   * @return string url
   */
  public function GetUrl()
  {
    return $this->_url;
  }

  /**
   * Get HTTP method.
   * @return string method
   */
  public function GetMethod()
  {
    return $this->_method;
  }

  /**
   * Get requested scheme.
   * @return string scheme
   */
  public function GetScheme()
  {
    return parse_url($this->_url, PHP_URL_SCHEME);
  }

  /**
   * Get requested domain.
   * @return string domain
   */
  public function GetDomain()
  {
    $matches = null;
    $host = parse_url($this->_url, PHP_URL_HOST);
    preg_match('/[^\.\/]+\.[^\.\/]+$/', $host, $matches);
    $domain = strtolower($matches[0]);
    if (null != strayConfigInstall::fGetInstance()->GetConfig()['domainprefix'])
    {
      $domain = strayConfigInstall::fGetInstance()->GetConfig()['domainprefix'] . '.' . $domain;
    }
    return $domain;
  }
}
