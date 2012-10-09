<?php
/**
 * Singleton.
 * @brief Bootstrap class.
 * @author nekith@gmail.com
 */

final class strayRoutingBootstrap extends strayASingleton
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
   * @param string $uri routing requested URI
   */
  public function Run($uri)
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
          . $this->_request->widget . '/' . $this->_request->widget . '.view.php';
      if (false === file_exists($path))
        throw new strayExceptionNotfound(strayExceptionNotfound::NOTFOUND_WIDGET, 'can\'t find "' . $this->_request->widget . '"');
      require_once $path;
      $type = 'widget' . ucfirst($this->_request->app) . ucfirst($this->_request->widget, true) . 'View';
      $view = new $type(STRAY_PATH_TO_APPS . $this->_request->app, STRAY_PATH_TO_APPS . $this->_request->app . '/widgets/' . $this->_request->widget);
      $render = $view->Run($this->_request);
      if (!($render instanceof strayAppsARender))
        throw new strayExceptionError('render isn\'t a render (' . var_export($this->_request) . ')');
      echo $render->Render();
      ob_end_flush();
    }
    catch (strayExceptionRedirect $e)
    {
      ob_end_clean();
      if (true === $e->IsHttpMode())
      {
        header('Location: http://' . strayRouting::fGetInstance()->GetHost() . '/' . $e->GetUri());
        exit();
      }
      $this->Run($e->GetUri());
    }
    catch (strayExceptionNotfound $e)
    {
      strayLog::fGetInstance()->Notice('404 : ' . (true === isset($this->_request->entireString) ? $this->_request->entireString : $uri));
      if (strayExceptionNotfound::NOTFOUND_APP == $e->GetType())
      {
        header('HTTP/1.1 404 Not Found');
        header('Location: http://' . $_SERVER['HTTP_HOST'] . '/hub/home/error/unknown');
      }
      elseif (strayExceptionNotfound::NOTFOUND_ACTION == $e->GetType()
        || strayExceptionNotfound::NOTFOUND_MODULE == $e->GetType())
      {
        header('HTTP/1.0 404 Not Found');
        if (true === isset(strayConfigApp::fGetInstance($this->_request->app)->Config()->module)
          && true === isset(strayConfigApp::fGetInstance($this->_request->app)->Config()->module->error)
          && true === isset(strayConfigApp::fGetInstance($this->_request->app)->Config()->module->error->routing))
        {
          $url = strayConfigApp::fGetInstance($this->_request->app)->Config()->module->error->routing;
          header('Location: http://' . $_SERVER['HTTP_HOST'] . $url);
        }
        else
          header('Location: http://' . $_SERVER['HTTP_HOST'] . '/hub/home/error/unknown');
      }
    }
    catch (strayException $e)
    {
      if (0 != strlen(ob_get_contents()))
        ob_end_clean();
      $log = strayLog::fGetInstance();
      $log->FwFatal($e->Display());
      header('Location: http://' . $_SERVER['HTTP_HOST'] . '/hub/home/error/unknown');
    }
    catch (Exception $e)
    {
      if (0 != strlen(ob_get_contents()))
        ob_end_clean();
      $log = strayLog::fGetInstance();
      $log->FwFatal($e->getMessage());
      header('Location: http://' . $_SERVER['HTTP_HOST'] . '/hub/home/error/unknown');
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
      //$plugins = new strayPlugins($request);
      //$plugins->Run();
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
