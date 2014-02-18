<?php

use ErrantWorks\StrayFw\Console\Console;

Console::registerRoutes(STRAY_PATH_VENDOR . 'ErrantWorks' . DIRECTORY_SEPARATOR . 'StrayFw' . DIRECTORY_SEPARATOR . 'Console', 'console.yml');
Console::registerRoutes(STRAY_PATH_VENDOR . 'ErrantWorks' . DIRECTORY_SEPARATOR . 'StrayFw' . DIRECTORY_SEPARATOR . 'Database', 'console.yml');
