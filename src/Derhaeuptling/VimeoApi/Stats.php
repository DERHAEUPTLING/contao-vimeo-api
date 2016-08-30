<?php

namespace Derhaeuptling\VimeoApi;

use Contao\Config;

class Stats
{
    const CURRENT_LIMIT = 'currentLimit';
    const TOTAL_LIMIT = 'totalLimit';
    const LIMIT_RESET_TIME = 'limitResetTime';

    /**
     * @var string
     */
    protected static $configKey = 'vimeoStats';

    /**
     * Return true if there is some data
     *
     * @return bool
     */
    public static function hasData()
    {
        return count(static::getData()) > 0;
    }

    /**
     * Set the property
     *
     * @param string $key
     * @param int    $value
     *
     * @throws \InvalidArgumentException
     */
    public static function set($key, $value)
    {
        if (!in_array($key, static::getAllowedKeys(), true)) {
            throw new \InvalidArgumentException(sprintf('The key "%s" is not allowed', $key));
        }

        $data       = static::getData();
        $data[$key] = (int)$value;

        Config::set(static::$configKey, serialize($data));
        Config::persist(static::$configKey, serialize($data));
    }

    /**
     * Get the property
     *
     * @param string $key
     *
     * @return int|null
     *
     * @throws \InvalidArgumentException
     */
    public static function get($key)
    {
        if (!in_array($key, static::getAllowedKeys(), true)) {
            throw new \InvalidArgumentException(sprintf('The key "%s" is not allowed', $key));
        }

        $data = static::getData();

        if (!isset($data[$key])) {
            return null;
        }

        return (int)$data[$key];
    }

    /**
     * Get the data
     *
     * @return array
     */
    protected static function getData()
    {
        return deserialize(Config::get(static::$configKey), true);
    }

    /**
     * Get the allowed keys
     *
     * @return array
     */
    protected static function getAllowedKeys()
    {
        return [self::CURRENT_LIMIT, self::TOTAL_LIMIT, self::LIMIT_RESET_TIME];
    }
}