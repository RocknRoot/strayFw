<?php
/**
 * Multiton
 * @brief Configuration class for applications.
 * @author nekith@gmail.com
 * @final
 */

final class strayConfigApp extends strayAMultiton
{
  /**
   * Application name.
   * @var string
   */
  private $_app;
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
   * Construct with application $args[0].
   * @param array $args arguments
   */
  protected function __construct($args)
  {
    $this->_app = $args[0];
    $config = strayConfigFile::fParse(STRAY_PATH_TO_APPS . $this->_app . '/settings');
    $routes = strayConfigFile::fParse(STRAY_PATH_TO_APPS . $this->_app . '/routes');
    if (false === $config)
      throw new strayExceptionNotfound(strayExceptionNotfound::NOTFOUND_APP, 'can\'t find app "' . $this->_app . '" settings/routes');
    if (false === $routes)
      throw new strayExceptionNotfound(strayExceptionNotfound::NOTFOUND_APP, 'can\'t find app "' . $this->_app . '" routes');
    $this->_config = $config;
    $this->_routes = $routes;
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

  /**
   * Check databases and includes needed databases that aren't yet.
   */
  public function PrepareDatabases()
  {
    if (true === isset($this->_config['databases']) && true === is_array($this->_config['databases']))
      foreach ($this->_config['databases'] as $db)
        strayModelsDatabase::fGetInstance($db);
  }
}
