<?php
namespace Leno\ORM;

/**
 * 一个Data是一个可以通过Mapper去进行存储操作的实体
 * Data本身应该告诉Mapper哪些字段需要更新，Data的哪些字段
 * 值不满足类型约束
 */
interface DataInterface
{
    /**
     * 验证字段是否满足类型约束
     */
    public function validate() : bool;

    /**
     * 取出所有的脏数据供Mapper使用
     */
    public function getDirty() : array;

    /**
     * 返回可用于唯一标识该Data的id
     */
    public function id() : array;
}
