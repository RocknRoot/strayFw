<?php
/**
 * @brief These are all widgets orders.
 * @author nekith@gmail.com
 * @final
 */

final class strayCommandOrdersWidget
{
  /**
   * Create a new widget.
   * @param array $params order params
   * @param array $options order options
   * @static
   */
  static public function fCreate(array $params = null, array $options = null)
  {
    if (2 != count($params))
      throw new strayException('wrong syntax for widget:create');
    $path = STRAY_PATH_TO_APPS . $params[0] . '/widgets/' . $params[1];
    if (true === is_dir($path))
      throw new strayExceptionError('widget "' . $params[0] . '.' . $params[1] . '" already exists');
    // mkdirs
    if (false === mkdir($path) || false === mkdir(STRAY_PATH_TO_APPS . $params[0] . '/templates/' . $params[1]))
      throw new strayExceptionFatal('can\'t mkdir');
    // files
    if (false === touch(STRAY_PATH_TO_APPS . $params[0] . '/templates/' . $params[1] . '/index.html.twig'))
        throw new strayExceptionFatal('can\'t touch (this!)');
    // xxx.controls.php
    $file = fopen($path . '/' . $params[1] . '.controls.php', 'x+');
    if (false === $file)
      throw new strayExceptionFatal('can\'t create file ' . $params[1] . '.controls.php');
    if (false === fwrite($file, "<?php\n\nfunction apps" . ucfirst($params[0]) . ucfirst($params[1])
        . "Index()\n{\n}\n"))
      throw new strayExceptionFatal('can\'t write in file ' . $params[1] . '.controls.php');
    fclose($file);
    // xxx.views.php
    $file = fopen($path . '/' . $params[1] . '.views.php', 'x+');
    if (false === $file)
      throw new strayExceptionFatal('can\'t create file ' . $params[1] . '.views.php');
    if (false === fwrite($file, "<?php\nrequire '" . $params[1] . ".controls.php';\n\nclass apps"
        . ucfirst($params[0]) . ucfirst($params[1])
        . "Views extends strayAppsWidgetAViews\n{\n  public function IndexView(strayRoutingRequest" . ' $request' . ")\n"
        . "  {\n    " . '$render = new strayAppsRenderTemplate($this, \'' . $params[1] . '/index.html.twig\');' . "\n"
        . '    return $render;' . "\n  }\n}\n"))
      throw new strayExceptionFatal('can\'t write in file ' . $params[1] . '.views.php');
    fclose($file);
    // xxx.scripts.php
    $file = fopen($path . '/' . $params[1] . '.scripts.php', 'x+');
    if (false === $file)
      throw new strayExceptionFatal('can\'t create file ' . $params[1] . '.scripts.php');
    if (false === fwrite($file, "<?php\n\nclass apps"
        . ucfirst($params[0]) . ucfirst($params[1])
        . "Scripts extends strayAppsWidgetAScripts\n{\n  public function IndexScript(" . 'array $params = null, array $options = null'
        . ")\n  {\n  }\n}\n"))
      throw new strayExceptionFatal('can\'t write in file ' . $params[1] . '.scripts.php');
    fclose($file);
    // -
    echo 'Widget "' . $params[0] . '.' . $params[1] . "\" created !\n";
  }

  /**
   * Execute a widget script.
   * @param array $params order params
   * @param array $options order options
   * @static
   */
  static public function fScript(array $params = null, array $options = null)
  {
    if (3 > count($params))
      throw new strayException('wrong syntax for widget:exec');
    $app = array_shift($params);
    $widget = array_shift($params);
    $path = STRAY_PATH_TO_APPS . $app . '/widgets/' . $widget . '/' . $widget . '.scripts.php';
    if (false === file_exists($path))
      throw new strayExceptionError('scripts for widget "' . $app . '.' . $widget . '" don\'t exist');
    strayConfigApp::fGetInstance($app)->PrepareDatabases();
    $fakeRequest = new strayRoutingRequest('/', 'GET');
    $fakeRequest->app = $app;
    $fakeRequest->widget = $widget;
    $plugins = new strayPlugins($fakeRequest);
    $plugins->Init();
    require $path;
    $class = 'apps' . ucfirst($app) . ucfirst($widget) . 'Scripts';
    $obj = new $class(STRAY_PATH_TO_APPS . $app, STRAY_PATH_TO_APPS . $app . '/widgets/' . $widget);
    $script = array_shift($params);
    echo 'Executing ' . $app . '.' . $widget . ':' . $script . '...' . PHP_EOL . PHP_EOL;
    $ret = $obj->Run($app, $widget, $script, $params, $options);
    echo PHP_EOL . 'Return : ' . PHP_EOL . PHP_EOL;
  }

  /**
   * Private constructor.
   */
  private function __construct() {}
}
