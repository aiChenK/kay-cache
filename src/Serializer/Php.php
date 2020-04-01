<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2020-04-01
 * Time: 14:23
 */

namespace KayCache\Serializer;

use KayCache\Exception\InvalidArgumentException;

class Php extends AbstractSerializer
{

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
        return serialize($this->data);
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
            $this->data = unserialize($data);
        }
    }
}