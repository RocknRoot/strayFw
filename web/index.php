<?php

define('STRAY_VERSION', '0.4');
define('STRAY_VERSION_CODE', 'Marius');

define('STRAY_PATH_ROOT', '../');
define('STRAY_PATH_APPS', '../apps/');
define('STRAY_PATH_VENDOR', '../vendor/');
define('STRAY_PATH_WEB', '');

define('STRAY_IS_CLI', false);

if (false === defined('STRAY_ENV')) {
    define('STRAY_ENV', ('development' === getenv('STRAY_ENV') ? 'development' : 'production'));
}

require STRAY_PATH_VENDOR . 'ErrantWorks/StrayFw/init.php';

// run
strayRouting::fGetInstance()->SetHost($_SERVER['SERVER_NAME']);

$url = (false === empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
$url .= $_SERVER['SERVER_NAME'] . str_replace('/index.php', null, $_SERVER['REQUEST_URI']);

strayRoutingBootstrap::fGetInstance()->Run($url, $_SERVER['REQUEST_METHOD']);
