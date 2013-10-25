<?php

namespace ErrantWorks\StrayFw\Locale;

use ErrantWorks\StrayFw\Locale\Locale;

/**
 * Date localization class.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Date
{
    const FORMAT_NONE = \IntlDateFormatter::NONE;
    const FORMAT_LONG = \IntlDateFormatter::LONG;
    const FORMAT_SHORT = \IntlDateFormatter::SHORT;

    /**
     * Intl date formatter instance.
     *
     * @var \IntlDateFormatter
     */
    protected $dateFormatter;

    /**
     * Initialize with date and time formats.
     * @param int $date date format
     * @param int $time time format
     */
    public function __construct($date = self::FORMAT_NONE, $time = self::FORMAT_NONE)
    {
        $this->dateFormatter = \IntlDateFormatter::create(Locale::getCurrentLanguage(), $date, $time);
    }

    /**
     * Set a custom format.
     *
     * @param  string $format new pattern to use
     * @return bool   true on success
     */
    public function setPattern($format)
    {
        return $this->dateFormatter->setPattern($format);
    }

    /**
     * Formats the time value as a localized string.
     *
     * @param  mixed  $time value to format
     * @return string formatted string
     */
    public function format($time)
    {
        return $this->dateFormatter->format($time);
    }
}
