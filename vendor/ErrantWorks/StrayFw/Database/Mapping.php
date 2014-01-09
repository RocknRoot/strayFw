<?php

namespace ErrantWorks\StrayFw\Database;

use ErrantWorks\StrayFw\Exception\InvalidDirectory;
use ErrantWorks\StrayFw\Exception\FileNotReadable;

/**
 * Mapping representation class.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Mapping
{
    /**
     * Registered mappings.
     *
     * @static
     * @var array[]
     */
    protected static $mappings = array();

    /**
     * Register a new mapping.
     *
     * @static
     * @throws InvalidDirectory if directory $dir can't be indentified
     * @throws InvalidDirectory if directory $modelsDir can't be indentified
     * @throws InvalidDirectory if directory $modelsBaseDir can't be indentified
     * @throws FileNotReadable  if file $configFile is not readable
     * @param  string           $dir           application root directory
     * @param  string           $configFile    mapping configuration file
     * @param  string           $modelsDir     user models directory
     * @param  string           $modelsBaseDir generated models directory
     */
    public static function registerMapping($dir, $configFile, $modelsDir, $modelsBaseDir)
    {
        if (is_dir($dir) === false) {
            throw new InvalidDirectory('directory "' . $dir . '" can\'t be identified');
        }
        if (is_dir($dir . DIRECTORY_SEPARATOR . $modelsDir) === false) {
            throw new InvalidDirectory('directory "' . $dir . DIRECTORY_SEPARATOR . $modelsDir . '" can\'t be identified');
        }
        if (is_dir($dir . DIRECTORY_SEPARATOR . $modelsBaseDir) === false) {
            throw new InvalidDirectory('directory "' . $dir . DIRECTORY_SEPARATOR . $modelsBaseDir . '" can\'t be identified');
        }
        if (is_readable($dir . DIRECTORY_SEPARATOR . $configFile) === false) {
            throw new FileNotReadable('file "' . $dir . DIRECTORY_SEPARATOR . $configFile . '" isn\'t readable');
        }
        self::$mappings[] =  array(
            'dir' => $dir,
            'file' => $configFile,
            'modelsDir' => $modelsDir,
            'modelsBaseDir' => $modelsBaseDir
        );
    }
}
