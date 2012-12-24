<?php
/**
 * stray extensions for Twig.
 * @author nekith@gmail.com
 */

/**
 * Get nice url for specified $route.
 * @param string $route route name
 * @param array $args route args
 * @return string nice url
 */
function strayExtTwigRoute($route, $args = array())
{
  $app = strayRoutingBootstrap::fGetInstance()->GetRequest()->app;
  $url = null;
  if (false !== strpos($route, '.'))
  {
    list($app, $route) = explode('.', $route);
    $routes = strayConfigInstall::fGetInstance()->GetRoutes();
    $url = $routes['routes'][$app]['subdomain'] . '.';
  }
  $routes = strayConfigApp::fGetInstance($app)->GetRoutes();
  if (null == $routes)
  {
    strayLog::fGetInstance()->Error('can\'t find app "' . $app . '" for route view helper');
    return null;
  }
  if (false === isset($routes['routes'][$route]))
  {
    strayLog::fGetInstance()->Error('can\'t find route "' . $route . '" for route view helper');
    return null;
  }
  $url .= $routes['routes'][$route]['url'];
  foreach ($args as $name => $value)
    $url = preg_replace('/\(\?<' . $name . '>(.*?)\)/', $value, $url);
  // clear optional parts
  $url = preg_replace('/\((.*?)\(\?<(\w)+>(.*?)\)(.*?)\)\?/', null, $url);
  $url = str_replace([ '(', ')', '?' ], null, $url);
  return strayExtTwigUrl($url);
}

/**
 * Simple proxy to strayTr.
 * @param string $key key for translated
 * @param array $args args values (to be replaced in the trad string)
 * @return string trad string
 */
function strayExtTwigTr($key, array $args = null)
{
  return strayfTr($key, $args);
}

/**
 * Get nice url for specified $url.
 * @param string $url url
 * @return string nice url
 */
function strayExtTwigUrl($url)
{
  return strayRouting::fGenerateNiceUrl($url);
}
