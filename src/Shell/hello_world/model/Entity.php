<?php
namespace Model;

use \Leno\ORM\Entity as LenoEntity;

/**
 * 应用程序的实体类的父类,所有的实体都应该继承这个类而不是直接继承\Leno\ORM\Entity
 * 减少与\Leno\ORM\Entity的耦合
 */
abstract class Entity extends LenoEntity
{
    /**
     * 需要在Insert之前添加逻辑，则取消注释下面的方法
     */
    protected function beforeInsert()
    {
       // Insert之前的逻辑
    }

    /**
     * 需要在update之前添加逻辑，则取消注释下面的代码
     */
    protected function beforeUpdate()
    {
       // update之前的逻辑
    }

    /**
     * 需要在save之前添加逻辑，则取消注释下面的代码,save的含义是在update/insert都将执行
     */
    protected function beforeSave()
    {
       // save之前的逻辑
    }
}
