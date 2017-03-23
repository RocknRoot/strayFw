<?php

define('STRAY_PATH_ROOT', __DIR__ . '/../');
define('STRAY_PATH_APPS', __DIR__ . '/../apps/');
define('STRAY_PATH_VENDOR', __DIR__ . '/../vendor/');

define('STRAY_IS_HTTP', true);

if (php_sapi_name() == 'cli-server') {
    define('STRAY_ENV', 'development');
} else if (defined('STRAY_ENV') === false) {
    define('STRAY_ENV', (getenv('STRAY_ENV') === 'development' ? 'development' : 'production'));
}

require STRAY_PATH_VENDOR . 'autoload.php';

use RocknRoot\StrayFw\Bootstrap;

Bootstrap::init();

require STRAY_PATH_ROOT . 'init.php';

Bootstrap::run();
