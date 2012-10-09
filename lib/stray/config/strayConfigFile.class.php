<?php
/**
 * @abstract
 * @brief Config file wrapper.
 * @author nekith@gmail.com
 */

abstract class strayConfigFile
{
  const EXTENSION = 'yml';

  /**
   * Parse specified file.
   * @param string $path file to parse
   * @param string $extension file extension
   * @return false if file doesn't exist
   * @return stdClass data
   * @static
   */ 
  static public function fParse($path, $extension = self::EXTENSION)
  {
    return yaml_parse_file($path . '.' . $extension);
  }

  /**
   * Encode content for future config file.
   * @param mixed $content content
   * @return mixed return of encoding function
   * @static
   */
  static public function fEncode($content)
  {
    return yaml_emit($content, YAML_UTF8_ENCODING);
  }

  /**
   * Create and perhaps truncate config file.
   * @param string $path file to create
   * @param mixed $content first content
   * @param string $extension file extension
   * @return bool true if succeeded
   * @static
   */
  static public function fCreate($path, $content = null, $extension = self::EXTENSION)
  {
    $file = fopen($path . '.' . $extension, 'w+');
    if (false === $file)
      return false;
    if (false === fwrite($file, $content))
      return false;
    return fclose($file);
  }
}
