<?php
/**
 * @brief These are all plugins' orders.
 * @author nekith@gmail.com
 * @final
 */

final class strayCommandOrdersPlugin
{
  /**
   * Create a new plugin.
   * @param array $params order params
   * @param array $options order options
   * @static
   */
  static public function fCreate(array $params = null, array $options = null)
  {
    if (1 > count($params))
      throw new strayException('wrong syntax for plugin:create');
    $path = STRAY_PATH_TO_LIB . 'plugins/' . $params[0] . '/';
    if (true === is_dir($path))
      throw new strayExceptionError('plugin ' . $params[0] . ' already exists');
    // create directory
    if (false === mkdir($path))
      throw new strayExceptionFatal('can\'t mkdir');
    // create require.php
    if (false === touch($path . 'require.php'))
      throw new strayExceptionFatal('can\'t touch (this!)');
    $file = fopen($path . 'require.php', 'w+');
    if (false === fwrite($file, "<?php\n"))
      throw new strayExceptionFatal('can\'t write in ' . $path . 'require.php');
    fclose($file);
    // end
    echo 'Plugin ' . $params[0] . " created !\n";
  }

  /**
   * Private constructor.
   */
  private function __construct() {}
}
