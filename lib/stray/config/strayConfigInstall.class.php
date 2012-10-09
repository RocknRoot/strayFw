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
    // NoREL connection
    if (false === empty($this->_config['norel']))
    {
      $user = true === isset($this->_config['norel']['user']) ? $this->_config['norel']['user'] : null;
      $pass = true === isset($this->_config['norel']['pass']) ? $this->_config['norel']['pass'] : null;
      strayNorel::fGetInstance()->Config($this->_config['norel']['host'], $user, $pass);
    }
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
   * Get the routes array.
   * @return array routes file decode result
   */
  public function GetRoutes()
  {
    return $this->_routes;
  }
}
