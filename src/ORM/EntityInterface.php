<?php
namespace Leno\ORM;

interface EntityInterface
{
    /**
     * 将一个Entity实例持久化存储
     * 该方法必须保证数据的完整性且解决Entity之间的数据依赖关系
     */
    public function save();

    /**
     * 设置Entity的属性
     */
    public function set(string $attr, $value, bool $dirty) : EntityInterface;

    /**
     * 一个属性是数组类型，add向其中添加值
     */
    public function add(string $attr, $value) : EntityInterface;

    /**
     * 获取一个Entity的属性值
     */
    public function get(string $attr, callable $callback);

    /**
     * 将一个Entity持久化移除,该方法必须保证数据的完整性
     */
    public function remove();

    /**
     * 如果该Entity中有属性和数据库中不一至，则返回true
     */
    public function dirty() : bool;

    /**
     * 返回Entity的id，该id有可能是表的主键，也有可能是唯一键
     * 它必须保证Entity的唯一性
     */
    public function id();

    /**
     * 将Entity转换为数组
     */
    public function toArray() : array;
}
