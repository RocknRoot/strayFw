<?php
/**
 * Singleton.
 * @brief Registry class for per-request persistance.
 * @author nekith@gmail.com
 */

final class strayRegistry extends strayASingleton
{
  /**
   * Stored data.
   * @var array
   */
  private $_vars;

  /**
   * Constructor.
   */
  protected function __construct()
  {
    $this->_vars = array();
  }

  /**
   * Get var value. Optionally set it with callable.
   * @param string $name var name
   * @param callable $get setter function if var is empty
   * @return mixed var value.
   */
  public function Get($name, callable $get = null)
  {
    if (true === isset($this->_vars[$name]))
      return $this->_vars[$name];
    if (null === $get)
      return null;
    $this->_vars[$name] = $get($name);
    return $this->_vars[$name];
  }
  
  /**
   * Set var value.
   * @param string $name var name
   * @param mixed $value value
   */
  public function Set($name, $value)
  {
    $this->_vars[$name] = $value;
    return $value;
  }
}
