<?php
/**
 * @brief Translation function.
 * @author nekith@gmail.com
 * @param string $key key for translated
 * @param array $args args values (to be replaced in the trad string)
 * @return string trad string
 */
function strayfTr($key, array $args = null)
{
  $tr = strayI18n::fGetInstance();
  $string = $tr->$key;
  if (true === is_array($args))
    $string = vsprintf($string, $args);
  return $string;
}
