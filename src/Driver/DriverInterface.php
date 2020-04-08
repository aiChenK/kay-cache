<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2020-04-08
 * Time: 10:20
 */

namespace KayCache\Driver;

use Psr\SimpleCache\CacheInterface;

interface DriverInterface extends CacheInterface
{
    public function handler();
    public function queryKeys($prefix = '', bool $realKey = false);
}