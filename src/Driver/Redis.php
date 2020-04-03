<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2020-03-31
 * Time: 14:44
 */

namespace KayCache\Driver;

/**
 * KakCache\Driver\Redis
 *
 * // 实例化
 * $cache = new Redis([
 *      'host'       => '127.0.0.1',
 *      'port'       => 6379,
 *      'index'      => 0,
 *      'lifetime'   => 3600,
 *      'prefix'     => 'test_'
 * ]);
 */
class Redis extends AbstractDriver
{
    protected $options = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',         // 认证
        'index'      => 0,          // dbIndex
        'lifetime'   => 0,
        'prefix'     => '',
        'timeout'    => 1,
        'persistent' => false,
        'serialize'  => 'php',
    ];

    public function __construct(array $options = [])
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('extension `redis` not support');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        parent::__construct($this->options);

        // 初始化实例
        $this->_handler = new \Redis();
        if ($this->options['persistent']) {
            $persistentId = 'persistent_' . $this->options['index'];
            $this->_handler->pconnect($this->options['host'], $this->options['port'], $this->options['timeout'], $persistentId);
        } else {
            $this->_handler->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
        }
        if ($this->options['password']) {
            $this->_handler->auth($this->options['password']);
        }
        if ($this->options['index']) {
            $this->_handler->select($this->options['index']);
        }
        if ($this->_prefix) {
            $this->_handler->setOption(\Redis::OPT_PREFIX, $this->_prefix);
            $this->_prefix = '';
        }

        $this->setSerializer();
    }

    /**
     * 设置序列化类
     */
    private function setSerializer()
    {
        $serialMap = [
            'php' => \Redis::SERIALIZER_PHP
        ];
        if (defined('\\Redis::SERIALIZER_JSON')) {
            $serialMap['json'] = constant('\\Redis::SERIALIZER_JSON');
        }
        if (defined('\\Redis::SERIALIZER_IGBINARY')) {
            $serialMap['igbinary'] = constant('\\Redis::SERIALIZER_IGBINARY');
        }
        if (defined('\\Redis::SERIALIZER_MSGPACK')) {
            $serialMap['msgpack'] = constant('\\Redis::SERIALIZER_MSGPACK');
        }
        // 使用redis自带序列化方式
        if (key_exists($this->_serialize, $serialMap)) {
            $this->_handler->setOption(\Redis::OPT_SERIALIZER, $serialMap[$this->_serialize]);
            $this->_serialize = '';
        }
        $this->initSerializer();
    }

    /**
     * 读取
     *
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        $key   = $this->getCacheKey($key);
        $value = $this->_handler->get($key);
        return $value ? $this->getUnserializeData($value) : $default;
    }

    /**
     * 写入
     *
     * @param string $key
     * @param mixed $value
     * @param null $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        $key   = $this->getCacheKey($key);
        $value = $this->getSerializeData($value);
        $ttl   = $this->getLifetime($ttl);

        if ($ttl) {
            return $this->_handler->setex($key, $ttl, $value);
        }
        return $this->_handler->set($key, $value);
    }

    /**
     * 删除
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $key = $this->getCacheKey($key);
        $this->_handler->del($key);
        return true;
    }

    /**
     * 清空
     *
     * @return bool
     */
    public function clear()
    {
        return $this->_handler->flushDB();
    }

    /**
     * 判断存在
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        $key = $this->getCacheKey($key);
        return !!$this->_handler->exists($key);
    }

    /**
     * 批量删除
     *
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple($keys)
    {
        $this->_handler->del((array) $keys);
        return true;
    }
}