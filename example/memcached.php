<?php

require_once '../vendor/autoload.php';

use KayCache\Cache;

$options = [
    'host'      => 'memcache',
    'serialize' => 'php',
    'prefix'    => ''
];
$cache = Cache::memcached($options);
//$cache = new \KayCache\Driver\Memcached($options);

$data = $cache->get('test');
print_r($data);

$keys = $cache->queryKeys();
print_r($keys);
