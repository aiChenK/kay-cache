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
    'host'      => 'memcache',
    'serialize' => 'php'
];
$cache = Cache::memcache($options);
//$cache = new \KayCache\Driver\Memcache($options);

$cache->set('test', ['a' => 1, 'b' => 2]);
$data = $cache->get('test');

print_r($data);

$cache->delete('test');