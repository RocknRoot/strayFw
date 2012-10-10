<?php
/**
 * Singleton.
 * @brief Wrapper for cookies vars.
 * @author nekith@gmail.com
 */

final class strayCookies extends strayASingleton
{
  /**
   * Cookies vars.
   * @var array
   */
  private $_vars;

  /**
   * Construct.
   */
  protected function __construct()
  {
    $this->_vars = array();
    foreach ($_COOKIES as $key => $e)
      $this->_vars[$key] = $e;
  }

  /**
   * Get value of key $name.
   * @param $name key
   * @return value
   */
  public function Get($name)
  {
    if (false === isset($this->_vars[$name]))
      return null;
    return $this->_vars[$name];
  }

  /**
   * Check if it contains key $name.
   * @param $name key
   * @return bool true if has $name
   */
  public function Has($name)
  {
    return isset($this->_vars[$name]);
  }

  /**
   * Set key $name with value $value.
   * @param $name key
   * @param $value value
   * @param $ttl seconds to live
   * @param $path cookie path
   */
  public function Set($name, $value, $ttl = 0, $path = null)
  {
    $this->_vars[$name] = $value;
    if (null != $path)
      setcookie($name, $value, time() + $ttl, $path);
    else
      setcookie($name, $value, time() + $ttl);
  }

  /**
   * Remove the key $name.
   * @param $name key
   */
  public function Del($name)
  {
    $ret = $this->Has($name);
    if (true === $ret)
    {
      unset($this->_vars[$name]);
      setcookie($name, '--', 1);
    }
    return $ret;
  }

  /**
   * Clear all cookies.
   */
  public function Clear()
  {
    foreach ($this->_vars as $v)
      setcookie($name, '--', 1);
    unset($this->_vars);
    $this->_vars = array();
  }
}
