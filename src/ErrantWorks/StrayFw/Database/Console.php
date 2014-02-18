<?php

namespace ErrantWorks\StrayFw\Database;

use ErrantWorks\StrayFw\Console\Request;
use ErrantWorks\StrayFw\Database\Mapping;
use ErrantWorks\StrayFw\Database\Provider\Schema;

/**
 * Console actions for Database namespace.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Console
{
    /**
     * Build data structures.
     *
     * @param Request $request current CLI request
     */
    public function buildAction(Request $request)
    {
        if (count($request->getArgs()) != 1) {
            echo 'Wrong parameters.' . PHP_EOL . 'Usage : db/build mapping_name' . PHP_EOL;
        } else {
            $mapping = $request->getArgs()[0];
            echo 'Are you sure you want to delete all existing tables and data for mapping "' . $mapping . '" ? [y/n] : ';
            if (fgetc(STDIN) == 'y') {
                $schema = Schema::getSchema($mapping);
                $schema->build();
            }
        }
    }

    /**
     * List registered mappings.
     *
     * @param Request $request current CLI request
     */
    public function listAction(Request $request)
    {
        $mappings = Mapping::getMappings();
        echo 'mapping - database - mapping configuration file path' . PHP_EOL;
        foreach ($mappings as $mapping) {
            echo $mapping['config']['name'] . ' - ' . $mapping['config']['database'] . ' - ' . rtrim($mapping['dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($mapping['file'], DIRECTORY_SEPARATOR) . PHP_EOL;
        }
    }

    /**
     * Generate base models for specified mapping.
     *
     * @param Request $request current CLI request
     */
    public function generateAction(Request $request)
    {
        if (count($request->getArgs()) != 1) {
            echo 'Wrong parameters.' . PHP_EOL . 'Usage : db/model/generate mapping_name' . PHP_EOL;
        } else {
            $mapping = $request->getArgs()[0];
            $schema = Schema::getSchema($mapping);
            $schema->generateModels();
        }
    }
}
