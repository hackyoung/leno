<?php
namespace Leno\Database;

/**
 * 一个adapter负责和底层数据库驱动打交道
 */
interface AdapterInterface
{
    /**
     * 释放数据库事务保存点
     */
    public function releaseSavePoint(string $sp_pos) : bool;

    /**
     * 开始一个事物
     */
    public function beginTransaction() : bool;

    /**
     * 提交一个事务
     */
    public function commitTransaction() : bool;

    /**
     * 事物回滚到上一个事务保存点
     */
    public function rollback();

    /**
     * 用不同的“括号”包裹表，字段等
     */
    public function keyQuote(string $key): string;

    /**
     * 执行一条sql语句
     */
    public function execute(string $sql, $params);

    /**
     * 返回一张表的结构
     */
    public function describeTable(string $table_name);

    /**
     * 返回一个执行sql的驱动
     */
    public function driver() : DriverInterface;

    /**
     * 返回一张表的约束信息
     */
    public function describeConstraint(string $table_name);
}
