<?php
namespace LeePHP\DB;

use LeePHP\Interfaces\IDb;
use LeePHP\Interfaces\IDbAdapter;
use LeePHP\ArgumentException;
use \PDO;
use \PDOException;

/**
 * PDO for MySQL 操作类。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class DbPdo implements IDb {
    /**
     * Return single records.
     *
     */
    const PDO_SQL_QUERY_FETCH_ROW = 1;

    /**
     * Return the entire result set.
     *
     */
    const PDO_SQL_QUERY_FETCH_ALL = 2;

    /**
     * Returns a single row single column result.
     *
     */
    const PDO_SQL_QUERY_FETCH_COLUMN = 3;

    /**
     * Execute INSERT statement.
     *
     */
    const PDO_SQL_EXECUTE_INSERT = 4;

    /**
     * Perform UPDATE / DELETE statements.
     *
     */
    const PDO_SQL_EXECUTE_UPDATE_OR_DELETE = 5;

    /**
     * SQL - INSERT type.
     *
     */
    const SQL_TYPE_INSERT = 1;

    /**
     * SQL - UPDATE type.
     *
     */
    const SQL_TYPE_UPDATE = 2;

    /**
     * SQL - DELETE type.
     *
     */
    const SQL_TYPE_DELETE = 3;

    /**
     * One-time implementation of a number of non-SELECT statement.
     *
     */
    const SQL_TYPE_MULTI_QUERY = 4;

    /**
     * 指示是否已创建数据库连接？
     *
     * @var boolean
     */
    private $isActive    = false;

    /**
     * 指示是否已执行 InnoDB 事务启动语句？
     *
     * @var boolean
     */
    private $isTransStarted = false;

    /**
     * 缺省数据库名称。
     *
     * @var string
     */
    private $defaultDbName = '';

    /**
     * 指示是否静默方式执行查询？
     *
     * @var boolean
     */
    private $silent = false;

    /**
     * 指示 InnoDB 事务自动提交模式。
     *
     * @var boolean
     */
    private $isAutoCommit = false;

    /**
     * 指示是否持久化连接？
     *
     * @var boolean
     */
    private $isPersistent = false;

    /**
     * 数据库名称集合。
     *
     * @var array
     */
    private $dbNames = NULL;

    /**
     * 活动的 DB 参数 Key 值。
     *
     * @var string
     */
    private $dbActiveKey = NULL;

    /**
     * 数据库配置参数集合。
     *
     * @var array
     */
    private $dbCfgs = NULL;

    /**
     * PDO 对象实例。
     *
     * @var PDO
     */
    private $dbo = NULL;

    /**
     * 延迟执行的 SQL 队列。
     *
     * @var array
     */
    private $sql_queues = NULL;

    /**
     * DB 事务计数器变量。(注: 当此变量值 = 0 时, 才可以提交/回滚事务)
     *
     * @var int
     */
    private $trans_count = 0;

    /**
     * 统计查询耗时。
     *
     * @var int
     */
    static private $_execute_seconds = 0;

    /**
     * 构造函数。
     *
     * @param boolean $isAutoCommit 指示是否自动提交事务?
     * @param boolean $isPersistent 指示是否开启持久连接机制?
     */
    function __construct($isAutoCommit = false, $isPersistent = false) {
        $this->isAutoCommit = $isAutoCommit;
        $this->isPersistent = $isPersistent;
    }

    /**
     * 析构函数。
     *
     */
    function __destruct() {
        self::$_execute_seconds = 0;

        $this->dbCfgs     = NULL;
        $this->dbNames    = NULL;
        $this->sql_queues = NULL;
    }

    /**
     * 连接数据库。
     *
     */
    function connect() {
        if (!$this->isActive) {
            $this->dbo = new PDO('mysql:host=' . $this->dbCfgs[$this->dbActiveKey]['host'] . ';dbname=' . $this->dbNames[$this->dbActiveKey][0] . ';', $this->dbCfgs[$this->dbActiveKey]['user'], $this->dbCfgs[$this->dbActiveKey]['pass']);

            $this->dbo->setAttribute(PDO::ATTR_PERSISTENT, $this->isPersistent);
            $this->dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            $this->dbo->setAttribute(PDO::ATTR_AUTOCOMMIT, $this->isAutoCommit);
            $this->dbo->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
            $this->dbo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            $this->dbo->exec("SET NAMES " . $this->dbCfgs[$this->dbActiveKey]['charset']);
            $this->dbo->exec("SET sql_mode = 'NO_UNSIGNED_SUBTRACTION';");

            $this->isActive = true;
        }
    }

    /**
     * 关闭数据库连接。
     *
     */
    function close() {
        $this->isActive = false;
        $this->dbo      = NULL;
    }

    /**
     * 切换数据库连接。
     *
     * @param string $alias_key
     * @param string $db_name
     * @return boolean
     */
    function change($alias_key, $db_name = NULL) {
        if (!isset($this->dbCfgs[$alias_key]))
            throw new ArgumentException('Invalid connection identifier key.', -1);

        if ($alias_key == $this->dbActiveKey)
            return true;

        $this->close();
        $this->dbActiveKey   = $alias_key;
        $this->defaultDbName = $db_name ? $db_name : $this->dbNames[$this->dbActiveKey][0];
        $this->isActive      = false;

        return true;
    }

    /**
     * USE `database` 方法切换活动数据库。
     *
     * @param string $dbname
     */
    function useDb($dbname = NULL) {
        $this->connect();

        if (is_null($dbname))
            $this->dbo->exec("USE `" . $this->dbNames[$this->dbActiveKey][0] . "`");
        else
            $this->dbo->exec("USE `" . $dbname . "`");
    }

    /**
     * USE `database` 快捷方式切换数据库。
     *
     * @param int $dbServerId 指定服务器 ID。(默认值: 0,连接到缺省数据库)
     */
    function changeDbServer($dbServerId = 0) {
        $this->useDb($this->dbNames['default'][$dbServerId]);
    }

    /**
     * 注册数据库连接参数。
     *
     * @param array $dbParameters
     */
    function addDb($dbParameters) {
        $first = is_null($this->dbCfgs);

        if ($first && !isset($dbParameters['default']))
            throw new ArgumentException('Yet to find the default database configuration parameters.', -1);

        if ($first)
            $this->dbCfgs = $dbParameters;
        else
            $this->dbCfgs = array_merge($this->dbCfgs, $dbParameters);

        foreach ($dbParameters as $key => $value) {
            if ('default' == $key) {
                if (!$this->dbNames)
                    $this->dbNames = array();

                $this->dbActiveKey        = 'default';
                $this->defaultDbName      = $dbParameters['default']['default'];
                $this->dbNames['default'] = explode('|', $dbParameters['default']['name']);
            } else {
                $this->dbNames[$key] = explode('|', $value['name']);
            }
        }
    }

    /**
     * 查询一行记录。
     *
     * @param string $sql
     * @param array $dataParams
     * @return array
     */
    function fetch($sql, $dataParams = NULL) {
        $d = $this->_execute($sql, $dataParams, self::PDO_SQL_QUERY_FETCH_ROW, PDO::FETCH_ASSOC);

        return $d;
    }

    /**
     * 查询全部记录。
     *
     * @param string $sql
     * @param array $dataParams
     * @return array
     */
    function fetchAll($sql, $dataParams = NULL) {
        $d = $this->_execute($sql, $dataParams, self::PDO_SQL_QUERY_FETCH_ALL, PDO::FETCH_ASSOC);

        return $d;
    }

    /**
     * 执行 IDbAdapter 扩展对象。
     * 
     * @param IDbAdapter $adapter
     * @return array
     */
    function fetchAdapter(&$adapter) {
        return $adapter->execute();
    }

    /**
     * 查询单行、单列数据。
     *
     * @param string $sql
     * @param array $dataParams
     * @return string
     */
    function scalar($sql, $dataParams = NULL) {
        $d = $this->_execute($sql, $dataParams, self::PDO_SQL_QUERY_FETCH_COLUMN, 0, 0);

        return $d;
    }

    /**
     * [简易] 新增记录。
     * 
     * @param string $table 指定数据表名。
     * @param array $fields 指定要添加的字段键值对列表。
     * @return int          返回最后插入的记录 AUTO_INCREMENT 字段值。
     */
    function a($table, $fields) {
        $a = array();
        $b = array();
        $c = array();

        foreach ($fields as $k => $v) {
            $a[] = '`' . $k . '`';
            $b[] = '?';
            $c[] = $v;
        }

        $sql = 'INSERT INTO ' . $table . '(' . implode(', ', $a) . ') VALUES(' . implode(', ', $b) . ');';

        $auto_id = $this->_execute($sql, $c, self::PDO_SQL_EXECUTE_INSERT);

        return $auto_id;
    }

    /**
     * [简易] 修改记录。
     * 
     * @param string $table     指定数据表名。
     * @param array $fields     指定要更新的键值对列表。
     * @param string $cond      指定查询条件。(注: 不包含 WHERE 关键字.)
     * @param array $condParams 指定条件参数。
     * @return int              返回更新操作影响的行数。
     */
    function e($table, $fields, $cond = '', $condParams = NULL) {
        $a = array();
        $b = array();

        foreach ($fields as $k => $v) {
            $a[] = '`' . $k . '` = ?';
            $b[] = $v;
        }

        if (!empty($cond) && is_array($condParams)) {
            foreach ($condParams as $v) {
                $b[] = $v;
            }
        }

        $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $a) . (empty($cond) ? : ' WHERE ' . $cond);

        $affected = $this->_execute($sql, $b, self::PDO_SQL_EXECUTE_UPDATE_OR_DELETE);

        return $affected;
    }

    /**
     * [简易] 删除记录。
     * 
     * @param string $table     指定数据表名。
     * @param string $cond      指定查询条件。(注: 不包含 WHERE 关键字.)
     * @param array $condParams 指定条件参数。
     * @return int              返回删除操作影响的行数。
     */
    function d($table, $cond = '', $condParams = NULL) {
        $sql = 'DELETE FROM ' . $table . (empty($cond) ? : ' WHERE ' . $cond);

        $affected = $this->_execute($sql, $condParams, self::PDO_SQL_EXECUTE_UPDATE_OR_DELETE);

        return $affected;
    }

    /**
     * 执行更新查询。
     *
     * @param string $sql
     * @param array $dataParams
     * @param int $sql_type 指定 SQL 更新查询类型。(1,INSERT, 2,UPDATE, 3,DELETE)
     */
    function execute($sql, $dataParams = NULL, $sql_type = 1) {
        if ($sql_type == self::SQL_TYPE_INSERT) {
            $d = $this->_execute($sql, $dataParams, self::PDO_SQL_EXECUTE_INSERT);
        } elseif ($sql_type == self::SQL_TYPE_UPDATE || $sql_type == self::SQL_TYPE_DELETE) {
            $d = $this->_execute($sql, $dataParams, self::PDO_SQL_EXECUTE_UPDATE_OR_DELETE);
        } elseif ($sql_type == self::SQL_TYPE_MULTI_QUERY) {
            $sqls = explode(';', $sql);
            foreach ($sqls as $sql)
                $this->_execute($sql, $dataParams, self::PDO_SQL_EXECUTE_UPDATE_OR_DELETE);
        }
        return $d;
    }

    /**
     * 获取查询耗时总计。(毫秒)
     *
     * @return float
     */
    function spend() {
        return self::$_execute_seconds * 1000;
    }

    /**
     * 启动 MySQL 事务机制。
     *
     */
    function begin() {
        $this->connect();

        if ($this->isActive) {
            $this->trans_count++;

            if (false == $this->isTransStarted) {
                $this->isTransStarted = true;
                $this->dbo->exec("BEGIN");

                if ($this->isAutoCommit)
                    $this->dbo->exec('SET autocommit = 0');
            }
        }
    }

    /**
     * 提交事务。
     *
     */
    function commit() {
        if ($this->isActive) {
            $this->trans_count--;

            if ($this->isTransStarted && 0 === $this->trans_count) {
                $this->dbo->exec("COMMIT");
                $this->isTransStarted = false;

                if ($this->isAutoCommit)
                    $this->dbo->exec('SET autocommit = 1');
            }
        }
    }

    /**
     * 回滚事务。
     *
     */
    function rollback() {
        if ($this->isActive) {
            $this->trans_count--;

            if ($this->isTransStarted && 0 === $this->trans_count) {
                $this->dbo->exec("ROLLBACK");
                $this->isTransStarted = false;

                if ($this->isAutoCommit)
                    $this->dbo->exec('SET autocommit = 1');
            }
        }
    }

    /**
     * 检查事务是否已开始？
     * 
     * @return boolean
     */
    function isTranStarted() {
        return $this->isTransStarted;
    }

    /**
     * Executive SQL queries.
     *
     * @param string $sql
     * 				  SQL statement to be executed (to support pre-compiled grammar).
     * @param array $data
     * 				  Array of parameters passed.
     * @param integer $exec_type
     * 				  Type definitions are available. (INSERT/UPDATE/DELETE/SELECT)�?
     * @param integer $fetch_style
     * 				  The results of the structure of the specified form of the output array.
     * @param integer $column_index
     * 				  Executive single column query, the value of the specified column index return.
     * @access private
     * @return mixed
     * 				  If returns FALSE, the query operation failed.
     */
    private function _execute($sql, $data = NULL, $exec_type = self::PDO_SQL_QUERY_FETCH_ALL, $fetch_style = PDO::FETCH_ASSOC, $column_index = 0) {
        $d = false;

        $sql = trim($sql);

        $start_time = microtime(true);

        if (!$this->isActive)
            $this->connect();

        try {
            if ($this->silent)
                $this->dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            else
                $this->dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sth = $this->dbo->prepare($sql);

            if (false == (is_null($data) || (is_array($data) && count($data) == 0))) {
                foreach ($data as $key => $value) {
                    if (is_array($value))
                        $sth->bindValue(1 + $key, $value[0], $value[1]);
                    else
                        $sth->bindValue(1 + $key, $value);
                }
            }

            $sth->execute();
        } catch (PDOException $ex) {
            throw $ex;
        }

        switch ($exec_type) {
            case self::PDO_SQL_EXECUTE_INSERT :
                $d = $this->dbo->lastInsertId();
                break;
            case self::PDO_SQL_EXECUTE_UPDATE_OR_DELETE :
                $d = $sth->rowCount();
                break;
            case self::PDO_SQL_QUERY_FETCH_ROW :
                $d = $sth->fetch($fetch_style);
                break;
            case self::PDO_SQL_QUERY_FETCH_ALL :
                $d = $sth->fetchAll($fetch_style);
                break;
            case self::PDO_SQL_QUERY_FETCH_COLUMN :
                $d = $sth->fetchColumn($column_index);
                break;
        }

        $sth->closeCursor();
        $sth = null;

        $end_time = microtime(true);

        self::$_execute_seconds += ($end_time - $start_time);

        return $d;
    }
}
