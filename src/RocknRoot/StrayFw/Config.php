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
     * @var array<string, array<string, mixed>>
     */
    protected static array $files = array();

    /**
     * Get installation settings.
     *
     * @static
     * @return array<string, mixed> file content
     */
    public static function getSettings(): array
    {
        return self::get(\constant('STRAY_PATH_ROOT') . 'settings.yml');
    }

    /**
     * Get a file content. Load it if not already done.
     *
     * @static
     * @param  string               $fileName file name
     * @throws BadUse               if once parsed, file content is not an array
     * @throws FileNotReadable      if file can't be opened
     * @throws FileNotParsable      if file can't be parsed
     * @return array<string, mixed> file content
     */
    public static function get(string $fileName): array
    {
        if (isset(self::$files[$fileName]) === false) {
            if (($content = \file_get_contents($fileName)) === false) {
                throw new FileNotReadable('file "' . $fileName . '" can\'t be read');
            }

            try {
                $content = Yaml::parse($content);
                if (!is_array($content)) {
                    throw new BadUse('once parsed, content of file "' . $fileName . '" is not an array');
                }
                self::$files[$fileName] = $content;
            } catch (ParseException $e) {
                throw new FileNotParsable('file "' . $fileName . '" can\'t be parsed: ' . $e->getMessage());
            }
        }
        return self::$files[$fileName];
    }

    /**
     * Write a file content. Save it internally.
     *
     * @static
     * @param  string               $fileName file name
     * @param  array<string, mixed> $content  file content
     * @throws FileNotWritable      if file can't be written
     */
    public static function set(string $fileName, array $content): void
    {
        try {
            $json = Yaml::dump($content, 2);
            if (\file_put_contents($fileName, $json) === false) {
                throw new FileNotWritable('can\'t write to "' . $fileName . '"');
            }
        } catch (DumpException $e) {
            throw new BadUse('Config::set() content parameter can\'t be dump to YAML');
        }
        self::$files[$fileName] = $content;
    }
}
