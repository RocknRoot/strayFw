<?php

namespace RocknRoot\StrayFw\Database\Provider;

use RocknRoot\StrayFw\Exception\BadUse;

/**
 * Enum representation parent class for all providers.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Enum
{
    /**
     * Keys and values as an associative array.
     *
     * @var array
     */
    protected static $array;

    /**
     * Enum value.
     *
     * @var string
     */
    protected $value;

    /**
     * Creates a new enum with specified value.
     *
     * @param string $v
     *
     * @throws BadUse if $v is not recognized as a possible value
     */
    public function __construct($v)
    {
        if (static::isValid($v) === false) {
            throw new BadUse('"' . $v . '" is not recognized as a possible value');
        }
        $this->value = $v;
    }

    /**
     * Get current enum value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Return current enum value.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Set current enum value.
     *
     * @param string $v new value
     * @return string
     */
    public function setValue($v)
    {
        if (static::isValid($v) === false) {
            throw new BadUse('"' . $v . '" is not recognized as a possible value');
        }
        $this->value = $v;
    }

    /**
     * Return keys and values as an associative array.
     *
     * @return array keys => values
     */
    public static function toArray()
    {
        if (static::$array == null) {
            $ref = new \ReflectionClass(static::class);
            $consts = $ref->getConstants();
            static::$array = array();
            foreach ($consts as $key => $value) {
                if (stripos($key, 'VALUE_') === 0) {
                    static::$array[$key] = $value;
                }
            }
        }
        return static::$array;
    }

    /**
     * Check if a value is a possible value for this enum.
     *
     * @param string $value
     * @return bool true if value is recognized
     */
    public static function isValid($value)
    {
        return in_array($value, static::toArray());
    }
}
