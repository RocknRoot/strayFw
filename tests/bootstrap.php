<?php

$base = rtrim(dirname(__FILE__), '/') . '/';

define('STRAY_PATH_TO_APPS', $base . '../apps/');
define('STRAY_PATH_TO_MODELS', $base . '../models/');
define('STRAY_PATH_TO_LIB', $base . '../lib/');
define('STRAY_PATH_TO_SCRIPTS', $base . '../scripts/');
define('STRAY_PATH_TO_WEB', $base . '../web/');
define('STRAY_PATH_TO_INSTALL', $base . '../');

// stray
$straypath = STRAY_PATH_TO_LIB . 'stray/';
require $straypath . 'global/require.php';
require $straypath . 'exception/require.php';
require $straypath . 'config/require.php';
require $straypath . 'persistance/require.php';
require $straypath . 'models/require.php';
require $straypath . 'models/mod/require.php';
require $straypath . 'models/mutation/require.php';
require $straypath . 'models/query/require.php';
require $straypath . 'routing/require.php';
require $straypath . 'routing/strayRoutingBootstrap.class.php';
require $straypath . 'locale/require.php';
require $straypath . 'command/require.php';
require $straypath . 'apps/require.php';
require $straypath . 'form/require.php';
require $straypath . 'ext/require.php';

// plugins
require STRAY_PATH_TO_LIB . 'plugins/strayRegistry/require.php';
