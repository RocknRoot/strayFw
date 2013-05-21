<?php

if (false === function_exists('getallheaders'))
{
  function getallheaders()
  {
    $headers = null;
    foreach ($_SERVER as $name => $value)
      if ('HTTP_' == substr($name, 0, 5))
        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
    return $headers;
  }
}