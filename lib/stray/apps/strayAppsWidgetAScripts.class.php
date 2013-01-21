<?php
/**
 * @brief Base for all widgets' scripts.
 * @author nekith@gmail.com
 * @abstract
 */

abstract class strayAppsWidgetAScripts
{
  /**
   * Application path.
   * @var string
   */
  private $_pathApp;
  /**
   * Widget path.
   * @var string
   */
  private $_pathWidget;

  /**
   * Construct.
   * @param string $pathApp app path
   * @param string $pathWidget widget path
   */
  public function __construct($pathApp, $pathWidget)
  {
    $this->_pathApp = $pathApp;
    $this->_pathWidget = $pathWidget;
  }

  /**
   * Get app path.
   * @return string path
   */
  public function GetPathApp()
  {
    return $this->_pathApp;
  }

  /**
   * Get widget path.
   * @return string path
   */
  public function GetPathWidget()
  {
    return $this->_pathWidget;
  }

  /**
   * Run a widget script.
   * @param string $app app name
   * @param string $widget widget name
   * @param string $script script name
   * @param array $params script params
   * @param array $options script options
   * @return mixed user return
   */
  public function Run($app, $widget, $script, array $params = null, array $options = null)
  {
    $method = ucfirst($script) . 'Script';
    if (false === method_exists($this, $method))
      throw new strayExceptionNotfound(strayExceptionNotfound::NOTFOUND_SCRIPT, 'can\'t find script '
        . $app . '.' . $widget . ':' . $script);
    strayConfigApp::fGetInstance($app)->PrepareDatabases();
    return $this->$method($params, $options);
  }
}
