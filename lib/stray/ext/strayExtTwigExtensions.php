<?php
/**
 * stray extensions for Twig.
 * @author nekith@gmail.com
 */

/**
 * Get nice url for specified $url.
 * @param string $url url
 * @return string nice url
 */
function strayExtTwigUrl($url)
{
  $request = strayRoutingBootstrap::fGetInstance()->GetRequest();
  $nice = '/' . ltrim($url, '/');
  if (true === $request->IsDebug())
    $nice = '/dev.php/' . ltrim($nice, '/');
  return $nice;
}

/**
 * Get nice url for specified $route.
 * @param string $route route name
 * @param array $args route args
 * @return string nice url
 */
function strayExtTwigRoute($route, $args = array())
{
  return null;
}
