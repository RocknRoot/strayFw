<?php
/**
 * @brief Date localization class.
 * @author nekith@gmail.com
 */

class strayDate
{
  const FORMAT_NONE = 0;
  const FORMAT_LONG = 1;
  const FORMAT_SHORT = 2;

  /**
   * Date formatter.
   * @var IntlDateFormatter
   */
  protected $_dateFormatter;

  /**
   * Builds a strayDate object with presetted options.
   * Long date and long time.
   * @static
   * @param string
   * @return string localized date
   */
  static public function fLongLong($timestamp)
  {
    $instance = new strayDate(self::FORMAT_LONG, self::FORMAT_LONG);
    return $instance->Format($timestamp);
  }

  /**
   * Builds a strayDate object with presetted options.
   * Short date and short time.
   * @static
   * @param string
   * @return string localized date
   */
  static public function fShortShort($timestamp)
  {
    $instance = new strayDate(self::FORMAT_SHORT, self::FORMAT_SHORT);
    return $instance->Format($timestamp);
  }

  /**
   * Builds a strayDate object with presetted options.
   * Long date and no time.
   * @static
   * @param string
   * @return string localized date
   */
  static public function fLongNone($timestamp)
  {
    $instance = new strayDate(self::FORMAT_LONG, self::FORMAT_NONE);
    return $instance->Format($timestamp);
  }

  /**
   * Construct with date and time formats.
   * @param int $datetype date format
   * @param int $timetype time format
   */
  public function __construct($datetype = self::FORMAT_NONE, $timetype = self::FORMAT_NONE)
  {
    $this->_dateFormatter = IntlDateFormatter::create(self::_fGetLocale(strayI18n::fGetInstance()->GetLanguage()),
      self::_fConvertType($datetype), self::_fConvertType($timetype));
  }

  /**
   * Set custom format.
   * @param string $format custom format
   * @return true if successful
   */
  public function SetPattern($format)
  {
    return $this->_dateFormatter->setPattern($format);
  }

  /**
   * Format a date and/or time with given timestamp.
   * @param int $timestamp unix timestamp
   */
  public function Format($timestamp)
  {
    return $this->_dateFormatter->format($timestamp);
  }

  /**
   * Get locale string by language.
   * @param string $lang language
   * @return string locale string
   */
  static protected function _fGetLocale($lang)
  {
    if ('fr' == $lang)
      return 'fr_FR';
    if ('en' == $lang)
      return 'en_EN';
    return 'en_EN';
  }

  /**
   * Convert intern date format id to intl one.
   * @param int $type intern date format id
   * @return int intl date format id
   */
  static protected function _fConvertType($type)
  {
    if (self::FORMAT_LONG == $type)
      return IntlDateFormatter::LONG;
    if (self::FORMAT_SHORT == $type)
      return IntlDateFormatter::SHORT;
    return IntlDateFormatter::NONE;
  }
}
