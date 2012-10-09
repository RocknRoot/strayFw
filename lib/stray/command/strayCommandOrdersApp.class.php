<?php
/**
 * @brief These are all applications orders.
 * @author nekith@gmail.com
 * @final
 */

final class strayCommandOrdersApp
{
  /**
   * Create a new application.
   * @param array $params order params
   * @param array $options order options
   * @static
   */
  static public function fCreate(array $params = null, array $options = null)
  {
    if (1 != count($params))
      throw new strayException('wrong syntax for app:create');
    $path = STRAY_PATH_TO_APPS . $params[0];
    if (true === is_dir($path))
      throw new strayExceptionError('app "' . $params[0] . '" already exists');
    // mkdirs
    if (false === mkdir($path) || false === mkdir($path . '/widgets')
        || false === mkdir($path . '/i18n')
        || false === mkdir($path . '/templates')
        || false === mkdir(STRAY_PATH_TO_WEB . 'css/' . $params[0])
        || false === mkdir(STRAY_PATH_TO_WEB . 'js/' . $params[0]))
      throw new strayExceptionFatal('can\'t mkdir');
    // files
    if (false === strayConfigFile::fCreate($path . '/routes')
        || false === strayConfigFile::fCreate($path . '/i18n/en')
        || false === touch($path . '/templates/base.html')
        || false === touch(STRAY_PATH_TO_WEB . 'css/' . $params[0] . '/main.css')
        || false === touch(STRAY_PATH_TO_WEB . 'js/' . $params[0] . '/main.js'))
      throw new strayExceptionFatal('can\'t touch (this!)');
    echo 'App "' . $params[0] . "\" created!\n";
  }

  /**
   * Private constructor.
   */
  private function __construct() {}
}
