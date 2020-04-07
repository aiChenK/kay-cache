<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2020-04-01
 * Time: 10:32
 */

namespace KayCache\Driver;

use KayCache\Serializer\AbstractSerializer;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

abstract class AbstractDriver implements CacheInterface
{
    /**
     * @var AbstractDriver
     */
    protected $_handler    = null;
    /**
     * @var AbstractSerializer
     */
    protected $_serializer = null;

    protected $_serialize = '';
    protected $_prefix    = '';
    protected $_lifetime  = 0;

    public function __construct($options)
    {
        $this->_lifetime  = $options['lifetime'];
        $this->_prefix    = $options['prefix'];
        $this->_serialize = $options['serialize'];
    }

    /**
     * 获取缓存实例
     *
     * @return AbstractDriver
     */
    public function handler()
    {
        return $this->_handler;
    }

    /**
     * 获取实际key
     *
     * @param string $key
     * @return string
     */
    protected function getCacheKey(string $key)
    {
        return $this->_prefix . $key;
    }

    /**
     * 获取生存时间
     *
     * @param $lifetime
     * @return int|null
     */
    protected function getLifetime($lifetime)
    {
        return $lifetime ?: $this->_lifetime;
    }

    /**
     * 获取序列化数据
     *
     * @param $data
     * @return string
     */
    protected function getSerializeData($data)
    {
        if ($this->_serializer) {
            $this->_serializer->setData($data);
            $data = $this->_serializer->serialize();
        }
        return $data;
    }

    /**
     * 获取反序列化数据
     *
     * @param $data
     * @param $default
     * @return mixed
     */
    protected function getUnserializeData($data, $default = null)
    {
        if (!$data) {
            return $default;
        }
        if ($this->_serializer) {
            $this->_serializer->unserialize($data);
            $data = $this->_serializer->getData();
        }
        return $data;
    }

    /**
     * 批量获取
     *
     * @param iterable $keys
     * @param null $default
     * @return array|iterable
     * @throws InvalidArgumentException
     */
    public function getMultiple($keys, $default = null)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    /**
     * 批量设置
     *
     * @param iterable $values
     * @param null $ttl
     * @return bool
     * @throws InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $val) {
            $result = $this->set($key, $val, $ttl);
            if (false === $result) {
                return false;
            }
        }
        return true;
    }

    /**
     * 批量删除
     *
     * @param iterable $keys
     * @return bool
     * @throws InvalidArgumentException
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $result = $this->delete($key);
            if (false === $result) {
                return false;
            }
        }
        return true;
    }

    /**
     * 初始化序列化类，无对应类则不使用序列化
     */
    protected function initSerializer()
    {
        if ($this->_serialize) {
            $serialize      = ucfirst($this->_serialize);
            $serializerName = "\\KayCache\\Serializer\\{$serialize}";
            if (class_exists($serializerName)) {
                $this->_serializer = new $serializerName();
            }
        }
    }
}