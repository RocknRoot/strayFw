<?php
/**
 * Singleton.
 * @brief Config of the stray installation.
 * @author nekith@gmail.com
 */

final class strayConfigInstall extends strayASingleton
{
  /**
   * Config file decode result.
   * @var array
   */
  private $_config;
  /**
   * Routes file decode result.
   * @var array
   */
  private $_routes;

  /**
   * Construct. Create databases instances.
   */
  protected function __construct()
  {
    $this->_config = strayConfigFile::fParse(STRAY_PATH_TO_INSTALL . 'config/settings');
    $this->_routes = strayConfigFile::fParse(STRAY_PATH_TO_INSTALL . 'config/routes');
  }

  /**
   * Get the config array.
   * @return array config file decode result
   */
  public function GetConfig()
  {
    return $this->_config;
  }
  
  /**
   * Get config tmp path.
   * @return stray tmp path
   */
  public function GetConfigTmp()
  {
      $tmp = rtrim($this->_config['tmp'], '/');
      if ('/' != $tmp[0])
        $tmp = realpath(STRAY_PATH_TO_INSTALL . $tmp);
      return $tmp . '/';
  }

  /**
   * Get the routes array.
   * @return array routes file decode result
   */
  public function GetRoutes()
  {
    return $this->_routes;
  }
}
