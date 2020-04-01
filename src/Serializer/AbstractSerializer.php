<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2020-04-01
 * Time: 14:05
 */

namespace KayCache\Serializer;

abstract class AbstractSerializer implements \Serializable
{
    protected $data = null;

    protected function isSerializable($data)
    {
        return !(empty($data) || gettype($data) === "bool" || is_numeric($data));
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}