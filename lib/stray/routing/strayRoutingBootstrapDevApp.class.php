<?php
/**
 * Singleton.
 * @brief Bootstrapping stuff for _stray app in development environment.
 * @author nekith@gmail.com
 */

class strayRoutingBootstrapDevApp extends strayASingleton
{
  /**
   * Run the _stray app and display output.
   * @param string $url requested url
   * @param string $mthod HTTP method
   */
  public function Run($url, $method)
  {
    $path = parse_url($url, PHP_URL_PATH);
    $sections = explode('/', $path);
    array_shift($sections);
    strayExtTwig::fGetInstance()->Init();
    if ('profiler' == $sections[1])
    {
      if (true === isset($sections[2]) && 'last' == $sections[2])
        echo strayProfiler::fGetInstance()->GetLastLog();
      elseif (true === isset($sections[2]) && true === is_numeric($sections[2]))
        strayProfiler::fGetInstance()->RenderLog($sections[2]);
      else
        strayProfiler::fGetInstance()->RenderLogList();
    }
  }

  /**
   * Check if the current requested URL is _stray app related.
   * @param string $url requested URL
   * @return bool true if it's a _stray app request
   */
  public function IsItForMe($url)
  {
    $path = parse_url($url, PHP_URL_PATH);
    $sections = explode('/', $path);
    array_shift($sections);
    return 2 <= count($sections) && '_stray' == $sections[0];
  }

  protected function __construct()
  {}
}
