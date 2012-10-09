<?php
/**
 * Singleton.
 * @brief Wrapper for session vars.
 * @author nekith@gmail.com
 */

final class straySession extends strayASingleton
{
  /**
   * Session vars.
   * @var array
   */
  private $_vars;

  /**
   * Construct.
   */
  protected function __construct()
  {
    session_start();
    $this->_vars = array();
    foreach ($_SESSION as $key => $e)
      $this->_vars[$key] = $e;
  }

  /**
   * Get value of key $name.
   * @param $name key
   * @return value
   * @method
   */
  public function __get($name)
  {
    if (false === isset($this->_vars[$name]))
      return null;
    return true === array_key_exists($name, $this->_vars) ? $this->_vars[$name]
        : null;
  }

  /**
   * Check if it contains key $name.
   * @param $name key
   * @return bool true if has $name
   * @method
   */
  public function __isset($name)
  {
    return isset($this->_vars[$name]);
  }

  /**
   * Set key $name with value $value.
   * @param $name key
   * @param $value value
   */
  public function __set($name, $value)
  {
    $tmp = $this->_vars;
    $tmp[$name] = $value;
    $this->_vars = $tmp;
    $_SESSION[$name] = $value;
  }

  /**
   * Remove the key $name.
   * @param $name key
   */
  public function __unset($name)
  {
    $ret = $this->__isset($name);
    if (true === $ret)
    {
      unset($this->_vars[$name]);
      unset($_SESSION[$name]);
    }
    return $ret;
  }

  /**
   * Clear all session vars.
   */
  public function Clear()
  {
    unset($this->_vars);
    $this->_vars = array();
    session_unset();
    session_destroy();
  }
}
