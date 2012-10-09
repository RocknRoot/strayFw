<?php
/**
 * @brief These are all scripts' orders.
 * @author nekith@gmail.com
 * @final
 */

final class strayCommandOrdersScript
{
  /**
   * Create a new script.
   * @param array $params order params
   * @param array $options order options
   * @static
   */
  static public function fCreate(array $params = null, array $options = null)
  {
    if (1 > count($params))
      throw new strayException('wrong syntax for script:create');
    $path = STRAY_PATH_TO_SCRIPTS . $params[0] . '/';
    if (true === is_dir($path))
      throw new strayExceptionError('script ' . $params[0] . ' already exists');
    // create directory
    if (false === mkdir($path))
      throw new strayExceptionFatal('can\'t mkdir');
    // create xxx.script.php
    if (false === touch($path . $params[0] . '.php'))
      throw new strayExceptionFatal('can\'t touch (this!)');
    $file = fopen($path . $params[0] . '.php', 'w+');
    if (false === fwrite($file, "<?php\n\nfunction script" . ucfirst($params[0])
        . '(array $params = null, array $options = null)' . "\n{\n}"))
      throw new strayExceptionFatal('can\'t write in ' . $path . $params[0] . '.php');
    fclose($file);
    // end
    echo 'Script ' . $params[0] . " created !\n";
  }

  /**
   * Execute a script.
   * @param array $params order params
   * @param array $options order options
   * @static
   */
  static public function fExec(array $params = null, array $options = null)
  {
    if (0 == count($params))
      throw new strayException('wrong syntax for script:exec');
    $path = STRAY_PATH_TO_SCRIPTS . $params[0] . '/';
    if (false === is_dir($path))
      throw new strayExceptionError('can\'t find script ' . $params[0]);
    require $path . $params[0] . '.php';
    $fct = 'script' . ucfirst($params[0]);
    array_shift($params);
    $fct($params, $options);
  }

  /**
   * Private constructor.
   */
  private function __construct() {}
}
