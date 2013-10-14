<?php

require 'Bootstrap.php';

use ErrantWorks\StrayFw\Bootstrap;

Bootstrap::init();

Bootstrap::registerLib('ErrantWorks\\StrayFw');
Bootstrap::registerLib('Symfony\\Component\\Yaml');
Bootstrap::registerLib('Twig_', STRAY_PATH_VENDOR . 'Twig/Twig');

if (STRAY_ENV === 'development') {
    Bootstrap::registerLib('Whoops', STRAY_PATH_VENDOR . 'Filp/Whoops');
}

require STRAY_PATH_ROOT . 'init.php';

Bootstrap::run();
