<?php

define('STRAY_PATH_TO_APPS', '../apps/');
define('STRAY_PATH_TO_MODELS', '../models/');
define('STRAY_PATH_TO_LIB', '../lib/');
define('STRAY_PATH_TO_PLUGINS', '../plugins/');
define('STRAY_PATH_TO_WEB', '');
define('STRAY_PATH_TO_INSTALL', '../');

$straypath = STRAY_PATH_TO_LIB . 'stray/';

require $straypath . 'global/require.php';
require $straypath . 'exception/require.php';
require $straypath . 'config/require.php';
require $straypath . 'persistance/require.php';
require $straypath . 'models/require.php';
require $straypath . 'models/query/require.php';
require $straypath . 'routing/require.php';
require $straypath . 'locale/require.php';
require $straypath . 'apps/require.php';
require $straypath . 'form/require.php';
require $straypath . 'plugins/strayPlugins.class.php';

strayRouting::fGetInstance()->SetServerName($_SERVER['SERVER_NAME']);

strayRoutingBootstrap::fGetInstance()->Run($_SERVER['REQUEST_URI']);
