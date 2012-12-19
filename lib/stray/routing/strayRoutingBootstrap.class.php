<?php
/**
 * Singleton.
 * @brief Production bootstrap class.
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
    set_error_handler(array('strayRoutingBootstrap', 'fError'));
    ignore_user_abort();
    ob_start();
    try
    {
      $this->_request = strayRouting::fGetInstance()->Route($url, $method);
      $this->_LoadExt($this->_request);
      strayConfigApp::fGetInstance($this->_request->app)->PrepareDatabases();
      $path = STRAY_PATH_TO_APPS . $this->_request->app . '/widgets/'
          . $this->_request->widget . '/' . $this->_request->widget . '.views.php';
      if (false === file_exists($path))
        throw new strayExceptionNotfound(strayExceptionNotfound::NOTFOUND_WIDGET, 'can\'t find "' . $this->_request->widget . '"');
      require_once $path;
      $type = 'apps' . ucfirst($this->_request->app) . ucfirst($this->_request->widget) . 'Views';
      $view = new $type(STRAY_PATH_TO_APPS . $this->_request->app .'/', STRAY_PATH_TO_APPS . $this->_request->app . '/widgets/' . $this->_request->widget . '/');
      $render = $view->Run($this->_request);
      if (!($render instanceof strayAppsARender))
        throw new strayExceptionError('render isn\'t a render (' . var_export($this->_request) . ')');
      echo $render->Render();
      ob_end_flush();
    }
    catch (strayExceptionRedirect $e)
    {
      ob_end_clean();
      $url = $e->GetUri();
      if ($url[strlen($url) - 1] == '.')
        $url = strayRoutingBootstrap::fGetInstance()->GetRequest()->GetScheme() . '://' . (1 == strlen($url) ? null : $url) . strayRoutingBootstrap::fGetInstance()->GetRequest()->GetDomain();
      else
        $url = strayRoutingBootstrap::fGetInstance()->GetRequest()->GetScheme() . '://' . strayRouting::fGetInstance()->GetHost() . '/' . ltrim($url, '/');
      header('Location: ' . $url);
      exit();
    }
    catch (strayExceptionNotfound $e)
    {
      strayLog::fGetInstance()->Notice('404 : ' . $url);
      if (strayExceptionNotfound::NOTFOUND_APP == $e->GetType())
      {
        header('HTTP/1.1 404 Not Found');
      }
      elseif (strayExceptionNotfound::NOTFOUND_ACTION == $e->GetType()
        || strayExceptionNotfound::NOTFOUND_WIDGET == $e->GetType())
      {
        header('HTTP/1.0 404 Not Found');
      }
    }
    catch (strayException $e)
    {
      if (0 != strlen(ob_get_contents()))
        ob_end_clean();
      $log = strayLog::fGetInstance();
      $log->FwFatal($e->Display());
    }
    catch (Exception $e)
    {
      if (0 != strlen(ob_get_contents()))
        ob_end_clean();
      $log = strayLog::fGetInstance();
      $log->FwFatal($e->getMessage());
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

  /**
   * Error callback for PHP errors.
   */
  static public function fError($errno, $errstr, $errfile, $errline)
  {
    if (0 != strlen(ob_get_contents()))
      ob_end_clean();
    $log = strayLog::fGetInstance();
    $msg = $errstr . ' . ' . $errfile . ' l' . $errline;
    switch ($errno)
    {
      case E_ERROR:
        $log->SysError($msg);
      case E_WARNING:
        $log->SysWarning($msg);
      default:
        $log->SysNotice($msg);
    }
    echo 'Internal server error';
  }
}
