#!/usr/bin/env php
<?php
/**
 * @brief Start of the command line.
 * @author nekith@gmail.com
 */

define('STRAY_PATH_TO_APPS', '../apps/');
define('STRAY_PATH_TO_MODELS', '../models/');
define('STRAY_PATH_TO_LIB', '../');
define('STRAY_PATH_TO_PLUGINS', '../plugins/');
define('STRAY_PATH_TO_WEB', '../web/');
define('STRAY_PATH_TO_INSTALL', '../');

require 'global/require.php';
require 'persistance/norel/require.php';
require 'persistance/strayLog.class.php';
require 'config/require.php';
require 'exception/require.php';
require 'models/require.php';
require 'models/mod/require.php';
require 'models/mutation/require.php';
require 'models/query/require.php';
require 'locale/require.php';
require 'plugins/strayITool.class.php';
require 'command/require.php';

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
