<?php
/**
 * Singleton
 * @brief Twig vendor library wrapper.
 * @author nekith@gmail.com
 */
class strayExtTwig extends strayASingleton
{
  /**
   * Array of Twig_Environment.
   * @var array
   */
  private $_envs;
  /**
   * Twig debug mode.
   * @var bool
   */
  public $debug;

  /**
   * Init Twig.
   */
  public function Init()
  {
    $this->debug = false;
    require_once STRAY_PATH_TO_LIB . 'vendor/Twig/Autoloader.php';
    Twig_Autoloader::register();
  }

  /**
   * Get environment for specified templates dir.
   * @params string $dir templates dir
   * @return Twig_Environment env
   */
  public function GetEnvironment($dir)
  {
    if (true === isset($this->_envs[$dir]))
      return $this->_envs[$dir];
    $dir = rtrim($dir, '/') . '/';
    if (true === is_dir($dir))
    {
      $config = strayConfigInstall::fGetInstance()->GetConfig();
      $tmp = rtrim($config['tmp'], '/');
      if ('/' != $tmp[0])
        $tmp = realpath(STRAY_PATH_TO_INSTALL . $tmp);
      if (false === is_dir($tmp . '/twig_compil/'))
        if (false === mkdir($tmp . '/twig_compil'))
          throw new strayExceptionError('can\'t mkdir ' . $tmp . '/twig_compil');
      $loader = new Twig_Loader_Filesystem($dir);
      $this->_envs[$dir] = new Twig_Environment($loader, array(
          'cache' => $tmp . '/twig_compil',
          'debug' => $this->debug
        ));
      return $this->_envs[$dir];
    }
    return null;
  }

  /**
   * Load a template and return template result.
   * @params Twig_Environment $env Twig env
   * @params string $template template path
   * @params array $args template args
   * @return string render result
   */
  public function LoadTemplate(Twig_Environment $env, $template, $args = array())
  {
    $template = $env->loadTemplate($template);
    return $template->render($args);
  }

  /**
   * Protected constructor.
   */
  protected function __construct()
  {
    $this->_envs = array();
  }
}
