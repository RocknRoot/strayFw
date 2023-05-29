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
    public function build(Request $request): void
    {
        $mapping = $request->getArgs()[0];
        if (\count($request->getArgs()) != 1 || !\is_string($mapping)) {
            echo 'Wrong arguments.' . PHP_EOL . 'Usage : db/build mapping_name' . PHP_EOL;
        } else {
            echo 'Are you sure you want to delete all existing tables and data for mapping "' . $mapping . '" ? [y/n] : ';
            if (\fgetc(STDIN) == 'y') {
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
    public function mappings(Request $request): void
    {
        $table = new \cli\Table();
        $table->setHeaders([ 'Mapping', 'Database', 'Models path' ]);
        $rows = [];
        $mappings = Mapping::getMappings();
        \usort($mappings, fn (array $a, array $b): int => \strcmp($a['config']['name'], $b['config']['name']));
        foreach ($mappings as $mapping) {
            $rows[] = [
                $mapping['config']['name'],
                $mapping['config']['database'],
                $mapping['config']['models']['path'],
            ];
        }
        $table->setRows($rows);
        $table->display();
    }

    /**
     * Generate base models for specified mapping.
     *
     * @param Request $request current CLI request
     */
    public function generate(Request $request): void
    {
        $mapping = $request->getArgs()[0];
        if (\count($request->getArgs()) != 1 || !\is_string($mapping)) {
            echo 'Wrong arguments.' . PHP_EOL . 'Usage : db/generate mapping_name' . PHP_EOL;
        } else {
            $schema = Schema::getSchema($mapping);
            $schema->generateModels();
        }
    }
}
