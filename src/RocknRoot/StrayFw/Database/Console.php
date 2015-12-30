<?php

namespace RocknRoot\StrayFw\Database;

use RocknRoot\StrayFw\Console\Request;
use RocknRoot\StrayFw\Database\Provider\Schema;

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
    public function build(Request $request)
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
    public function mappings(Request $request)
    {
        $mappings = Mapping::getMappings();
        echo 'mapping - database - models path' . PHP_EOL;
        foreach ($mappings as $mapping) {
            echo $mapping['config']['name'] . ' - ' . $mapping['config']['database'] . ' - ' . $mapping['config']['models']['path'] . PHP_EOL;
        }
    }

    /**
     * Generate base models for specified mapping.
     *
     * @param Request $request current CLI request
     */
    public function generate(Request $request)
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
