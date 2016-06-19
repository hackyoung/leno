<?php
namespace Leno\Type;

interface TypeStorageInterface
{
    /**
     * 保存在数据库中的类型，将php中定义的类型转换为数据库友好的类型
     */
    public function toDbType () : string;

    /**
     * 将属于该类型的一个值转换为数据库友好的可存储的值
     */
    public function toDB ($value) : string;

    /**
     * 将数据库友好的值转换为php友好的值
     */
    public function toPHP ($value);
}
