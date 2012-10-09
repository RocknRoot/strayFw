<?php

if (isset($_SERVER['REMOTE_ADDR']) === false
  || in_array($_SERVER['REMOTE_ADDR'], array(
    '127.0.0.1',
    '::1')) === false)
{
  header('HTTP/1.0 403 Forbidden');
  exit('You are not allowed to access this.');
}

define('STRAY_PATH_TO_APPS', '../apps/');
define('STRAY_PATH_TO_MODELS', '../models/');
define('STRAY_PATH_TO_LIB', '../lib/');
define('STRAY_PATH_TO_WEB', '');
define('STRAY_PATH_TO_INSTALL', '../');

// vendors
require STRAY_PATH_TO_LIB . 'vendor/php_error.php';
\php_error\reportErrors();

// stray
$straypath = STRAY_PATH_TO_LIB . 'stray/';
require $straypath . 'global/require.php';
require $straypath . 'exception/require.php';
require $straypath . 'config/require.php';
require $straypath . 'persistance/require.php';
require $straypath . 'models/require.php';
require $straypath . 'models/query/require.php';
require $straypath . 'routing/require.php';
require $straypath . 'routing/strayRoutingBootstrapDev.class.php';
require $straypath . 'locale/require.php';
require $straypath . 'apps/require.php';
//require $straypath . 'form/require.php';
require $straypath . 'ext/require.php';

// run
strayRouting::fGetInstance()->SetHost($_SERVER['SERVER_NAME']);
$url = (empty($_SERVER['HTTPS']) === false && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
$url .= $_SERVER['SERVER_NAME'] . str_replace('/dev.php', null, $_SERVER['REQUEST_URI']);
strayRoutingBootstrapDev::fGetInstance()->Run($url, $_SERVER['REQUEST_METHOD']);
