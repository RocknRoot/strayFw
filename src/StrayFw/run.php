<?php

require 'Bootstrap.php';

use ErrantWorks\StrayFw\Bootstrap;

Bootstrap::init();

require STRAY_PATH_VENDOR . 'Twig/Twig/Autoloader.php';
\Twig_Autoloader::register();

require 'init.php';

require STRAY_PATH_ROOT . 'init.php';

Bootstrap::run();
