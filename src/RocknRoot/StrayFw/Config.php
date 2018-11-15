<?php

namespace RocknRoot\StrayFw;

use RocknRoot\StrayFw\Exception\BadUse;
use RocknRoot\StrayFw\Exception\FileNotParsable;
use RocknRoot\StrayFw\Exception\FileNotReadable;
use RocknRoot\StrayFw\Exception\FileNotWritable;
use Symfony\Component\Yaml\Exception\DumpException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * General class for settings, configuration and definition files.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Config
{
    /**
     * Loaded files.
     *
     * @static
     * @var array<string, array>
     */
    protected static $files = array();

    /**
     * Get installation settings.
     *
     * @static
     * @return array file content
     */
    public static function getSettings() : array
    {
        return self::get(constant('STRAY_PATH_ROOT') . 'settings.yml');
    }

    /**
     * Get a file content. Load it if not already done.
     *
     * @static
     * @throws FileNotReadable if file can't be opened
     * @throws FileNotParsable if file can't be parsed
     * @param  string          $fileName file name
     * @return array           file content
     */
    public static function get(string $fileName) : array
    {
        if (isset(self::$files[$fileName]) === false) {
            if (($content = file_get_contents($fileName)) === false) {
                throw new FileNotReadable('file "' . $fileName . '" can\'t be read');
            }
            $content = (string) $content; // re: https://github.com/phpstan/phpstan/issues/647
            try {
                $content = Yaml::parse($content);
            } catch (ParseException $e) {
                throw new FileNotParsable('file "' . $fileName . '" can\'t be parsed');
            }
            self::$files[$fileName] = $content;
        }

        return self::$files[$fileName];
    }

    /**
     * Write a file content. Save it internally.
     *
     * @static
     * @throws FileNotWritable if file can't be written
     * @param  string          $fileName file name
     * @param  array           $content  file content
     */
    public static function set(string $fileName, array $content)
    {
        try {
            $json = Yaml::dump($content, 2);
            if (file_put_contents($fileName, $json) === false) {
                throw new FileNotWritable('can\'t write to "' . $fileName . '"');
            }
        } catch (DumpException $e) {
            throw new BadUse('Config::set() content parameter can\'t be dump to YAML');
        }
        self::$files[$fileName] = $content;
    }
}
