<?php
/**
 * @brief Generic multiton.
 * @author nekith@gmail.com
 * @abstract
 */

abstract class strayAMultiton
{
  static private $_instances = array();

  /**
   * Get an instance associated to key.
   * @return object instance
   */
  static public function fGetInstance($key)
  {
    $class = get_called_class();
    if (false === array_key_exists($class, self::$_instances))
      self::$_instances[$class] = array();
    if (false === isset(self::$_instances[$class][$key]))
    {
      self::$_instances[$class][$key] = new static(func_get_args());
    }
    return self::$_instances[$class][$key];
  }

  /**
   * Delete an instance associated to key.
   * @return bool true if succesfully deleted
   */
  static public function fDelInstance($key)
  {
    if (true === isset(self::$_instances[$key]))
    {
      unset(self::$_instances[$key]);
      return true;
    }
    return false;
  }

  /**
   * Prevents direct creation.
   */
  protected function __construct() {}

  /**
   * Prevents cloning instance.
   */
  final private function __clone() {}

  /**
   * Prevents unserializing instance.
   */
  final private function __wakeup() {}
}
