<?php

define('STRAY_PATH_ROOT', __DIR__ . '/../../');
define('STRAY_PATH_APPS', __DIR__ . '/../../apps/');
define('STRAY_PATH_VENDOR', __DIR__ . '/../../vendor/');

define('STRAY_ENV', 'development');

require STRAY_PATH_VENDOR . 'autoload.php';

use RocknRoot\StrayFw\Bootstrap;

Bootstrap::init();
