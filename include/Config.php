<?php

/**
 * Class Config
 */
class Config
{
    /**
     * Configuration data
     *
     * @var null
     */
    private static $config = null;

    /**
     * Get config item
     *
     * @param string $key     Key to retrieve
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public static function getConfig($key, $default = null)
    {
        if (null === self::$config) {
            self::$config = require __DIR__ . '/../config/contact.config.php';
        }

        return isset(self::$config[$key]) ? self::$config[$key] : $default;
    }
}
