<?php

namespace RocknRoot\StrayFw\Database;

use RocknRoot\StrayFw\Console\Request;

/**
 * Console actions for migration related operations.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Migration
{
    public function create(Request $req)
    {
        if (count($req->getArgs()) != 2) {
            echo 'Wrong arguments.' . PHP_EOL . 'Usage : db/migration/create mapping_name migration_name' . PHP_EOL;
        } else {
            $mappingName = $req->getArgs()[0];
            $mapping = Mapping::get($mappingName);
            $name = ucfirst($req->getArgs()[1]);
            $path = rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $path .= $name . '.php';
            if (file_exists($path) === true) {
                echo 'A migration with this name already exists. Do you want to overwrite it ? [y/n] : ';
                if (fgetc(STDIN) != 'y') {
                    return;
                }
            }
            $file = fopen($path, 'w+');
            if ($file === false) {
                throw new FileNotWritable('can\'t open "' . $path . '" with write permission');
            }
            $content = "<?php\n\nnamespace " . ltrim(rtrim($mapping['config']['migrations']['namespace'], '\\'), '\\') . ";\n\nusing RocknRoot\StrayFw\Database\Provider\Migration;\n";
            $content .= "\nclass " . $name . " extends Migration\n{\n";
            $content .= '    const NAME = \'' . $name . "';\n    const MAPPING = '" . $mappingName . "';\n\n";
            $content .= "    public function up()\n    {\n    }\n\n";
            $content .= "    public function down()\n    {\n    }\n";
            $content .= "}";
            if (fwrite($file, $content) === false) {
                throw new FileNotWritable('can\'t write in "' . $path . '"');
            }
            fclose($file);
            echo 'Migration "' . $name . '" created.' . PHP_EOL;
        }
    }

    public function generate(Request $req)
    {
        echo 'Not implemented yet.' . PHP_EOL;
    }

    public function migrate(Request $req)
    {
        echo 'Not implemented yet.' . PHP_EOL;
    }

    public function rollback(Request $req)
    {
        echo 'Not implemented yet.' . PHP_EOL;
    }
}
