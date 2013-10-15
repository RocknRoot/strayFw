<?php

define('STRAY_VERSION', '0.4');
define('STRAY_VERSION_CODE', 'Marius');

define('STRAY_PATH_ROOT', __DIR__ . '/../../');
define('STRAY_PATH_APPS', __DIR__ . '/../../apps/');
define('STRAY_PATH_VENDOR', __DIR__ . '/../../vendor/');

if (false === defined('STRAY_ENV')) {
    define('STRAY_ENV', (getenv('STRAY_ENV') === 'development' ? 'development' : 'production'));
}

require STRAY_PATH_VENDOR . 'ErrantWorks/StrayFw/run.php';

/*use ErrantWorks\StrayFw\Bootstrap;

Bootstrap::init();

Bootstrap::registerLib('Symfony\\Component\\Yaml');
Bootstrap::registerLib('Twig_', STRAY_PATH_VENDOR . 'Twig/Twig');
Bootstrap::registerLib('Whoops', STRAY_PATH_VENDOR . 'Filp/Whoops');*/
