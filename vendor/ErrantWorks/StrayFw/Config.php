<?php

namespace ErrantWorks\StrayFw;

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
     * @var string[]
     */
    protected static $files = array();

    /**
     * Get a file content. Load it if it isn\'t already done.
     *
     * @static
     * @param string $fileName file name
     * @return string file content
     */
    public static function get($fileName)
    {
        if (isset(self::$files[$fileName]) === false) {
            if (($content = file_get_contents($fileName)) === false) {
                throw new FileNotReadable('file "' . $fileName . '" can\'t be read');
            }
            try {
                $content = Yaml::parse($content);
            } catch (ParseException $e) {
                throw new FileNotParsable('file "' . $fileName . '" can\'t be parsed');
            }
            self::$files[$fileName] = $content;
        }
        return self::$files[$fileName];
    }
}
