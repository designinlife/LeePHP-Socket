<?php
namespace LeePHP\Interfaces;

/**
 * IDb 接口定义。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
interface IDb {
    /**
     * 连接数据库。
     *
     */
    function connect();

    /**
     * 关闭数据库连接。
     *
     */
    function close();

    /**
     * 获取查询耗时总计。(毫秒)
     *
     */
    function spend();

    /**
     * 切换数据库连接。
     *
     * @param string $alias_key
     * @param string $db_name
     * @return boolean
     */
    function change($alias_key, $db_name = NULL);

    /**
     * USE `database` 方法切换活动数据库。
     *
     * @param string $dbname
     */
    function useDb($dbname = NULL);

    /**
     * USE `database` 快捷方式切换数据库。
     *
     * @param int $dbServerId 指定服务器 ID。(默认值: 0,连接到缺省数据库)
     */
    function changeDbServer($dbServerId = 0);

    /**
     * 注册数据库连接参数。
     *
     * @param array $dbParameters
     */
    function addDb($dbParameters);

    /**
     * 查询一行记录。
     *
     * @param string $sql
     * 				指定 SQL 语句。
     * @param array $dataParams
     * 				指定查询参数集合。
     * @param int $cache_type
     * 				指定缓存类型。(1,Memcached 2,Redis)
     * @param string $cache_key
     * 				指定缓存标识键。
     * @param int $expire
     * 				指定过期时间。
     * @return array
     */
    function fetch($sql, $dataParams = NULL);

    /**
     * 查询全部记录。
     *
     * @param string $sql
     * 				指定 SQL 语句。
     * @param array $dataParams
     * 				指定查询参数集合。
     * @param int $cache_type
     * 				指定缓存类型。(1,Memcached 2,Redis)
     * @param string $cache_key
     * 				指定缓存标识键。
     * @param int $expire
     * 				指定过期时间。
     * @return array
     */
    function fetchAll($sql, $dataParams = NULL);

    /**
     * 执行 IDbAdapter 扩展对象。
     * 
     * @param IDbAdapter $adapter
     * @return array
     */
    function fetchAdapter(&$adapter);

    /**
     * 查询单行、单列数据。
     *
     * @param string $sql
     * @param array $dataParams
     * @return string
     */
    function scalar($sql, $dataParams = NULL);

    /**
     * [简易] 新增记录。
     * 
     * @param string $table 指定数据表名。
     * @param array $fields 指定要添加的字段键值对列表。
     * @return int          返回最后插入的记录 AUTO_INCREMENT 字段值。
     */
    function a($table, $fields);

    /**
     * [简易] 修改记录。
     * 
     * @param string $table     指定数据表名。
     * @param array $fields     指定要更新的键值对列表。
     * @param string $cond      指定查询条件。(注: 不包含 WHERE 关键字.)
     * @param array $condParams 指定条件参数。
     * @return int              返回更新操作影响的行数。
     */
    function e($table, $fields, $cond = '', $condParams = NULL);

    /**
     * [简易] 删除记录。
     * 
     * @param string $table     指定数据表名。
     * @param string $cond      指定查询条件。(注: 不包含 WHERE 关键字.)
     * @param array $condParams 指定条件参数。
     * @return int              返回删除操作影响的行数。
     */
    function d($table, $cond = '', $condParams = NULL);

    /**
     * 执行更新查询。
     *
     * @param string $sql
     * @param array $dataParams
     * @param int $sql_type 指定 SQL 更新查询类型。(1,INSERT, 2,UPDATE, 3,DELETE)
     */
    function execute($sql, $dataParams = NULL, $sql_type = 1);

    /**
     * 启动 MySQL 事务机制。
     *
     */
    function begin();

    /**
     * 提交事务。
     *
     */
    function commit();

    /**
     * 回滚事务。
     *
     */
    function rollback();

    /**
     * 检查事务是否已开始？
     * 
     * @return boolean
     */
    function isTranStarted();
}
