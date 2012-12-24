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
  public function Init()
  {
    // install plugins
    $config = strayConfigInstall::fGetInstance()->GetConfig();
    if (true === isset($config['plugins']))
    {
      $plugins = $config['plugins'];
      if (true === is_array($plugins))
        foreach ($plugins as $elem)
          if (null != $elem &&true === is_dir(STRAY_PATH_TO_LIB . 'plugins/' . $elem)
              && true === file_exists(STRAY_PATH_TO_LIB . 'plugins/' . $elem . '/require.php'))
            require STRAY_PATH_TO_LIB . 'plugins/' . $elem . '/require.php';
    }
    // app plugins
    $config = strayConfigApp::fGetInstance($this->_request->app)->GetConfig();
    if (true === isset($config['plugins']))
    {
      $plugins = $config['plugins'];
      foreach ($plugins as $elem)
        if (null != $elem && true === is_dir(STRAY_PATH_TO_LIB . 'plugins/' . $elem)
          && true === file_exists(STRAY_PATH_TO_LIB . 'plugins/' . $elem . '/require.php'))
          require STRAY_PATH_TO_LIB . 'plugins/' . $elem . '/require.php';
    }
  }
}
