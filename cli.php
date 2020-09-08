<?php

define('STRAY_PATH_ROOT', __DIR__ . '/');
define('STRAY_PATH_APPS', __DIR__ . '/apps/');
define('STRAY_PATH_VENDOR', __DIR__ . '/vendor/');

define('STRAY_IS_CLI', true);

define('STRAY_ENV', 'production');

require STRAY_PATH_VENDOR . 'autoload.php';

use RocknRoot\StrayFw\Bootstrap;

Bootstrap::init();

require STRAY_PATH_ROOT . 'init.php';

Bootstrap::run();
