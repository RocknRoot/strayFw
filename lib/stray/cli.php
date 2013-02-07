#!/usr/bin/env php
<?php

define('STRAY_VERSION', '0.2');
define('STRAY_VERSION_CODE', 'The Butcher');

$base = rtrim(dirname(__FILE__), '/') . '/';
define('STRAY_PATH_TO_APPS', $base . '../../apps/');
define('STRAY_PATH_TO_MODELS', $base . '../../models/');
define('STRAY_PATH_TO_LIB', $base . '../');
define('STRAY_PATH_TO_SCRIPTS', $base . '../../scripts/');
define('STRAY_PATH_TO_WEB', $base . '../../web/');
define('STRAY_PATH_TO_INSTALL', $base . '../../');

define('STRAY_ENV', 'cli');

require 'global/require.php';
require 'exception/require.php';
require 'config/require.php';
require 'persistance/strayLog.class.php';
require 'models/require.php';
require 'models/mod/require.php';
require 'models/mutation/require.php';
require 'models/query/require.php';
require 'locale/require.php';
require 'command/require.php';
require 'apps/strayAppsWidgetAScripts.class.php';

echo 'Welcome to the Amazing Stray CLI !' . PHP_EOL . PHP_EOL;
strayConfigInstall::fGetInstance();
$command = new strayCommand();
try
{
  $command->Parse();
  $command->Run();
}
catch (strayException $e)
{
  echo $e->Display() . PHP_EOL;
}
catch (Exception $e)
{
  echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
