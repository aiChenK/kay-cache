# kay-cache
> 符合psr-16 简单缓存类

## 运行环境
- PHP 7.2+

## 安装方法
        composer require aichenk/kay-cache
        
## 使用
```php
use KayCache\Cache;

$options = [
    'host'       => '127.0.0.1',
    'port'       => 11211,
    'lifetime'   => 3600,
    'prefix'     => '',
    'serialize' => 'php'  // php|json|igbinary|msgpack
];
$cache = Cache::memcache($options);

$cache->set('test', '111');
$data = $cache->get('test');

print_r($data);

$keys = $cache->queryKeys();
print_r($keys);
```

## 更新说明
2020-04-08
- **Add** 支持`queryKeys($prefix = '', bool $realKey = false)`方法，获取key列表
