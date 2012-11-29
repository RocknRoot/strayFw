<?php
/**
 * @brief Base for all widgets' views.
 * @author nekith@gmail.com
 * @abstract
 */ 

abstract class strayAppsWidgetAViews
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
   * Run the widget view logic.
   * @param strayRoutingRequest $request request
   * @return strayAppsARender generated render
   */
  public function Run(strayRoutingRequest $request)
  {
    $method = ucfirst($request->view) . 'View';
    if (false === method_exists($this, $method))
      throw new strayExceptionNotfound(strayExceptionNotfound::NOTFOUND_ACTION, 'can\'t find action '
      . $request->app . '.' . $request->widget . ':' . $request->view);
    $args = array($request);
    if (0 != count($request->params))
      $args = array_merge($args, $request->params);
    $render = call_user_func_array(array($this, $method), $args);
    return $render;
  }

  /**
   * Run another view in the same app.
   * @param strayRoutingRequest $request current request object
   * @param string $widget widget name
   * @param string $view new widget view name
   * @param array $params new widget action arguments
   * @return string generated content
   */
  protected function _RunSubView(strayRoutingRequest $request, $widget, $view, array $params = array())
  {
    $path = $this->GetPathApp() . '/widgets/' . $widget . '/' . $widget . '.views.php';
    if (false === file_exists($path))
      throw new strayExceptionNotFound(strayExceptionNotfound::NOTFOUND_WIDGET, 'can\'t find "' . $request->app . '.' . $widget . '" widget');
    require_once $path;
    $type = 'apps' . ucfirst($request->app) . ucfirst($widget) . 'Views';
    $object = new $type($this->GetPathApp(), $this->GetPathApp() . '/widgets/' . $widget . '/');
    $newRequest = clone $request;
    $newRequest->widget = $widget;
    $newRequest->view = $view;
    $newRequest->params = $params;
    $render = $object->Run($newRequest);
    if (!($render instanceof strayAppsARender))
      throw new strayExceptionError('render isn\'t a render (' . var_export($newRequest) . ')');
    return $render->Render();
  }
}
