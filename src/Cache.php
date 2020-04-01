<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2020-04-01
 * Time: 15:37
 */

namespace KayCache;

use KayCache\Driver\Memcache;
use KayCache\Exception\CacheException;

/**
 * @method static Memcache memcache(array $options = [])
 */
class Cache
{

    /**
     * 获取实例
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws CacheException
     */
    public static function __callStatic($name, $arguments)
    {
        $name = ucfirst($name);
        $driverName = "\\KayCache\\Driver\\{$name}";
        if (!class_exists($driverName)) {
            throw new CacheException("not support `$name` cache");
        }
        return new $driverName($arguments[0]);
    }
}