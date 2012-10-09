<?php
/**
 * @brief Plugins handling class.
 * @author nekith@gmail.com
 */

class strayPlugins
{
  /**
   * Request object.
   * @var strayRoutingRequest
   */
  private $_request;

  /**
   * Constructor.
   * @param strayRoutingRequest $request request object
   */
  public function __construct(strayRoutingRequest $request)
  {
    $this->_request = $request;
  }

  /**
   * Includes enabled plugins.
   */
  public function Run()
  {
    // install plugins
    if (true === isset(strayConfigInstall::fGetInstance()->Config()->plugins))
    {
      $plugins = strayConfigInstall::fGetInstance()->Config()->plugins;
      if (true === is_array($plugins))
        foreach ($plugins as $elem)
          if (true === is_dir(STRAY_PATH_TO_PLUGINS . $elem)
              && true === file_exists(STRAY_PATH_TO_PLUGINS . $elem . '/require.php'))
            require STRAY_PATH_TO_PLUGINS . $elem . '/require.php';
    }
    // app plugins
    if (true === isset(strayConfigApp::fGetInstance($this->_request->app)->Config()->plugins))
    {
      $plugins = strayConfigApp::fGetInstance($this->_request->app)->Config()->plugins;
      foreach ($plugins as $elem)
        if (true === is_dir(STRAY_PATH_TO_PLUGINS . $elem)
            && true === file_exists(STRAY_PATH_TO_PLUGINS . $elem . '/require.php'))
          require STRAY_PATH_TO_PLUGINS . $elem . '/require.php';
    }
  }
}
