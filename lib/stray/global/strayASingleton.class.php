<?php
/**
 * @brief Generic singleton.
 * @author nekith@gmail.com
 * @abstract
 */

abstract class strayASingleton
{
  /**
   * Single instance of strayASingleton child.
   * @var array
   */
  static private $_instance = array();

  /**
   * Get the single strayASingleton child instance.
   * @return strayASingleton single instance
   * @static
   */
  static public function fGetInstance()
  {
    $class = get_called_class();
    if (false === array_key_exists($class, self::$_instance))
    {
      self::$_instance[$class] = new static(func_get_args());
    }
    return self::$_instance[$class];
  }

  /**
   * Delete the single strayASingleton child instance.
   * @return bool true if instance wasn't null
   */
  static public function fDelInstance()
  {
    if (true === isset(self::$_instance))
    {
      unset(self::$_instance);
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
