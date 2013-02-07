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
  return strayRouting::fGenerateNiceUrlForRoute($route, $args);
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
