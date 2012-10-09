<?php
/**
 * @brief Base for all widgets' views.
 * @author nekith@gmail.com
 * @abstract
 */ 

abstract class strayAppsWidgetAView
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
   * Construct/
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
   * Run the widget view logic.
   * @param strayRoutingRequest $request request
   * @return strayAppsARender generated render
   */
  public function Run(strayRoutingRequest $request)
  {
    $method = ucfirst($request->view) . 'Action';
    if (false === method_exists($this, $method))
      throw new strayExceptionNotfound(strayExceptionNotfound::NOTFOUND_ACTION, 'can\'t find action '
      . $request->app . '.' . $request->widget . ':' . $request->view);
    $args = array($request);
    if (0 != count($request->params))
      $args = array_merge($args, $request->params);
    $render = call_user_func_array(array($this, $method), $args);
    return $render;
  }
}
