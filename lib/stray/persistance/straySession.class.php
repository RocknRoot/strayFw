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
   * Get all session vars.
   * @return array session vars
   */
  public function All()
  {
      return $this->_vars;
  }
  
  /**
   * Get value of key $name.
   * @param string $name key
   * @return mixed value
   */
  public function Get($name)
  {
    if (false === isset($this->_vars[$name]))
      return null;
    return $this->_vars[$name];
  }

  /**
   * Check if it contains key $name.
   * @param string $name key
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
   */
  public function Set($name, $value)
  {
    $this->_vars[$name] = $value;
    $_SESSION[$name] = $value;
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
