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
//    'host'      => 'memcache',
    'servers'    => [
        [
            'host' => 'memcache',
            'port' => 11211
        ],
        [
            'host' => 'memcache2',
            'port' => 11211,
            'weight' => 3
        ]
    ],
    'serialize' => 'php',
    'prefix' => 'a_'
];
$cache = Cache::memcache($options);
//$cache = new \KayCache\Driver\Memcache($options);

$cache->set('test', ['a' => 3, 'b' => 4]);
$data = $cache->get('test');
print_r($data);

$cache->delete('test');
