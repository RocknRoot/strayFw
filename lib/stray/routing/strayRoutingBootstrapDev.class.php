<?php
/**
 * Singleton.
 * @brief Development bootstrap class.
 * @author nekith@gmail.com
 */

final class strayRoutingBootstrap extends strayASingleton implements strayRoutingIBootstrap
{
  /**
   * Routing request.
   * @var strayRoutingRequest
   */
  protected $_request;

  /**
   * Get routing request.
   * @return strayRoutingRequest
   */
  public function GetRequest()
  {
    return $this->_request;
  }

  /**
   * Bootstrapping the installation.
   * @param string $url routing requested URL
   * @param string $method HTTP method
   */
  public function Run($url, $method)
  {
    ignore_user_abort();
    try
    {
      ob_start();
      $this->_request = strayRouting::fGetInstance()->Route($url, $method, true);
      if (true === strayRoutingBootstrapDevApp::fGetInstance()->IsItForMe($url))
        strayRoutingBootstrapDevApp::fGetInstance()->Run($url, $method);
      else
      {
        $startTime = microtime(true);
        strayProfiler::fGetInstance()->RequestStart();
        $this->_LoadExt($this->_request);
        strayConfigApp::fGetInstance($this->_request->app)->PrepareDatabases();
        $path = STRAY_PATH_TO_APPS . $this->_request->app . '/widgets/'
          . $this->_request->widget . '/' . $this->_request->widget . '.views.php';
        if (false === file_exists($path))
          throw new strayExceptionNotfound(strayExceptionNotfound::NOTFOUND_WIDGET, 'can\'t find "' . $this->_request->widget . '"');
        $type = 'apps' . ucfirst($this->_request->app) . ucfirst($this->_request->widget) . 'Views';
        if (false === class_exists($type))
          require $path;
        strayProfiler::fGetInstance()->AddTimerRoutingLog(microtime(true) - $startTime);
        $startTime = microtime(true);
        $view = new $type(STRAY_PATH_TO_APPS . $this->_request->app, STRAY_PATH_TO_APPS . $this->_request->app . '/widgets/' . $this->_request->widget);
        $render = $view->Run($this->_request);
        strayProfiler::fGetInstance()->AddTimerViewLog(microtime(true) - $startTime);
        $startTime = microtime(true);
        if (!($render instanceof strayAppsARender))
          throw new strayExceptionError('render isn\'t a render (' . var_export($this->_request, true) . ')');
        echo $render->Render();
        if (!($render instanceof strayAppsRenderTemplate)
            && true === empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && 'xmlhttprequest' !== strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
          strayProfiler::fGetInstance()->needToDisplay = false;
        strayProfiler::fGetInstance()->AddTimerRenderLog(microtime(true) - $startTime);
        strayProfiler::fGetInstance()->RequestEnd();
      }
      ob_end_flush();
    }
    catch (strayExceptionRedirect $e)
    {
      ob_end_clean();
      $url = strayRouting::fGenerateNiceUrl($e->GetUri());
      header('Location: ' . $url);
      exit();
    }
  }

  /**
   * Load external.
   * @param strayRoutingRequest $request request
   */
  private function _LoadExt(strayRoutingRequest $request)
  {
    static $done = false;
    if (false === $done)
    {
      strayExtTwig::fGetInstance()->Init();
      $plugins = new strayPlugins($request);
      $plugins->Init();
      $done = true;
    }
  }
}
