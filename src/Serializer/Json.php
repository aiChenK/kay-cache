<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2020-04-01
 * Time: 14:23
 */

namespace KayCache\Serializer;

use KayCache\Exception\InvalidArgumentException;

class Json extends AbstractSerializer
{

    public function __construct()
    {
        if (!extension_loaded('json')) {
            throw new \BadFunctionCallException('extension `json` not support');
        }
    }

    /**
     * 序列化
     *
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function serialize()
    {
        if (gettype($this->data) === 'object' && !($this->data instanceof \JsonSerializable)) {
            throw new InvalidArgumentException('object can not JsonSerializable');
        }

        if (!$this->isSerializable($this->data)) {
            return $this->data;
        }
        return json_encode($this->data);
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
            $this->data = json_decode($data, true);
        }
    }
}