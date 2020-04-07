<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2020-03-31
 * Time: 16:37
 */
require_once '../vendor/autoload.php';

use KayCache\Cache;

$options = [
    'host'      => 'redis',
    'serialize' => 'php'
];
$cache = Cache::redis($options);

$cache->set('test', ['a' => 1, 'b' => 2]);
$cache->set('test1', 'aaaaaa');
$data = $cache->get('test');

print_r($data);
$cache->deleteMultiple(['test', 'test1']);

$redis = $cache->handler();
var_dump($redis->incr('test2'));