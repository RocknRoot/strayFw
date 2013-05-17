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
   * @var mixed[]
   */
  private $_vars;
  /**
   * Registered providers.
   * @var callable[]
   */
  private $_providers;

  /**
   * Constructor.
   */
  protected function __construct()
  {
    $this->_vars = array();
    $this->_providers = array();
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
    {
      if (false === isset($this->_providers[$name]))
        return null;
      $this->_vars[$name] = $this->_providers[$name]($name);
    }
    else
    {
      $this->_vars[$name] = $get($name);
    }
    return $this->_vars[$name];
  }

  /**
   * Define a provider for further Get method uses.
   * @param string $name var name
   * @param callable $get setter function
   */
  public function DefineProvider($name, callable $get)
  {
    $this->_providers[$name] = $get;
  }
  
  /**
   * Set var value.
   * @param string $name var name
   * @param mixed $value value
   * @return mixed value
   */
  public function Set($name, $value)
  {
    $this->_vars[$name] = $value;
    return $value;
  }
}
