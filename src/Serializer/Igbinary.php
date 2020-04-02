<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2020-04-02
 * Time: 17:07
 */

namespace KayCache\Serializer;

use KayCache\Exception\InvalidArgumentException;

class Igbinary extends AbstractSerializer
{

    public function __construct()
    {
        if (!extension_loaded('igbinary')) {
            throw new \BadFunctionCallException('extension `igbinary` not support');
        }
    }

    /**
     * 序列化
     *
     * @return string|null
     */
    public function serialize()
    {
        if (!$this->isSerializable($this->data)) {
            return $this->data;
        }
        return igbinary_serialize($this->data);
    }

    /**
     * 反序列化
     *
     * @param string $data
     * @throws InvalidArgumentException
     */
    public function unserialize($data)
    {
        if (!$this->isSerializable($data)) {
            $this->data = $data;
        } else if (!is_string($data)) {
            throw new InvalidArgumentException('data for unserialize must be string');
        } else {
            $this->data = igbinary_unserialize($data);
        }
    }
}