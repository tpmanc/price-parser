<?php
/**
 * @author tpmanc <tpxtrime@mail.ru>
 */

namespace Classes;

/**
 * Class Settings - params vault
 */
class Settings {
    public static $settings = [];

    /**
     * Add param to array
     * @param string $key Param key
     * @param string $value Param value
     * @return void
     */
    public static function set($key, $value)
    {
        self::$settings[$key] = $value;
    }

    /**
     * Get param to array
     * @param string $key Param key
     * @return string Param value
     */
    public static function get($key)
    {
        return self::$settings[$key];
    }
}