<?php

/**
 * 一个adapter负责和底层数据库打交道
 */
namespace Leno\Database;

interface AdapterInterface
{
    /**
     * 连接数据库，可通过option附加连接选项
     */
    public function connect(array $option = null): AdapterInterface;

    /**
     * 取消连接数据库
     */
    public function disconnect(): AdapterInterface;

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
    public function rollback(): AdapterInterface;

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
    public function execute(string $sql, $params = null);
}
