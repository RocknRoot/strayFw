<?php

namespace RocknRoot\StrayFw\Database;

use RocknRoot\StrayFw\Config;
use RocknRoot\StrayFw\Console\Request;
use RocknRoot\StrayFw\Exception\FileNotReadable;
use RocknRoot\StrayFw\Exception\FileNotWritable;
use RuntimeException;

/**
 * Console actions for migration related operations.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Migration
{
    /**
     * Create a new migration.
     *
     * @param Request $req current CLI request
     * @throws FileNotReadable if can't find schema file
     * @throws FileNotWritable if can't copy schema file
     */
    public function create(Request $req) : void
    {
        if (count($req->getArgs()) != 2) {
            echo 'Wrong arguments.' . PHP_EOL . 'Usage : db/migration/create mapping_name migration_name' . PHP_EOL;
        } else {
            $mappingName = $req->getArgs()[0];
            $mapping = Mapping::get($mappingName);
            $name = ucfirst($req->getArgs()[1]);
            if ($this->write($mapping, $mappingName, $name) === true) {
                $path = rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                $path .= $name . DIRECTORY_SEPARATOR . 'schema.yml';
                if (file_exists($mapping['config']['schema']) === false) {
                    throw new FileNotReadable('can\'t find "' . $mapping['schema'] . '"');
                }
                if (copy($mapping['config']['schema'], $path) === false) {
                    throw new FileNotWritable('can\'t copy "' . $mapping['schema'] . '" to "' . $path . '"');
                }
                $migrations = [];
                $path = rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'migrations.yml';
                if (file_exists($path) === true) {
                    $migrations = Config::get($path);
                }
                $migrations[] = [
                    'name' => $name,
                    'timestamp' => time(),
                ];
                Config::set($path, $migrations);
                echo 'Migration "' . $name . '" created.' . PHP_EOL;
            }
        }
    }

    /**
     * Generate code for migration.
     *
     * @param Request $req current CLI request
     * @throws FileNotReadable if can't find migrate
     */
    public function generate(Request $req) : void
    {
        if (count($req->getArgs()) != 2) {
            echo 'Wrong arguments.' . PHP_EOL . 'Usage : db/migration/create mapping_name migration_name' . PHP_EOL;
        } else {
            $mappingName = $req->getArgs()[0];
            $mapping = Mapping::get($mappingName);
            $name = ucfirst($req->getArgs()[1]);
            $path = rtrim($mapping['config']['migrations']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $path .= $name . DIRECTORY_SEPARATOR . $name . '.php';
            if (file_exists($path) === false) {
                throw new FileNotReadable('can\'t find migration at "' . $path . '"');
            }
            $cl = '\\' . ltrim(rtrim($mapping['config']['provider'], '\\'), '\\') . '\\Migration::generate';
            if (is_callable($cl) === false) {
                throw new RuntimeException(
                    'Migration generate method is not callable on configured provider!'
                );
            }
            $res = call_user_func($cl, $mapping, $mappingName, $name);
            $this->write($mapping, $mappingName, $name, $res['up'], $res['down'], $res['import']);
            echo 'Migration "' . $name . '" generated.' . PHP_EOL;
            echo 'This is an automatic generation, please validate or rewrite parts of the migration.' . PHP_EOL;
            echo 'File is there:' . PHP_EOL;
            echo $path . PHP_EOL;
        }
    }

    /**
     * Run migration code for a mapping.
     *
     * @param Request $req current CLI request
     */
    public function migrate(Request $req) : void
    {
        if (count($req->getArgs()) != 1) {
            echo 'Wrong arguments.' . PHP_EOL . 'Usage : db/migration/migrate mapping_name' . PHP_EOL;
        } else {
            $mappingName = $req->getArgs()[0];
            $mapping = Mapping::get($mappingName);
            $cl = '\\' . ltrim(rtrim($mapping['config']['provider'], '\\'), '\\') . '\\Migration::migrate';
            if (is_callable($cl) === false) {
                throw new RuntimeException(
                    'Migration migrate method is not callable on configured provider!'
                );
            }
            call_user_func($cl, $mapping);
            echo 'Migrate - Done' . PHP_EOL;
        }
    }

    /**
     * Rollback last migration for a mapping.
     *
     * @param Request $req current CLI request
     */
    public function rollback(Request $req) : void
    {
        if (count($req->getArgs()) != 1) {
            echo 'Wrong arguments.' . PHP_EOL . 'Usage : db/migration/rollback mapping_name' . PHP_EOL;
        } else {
            $mappingName = $req->getArgs()[0];
            $mapping = Mapping::get($mappingName);
            $cl = '\\' . ltrim(rtrim($mapping['config']['provider'], '\\'), '\\') . '\\Migration::rollback';
            if (is_callable($cl) === false) {
                throw new RuntimeException(
                    'Migration rollback method is not callable on configured provider!'
                );
            }
            call_user_func($cl, $mapping);
            echo 'Rollback - Done' . PHP_EOL;
        }
    }

    /**
     * Write migration code to file.
     *
     * @param  array           $mapping     mapping definition
     * @param  string          $mappingName mapping name
     * @param  string          $name        migration name
     * @param  array           $up          up code
     * @param  array           $down        down code
     * @param  array           $import      used classes in migration code
     * @return bool true if successful
     * @throws FileNotWritable if can't mkdir
     * @throws FileNotWritable if can't open file with write permission
     * @throws FileNotWritable if can't write to file
     */
    private function write(array $mapping, string $mappingName, string $name, array $up = [], array $down = [], array $import = []) : bool
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
        $content = "<?php\n\nnamespace " . ltrim(rtrim($mapping['config']['migrations']['namespace'], '\\'), '\\') . '\\' . $name . ";\n\nuse " . ltrim(rtrim($mapping['config']['provider'], '\\'), '\\') . '\\Migration;' . PHP_EOL;
        if (count($import) >= 1) {
            $content .= 'use ' . ltrim(rtrim($mapping['config']['provider'], '\\'), '\\') . '\\Mutation\\{';
            $content .= implode(', ', $import) . "};\n";
        }
        $up = implode('', array_map(function (string $a) {
            return '        ' . $a . '->execute();' . PHP_EOL;
        }, $up));
        $down = implode('', array_map(function (string $a) {
            return '        ' . $a . '->execute();' . PHP_EOL;
        }, $down));
        var_dump($up);
        $content .= "\nclass " . $name . " extends Migration\n{\n";
        $content .= '    const NAME = \'' . $name . "';\n\n";
        $content .= "    public function getMappingName() : string\n    {\n        return '" . $mappingName . "';\n    }\n\n";
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
