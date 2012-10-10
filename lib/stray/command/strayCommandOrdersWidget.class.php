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
    if (false === touch(STRAY_PATH_TO_APPS . $params[0] . '/templates/' . $params[1] . '/index.html'))
        throw new strayExceptionFatal('can\'t touch (this!)');
    // xxx.controls.php
    $file = fopen($path . '/' . $params[1] . '.controller.php', 'x+');
    if (false === $file)
      throw new strayExceptionFatal('can\'t create file ' . $params[1] . '.controller.php');
    if (false === fwrite($file, "<?php\n\nfunction apps" . ucfirst($params[0]) . ucfirst($params[1])
        . "Index()\n{\n}\n"))
      throw new strayExceptionFatal('can\'t write in file ' . $params[1] . '.controller.php');
    fclose($file);
    // xxx.view.php
    $file = fopen($path . '/' . $params[1] . '.view.php', 'x+');
    if (false === $file)
      throw new strayExceptionFatal('can\'t create file ' . $params[1] . '.view.php');
    if (false === fwrite($file, "<?php\nrequire '" . $params[1] . ".controls.php';\n\nclass apps"
        . ucfirst($params[0]) . ucfirst($params[1])
        . "View extends strayAppsWidgetAView\n{\n  public function IndexAction(strayRoutingRequest" . ' $request' . ")\n"
        . "  {\n    " . '$view = new strayAppsRenderTemplate(\'' . $params[1] . '/index.html\');' . "\n"
        . '    return $view;' . "\n  }\n}\n"))
      throw new strayExceptionFatal('can\'t write in file ' . $params[1] . '.view.php');
    fclose($file);
    // -
    echo 'Widget "' . $params[0] . '.' . $params[1] . "\" created !\n";
  }

  /**
   * Private constructor.
   */
  private function __construct() {}
}
