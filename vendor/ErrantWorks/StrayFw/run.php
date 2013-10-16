<?php

require 'Bootstrap.php';

use ErrantWorks\StrayFw\Bootstrap;

Bootstrap::init();

Bootstrap::registerLib('Symfony\\Component\\Yaml');
require STRAY_PATH_VENDOR . 'Twig/Twig/Autoloader.php';
\Twig_Autoloader::register();

if (STRAY_ENV === 'development') {
    Bootstrap::registerLib('Whoops', STRAY_PATH_VENDOR . 'Filp/Whoops');
}

require STRAY_PATH_ROOT . 'init.php';

Bootstrap::run();
