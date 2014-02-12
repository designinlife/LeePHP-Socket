<?php
namespace LeePHP;

define('LEE_PHP_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('LEE_PHP_ROOT_P', dirname(LEE_PHP_ROOT) . DIRECTORY_SEPARATOR);

include (LEE_PHP_ROOT . 'Exceptions.php');

use LeePHP\Interfaces\IProtocol;
use LeePHP\Interfaces\ISwoole;
use LeePHP\Protocol\AppServer;
use LeePHP\System\Application;
use LeePHP\System\Logger;
use LeePHP\DB\DbPdo;
use LeePHP\Utility\Console;
use LeePHP\Interfaces\IDb;

/**
 * LeePHP 框架核心启动对象。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class Bootstrap {
    /**
     * 系统时区。
     *
     * @var string
     */
    private $_timeZone = 'Asia/Shanghai';

    /**
     * 错误级别。
     *
     * @var int
     */
    private $_errorLevel = 32759;

    /**
     * 控制器名称空间。
     *
     * @var string
     */
    private $_controll_ns = '\Application\Controller';

    /**
     * DB 自动提交。(仅适用于 InnoDB 引擎)
     *
     * @var boolean
     */
    private $_dbAutoCommit = true;

    /**
     * DB 持久化连接。
     *
     * @var boolean
     */
    private $_dbPersistent = false;

    /**
     * 监听主机地址。
     *
     * @var string
     */
    private $_host = '0.0.0.0';

    /**
     * 监听端口。
     *
     * @var int
     */
    private $_port = 9501;

    /**
     * 日志级别。
     *
     * @var int
     */
    private $_logLevel = 0;

    /**
     * 日志文件存储目录。
     *
     * @var string
     */
    private $_logDir = NULL;

    /**
     * DEBUG 模式开关。
     *
     * @var boolean
     */
    private $_debug_enable = false;

    /**
     * 依赖的 Pecl 扩展名称列表。(注: 半角逗号分隔)
     *
     * @var string
     */
    private $_depends = NULL;

    /**
     * 开始时间。(毫秒)
     *
     * @var float
     */
    private $_start_ms = 0;

    /**
     * 结束时间。(毫秒)
     *
     * @var float
     */
    private $_end_ms = 0;

    /**
     * 总计执行时间。(毫秒)
     *
     * @var float
     */
    private $_execute_ms = 0;

    /**
     * Swoole 实例对象。
     *
     * @var ISwoole
     */
    private $_ci = NULL;

    /**
     * 当前协议对象。
     *
     * @var IProtocol
     */
    private $_as;

    /**
     * 系统当前时间戳。
     *
     * @var int
     */
    public $timestamp = 0;

    /**
     * CLI 命令行参数集合。
     *
     * @var array
     */
    public $argv = NULL;

    /**
     * 系统配置参数。
     *
     * @var array
     */
    public $cfgs = array();

    /**
     * 系统命令配置列表。
     *
     * @var array
     */
    public $cmds;

    /**
     * 当前进程 PID。
     *
     * @var int
     */
    public $pid = 0;

    /**
     * Application 对象实例。
     *
     * @var Application
     */
    public $app;

    /**
     * IDb 对象实例。
     *
     * @var IDb
     */
    public $db;

    /**
     * Logger 日志管理对象。
     *
     * @var Logger
     */
    public $logger;

    /**
     * 请求调度。
     * 
     * @param array $argv    指定 CLI 模式运行时的命令行参数集合。
     * @param array $def_cnf 指定系统全局配置参数。
     */
    function dispatch(&$argv, &$def_cnf) {
        if (0 != strcmp(PHP_SAPI, 'cli')) {
            echo '此脚本仅支持 cli 模式运行!';
            exit(0);
        }

        // 载入命令行参数
        $this->argv = $argv;

        include (SYS_CONF . 'cmd.inc.php');

        $this->cmds = $g_cmd_hash;

        $this->timestamp = time();
        $this->_start_ms = microtime(true);
        $this->cfgs['g'] = $def_cnf;

        // 设置系统全局参数
        date_default_timezone_set($this->_timeZone);
        error_reporting($this->_errorLevel);
        set_error_handler(array($this, 'defErrorHandler'), $this->_errorLevel);
        set_exception_handler(array($this, 'defExceptionHandler'));

        // 初始化全局基础对象
        $this->app = new Application($this);

        Console::initialize($this, true);

        // 实例化基础对象 ...
        $this->logger = new Logger($this);

        $this->db = new DbPdo($this->_dbAutoCommit, $this->_dbPersistent);
        $this->db->addDb($this->cfgs['g']['db']);

        // 初始化 Swoole 实例 ...
        $this->_as = new AppServer($this);

        $this->_ci = new \swoole_server($this->_host, $this->_port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $this->_ci->set($this->cfgs['g']['swoole']);

        $this->_ci->on('start', array($this->_as, 'onStart'));
        $this->_ci->on('shutdown', array($this->_as, 'onShutdown'));
        $this->_ci->on('connect', array($this->_as, 'onConnect'));
        $this->_ci->on('close', array($this->_as, 'onClose'));
        $this->_ci->on('workerStart', array($this->_as, 'onWorkerStart'));
        $this->_ci->on('workerStop', array($this->_as, 'onWorkerStop'));
        $this->_ci->on('masterConnect', array($this->_as, 'onMasterConnect'));
        $this->_ci->on('masterClose', array($this->_as, 'onMasterClose'));
        $this->_ci->on('receive', array($this->_as, 'onReceive'));
        $this->_ci->on('timer', array($this->_as, 'onTimer'));
        $this->_ci->on('task', array($this->_as, 'onTask'));
        $this->_ci->on('finish', array($this->_as, 'onFinish'));
        $this->_ci->start();
    }

    /**
     * 程序执行终止之前调用此方法。
     */
    function terminate() {
        $this->_end_ms     = microtime(true);
        $this->_execute_ms = ($this->_end_ms - $this->_start_ms) * 1000;
    }

    /**
     * 缺省异常处理函数。
     * 
     * @param \Exception $ex
     */
    function defExceptionHandler($ex) {
        echo '(', $ex->getCode(), ') ', $ex->getMessage(), PHP_EOL, $ex->getTraceAsString(), PHP_EOL;
        Application::bye(-1);
    }

    /**
     * 缺省错误处理函数。
     * 
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     */
    function defErrorHandler($errno, $errstr, $errfile, $errline) {
        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    /**
     * 获取控制器名称空间。
     * 
     * @return string
     */
    function getControllerNs() {
        return $this->_controll_ns;
    }

    /**
     * 设置控制器名称空间。
     * 
     * @param string $value
     * @return \LeePHP\Bootstrap
     */
    function setControllerNs($value) {
        $this->_controll_ns = $value;
        return $this;
    }

    /**
     * 设置系统时区。
     * 
     * @param string $value
     * @return \LeePHP\Bootstrap
     */
    function setTimeZone($value) {
        $this->_timeZone = $value;
        return $this;
    }

    /**
     * 设置系统错误报告级别。
     * 
     * @param int $value
     * @return \LeePHP\Bootstrap
     */
    function setErrorLevel($value) {
        $this->_errorLevel = $value;
        return $this;
    }

    /**
     * 设置缺省控制器名称。
     * 
     * @param string $value
     * @return \LeePHP\Bootstrap
     */
    function setDefaultControllerName($value) {
        $this->_defaultControllerName = $value;
        return $this;
    }

    /**
     * 获取日志级别。
     * 
     * @return int
     */
    function getLogLevel() {
        return $this->_logLevel;
    }

    /**
     * 设置日志级别。
     * 
     * @param int $value
     * @return \LeePHP\Bootstrap
     */
    function setLogLevel($value) {
        $this->_logLevel = $value;
        return $this;
    }

    /**
     * 获取日志文件存储目录。
     * 
     * @return string
     */
    function getLogDir() {
        return $this->_logDir;
    }

    /**
     * 设置日志文件存储目录。
     * 
     * @param string $value
     * @return \LeePHP\Bootstrap
     */
    function setLogDir($value) {
        $this->_logDir = $value;
        return $this;
    }

    /**
     * 设置 DEBUG 模式开启状态。
     * 
     * @param boolean $enable
     * @return \LeePHP\Bootstrap
     */
    function setDebugEnable($enable) {
        $this->_debug_enable = $enable;
        return $this;
    }

    /**
     * 指示是否已开启 DEBUG 模式？
     * 
     * @return boolean
     */
    function isDebug() {
        return $this->_debug_enable;
    }

    /**
     * 获取主服务监听地址及端口。
     * 
     * @return string
     */
    function getListener() {
        return $this->_host . ':' . $this->_port;
    }

    /**
     * 设置服务监听地址、端口。
     * 
     * @param string $host
     * @param int $port
     * @return \LeePHP\Bootstrap
     */
    function setListener($host, $port) {
        $this->_host = $host;
        $this->_port = $port;
        return $this;
    }

    /**
     * 指示是否开启 DB 自动提交？(仅适用于 InnoDB 引擎)
     * 
     * @param boolean $enable
     * @return \LeePHP\Bootstrap
     */
    function setDbAutoCommit($enable) {
        $this->_dbAutoCommit = $enable;
        return $this;
    }

    /**
     * 指示是否开启 DB 持久化连接？
     * 
     * @param boolean $enable
     * @return \LeePHP\Bootstrap
     */
    function setDbPersistent($enable) {
        $this->_dbPersistent = $enable;
        return $this;
    }

    /**
     * 设置依赖的 Pecl 扩展名称列表。(注: 半角逗号分隔)
     * 
     * @param string $depends
     * @return \LeePHP\Bootstrap
     */
    function setDepends($depends) {
        $this->_depends = $depends;
        return $this;
    }
}
