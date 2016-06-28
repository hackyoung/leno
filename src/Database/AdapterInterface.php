<?php

/**
 * 一个adapter负责和底层数据库驱动打交道
 */
namespace Leno\Database;

interface AdapterInterface
{
    /**
     * 返回一个执行sql的驱动
     */
    public function driver() : DriverInterface;

    /**
     * 释放数据库事务保存点
     */
    public function releaseSavePoint(string $sp_pos): bool;

    /**
     * 开始一个事物
     */
    public function beginTransaction(): bool;

    /**
     * 提交一个事务
     */
    public function commitTransaction(): bool;

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
     * @exception ForeignKeyException
     * @exception PrimaryKeyException
     * @exception UniqueKeyException
     */
    public function execute(string $sql, $params);
}
