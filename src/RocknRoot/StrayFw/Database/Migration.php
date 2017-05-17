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
            if ($this->write($mapping, $mappingName, $name, '', '') === true) {
                $path = rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                $path .= $name . DIRECTORY_SEPARATOR . 'schema.yml';
                if (copy($mapping['schema'], $path) === false) {
                    throw new FileNotWritable('can\'t copy "' . $mapping['schema'] . '" to "' . $path . '"');
                }
                echo 'Migration "' . $name . '" created.' . PHP_EOL;
            }
        }
    }

    public function generate(Request $req)
    {
        if (count($req->getArgs()) != 2) {
            echo 'Wrong arguments.' . PHP_EOL . 'Usage : db/migration/create mapping_name migration_name' . PHP_EOL;
        } else {
            $mappingName = $req->getArgs()[0];
            $mapping = Mapping::get($mappingName);
            $name = ucfirst($req->getArgs()[1]);
        }
    }

    public function migrate(Request $req)
    {
        echo 'Not implemented yet.' . PHP_EOL;
    }

    public function rollback(Request $req)
    {
        echo 'Not implemented yet.' . PHP_EOL;
    }

    private function write(array $mapping, string $mappingName, string $name, string $up, string $down)
    {
        $path = rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $path .= $name . DIRECTORY_SEPARATOR;
        if (file_exists($path . $name . '.php') === true) {
            echo 'A migration with this name already exists. Do you want to overwrite it ? [y/n] : ';
            if (fgetc(STDIN) != 'y') {
                return false;
            }
        }
        if (is_dir($path) === false) {
            if (mkdir($path) === false) {
                throw new FileNotWritable('can\'t mkdir "' . $path . '"');
            }
        }
        $path .= $name . '.php';
        $file = fopen($path, 'w+');
        if ($file === false) {
            throw new FileNotWritable('can\'t open "' . $path . '" with write permission');
        }
        $content = "<?php\n\nnamespace " . ltrim(rtrim($mapping['config']['migrations']['namespace'], '\\'), '\\') . '\\' . $name . ";\n\nusing " . ltrim(rtrim($mapping['config']['provider'], '\\'), '\\') . '\\Migration;' . PHP_EOL;
        $content .= "\nclass " . $name . " extends Migration\n{\n";
        $content .= '    const NAME = \'' . $name . "';\n    const MAPPING = '" . $mappingName . "';\n\n";
        $content .= "    public function up()\n    {\n" . $up . "    }\n\n";
        $content .= "    public function down()\n    {\n" . $down . "    }\n";
        $content .= "}";
        if (fwrite($file, $content) === false) {
            throw new FileNotWritable('can\'t write in "' . $path . '"');
        }
        fclose($file);

        return true;
    }
}
