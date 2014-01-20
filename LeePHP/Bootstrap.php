<?php
namespace LeePHP;

define('LEE_PHP_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('LEE_PHP_ROOT_P', dirname(LEE_PHP_ROOT) . DIRECTORY_SEPARATOR);

include (LEE_PHP_ROOT . 'Exceptions.php');

use LeePHP\Core\Application;
use LeePHP\Core\DbPdo;
use LeePHP\Core\Console;
use LeePHP\RuntimeException;
use LeePHP\Interfaces\IProtocol;

/**
 * LeePHP 框架核心启动对象。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class Bootstrap {
    /**
     * 设置监听主机地址。
     *
     * @var string
     */
    private $_host = '0.0.0.0';

    /**
     * 设置服务监听端口号。
     *
     * @var int
     */
    private $_port = 9501;

    /**
     * 设置 SSL 授权服务监听端口号。
     *
     * @var int
     */
    private $_trustPort = 843;

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
    private $_controllerNs = NULL;

    /**
     * 缺省控制器名称。
     *
     * @var string
     */
    private $_defaultControllerName = 'Index';

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
     * 依赖的 Pecl 扩展名称列表。(注: 半角逗号分隔)
     *
     * @var string
     */
    private $_depends = NULL;

    /**
     * 系统配置 INI 文件路径列表。
     *
     * @var array
     */
    private $_iniFiles;

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
     * 当前 IProtocol 实例。
     *
     * @var IProtocol
     */
    private $_protocol = NULL;

    /**
     * 协议名称。
     *
     * @var string
     */
    private $_protocolName = 'Socket';

    /**
     * 设置服务进程名称。
     *
     * @var string
     */
    private $_processName;

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
     * 当前进程 PID。
     *
     * @var int
     */
    public $pid = 0;

    /**
     * Application 对象实例。
     *
     * @var Core\Application
     */
    public $application;

    /**
     * IDb 对象实例。
     *
     * @var Interfaces\IDb
     */
    public $db;

    /**
     * Swoole 资源对象。
     *
     * @var Swoole
     */
    public $swoole;

    /**
     * 请求调度。
     * 
     * @param array $argv    指定 CLI 模式运行时的命令行参数集合。
     * @param array $cmd_map 指定命令配置列表。(默认值: Null | 注: CLI 模式运行时无需此参数)
     */
    function dispatch(&$argv, $cmd_map = NULL) {
        Console::initialize($this);
        
        // 检查是否 CLI 命令行执行模式?
        if (0 !== strcmp(PHP_SAPI, 'cli')) {
            Console::log('仅支持 CLI 模式启动!');
            exit(1);
        }

        // 检查系统依赖 ...
        $this->checkExtensionDepend();

        $this->argv      = $argv;
        $this->pid       = getmypid();
        $this->timestamp = time();
        $this->_start_ms = microtime(true);

        // 设置系统全局参数
        date_default_timezone_set($this->_timeZone);
        error_reporting($this->_errorLevel);
        set_error_handler(array($this, 'defErrorHandler'), $this->_errorLevel);
        set_exception_handler(array($this, 'defExceptionHandler'));

        // 解析系统配置 INI 文件
        if (is_array($this->_iniFiles)) {
            if (!isset($this->_iniFiles['default']))
                throw new ArgumentException('缺少缺省的 INI 系统配置。', -1);

            foreach ($this->_iniFiles as $key => $file)
                $this->cfgs[$key] = parse_ini_file($file, true);
        } else {
            throw new ArgumentException('至少需要一个缺省 INI 系统配置。', -1);
        }

        // 初始化全局基础对象
        $this->application = new Application($this);

        // 实例化基础对象 ...
        $this->db = new DbPdo($this->_dbAutoCommit, $this->_dbPersistent);
        $this->db->addDb($this->cfgs['default']['db']);

        // 初始化协议对象 ...
        if (!is_file(LEE_PHP_ROOT . 'Protocol' . DIRECTORY_SEPARATOR . $this->_protocolName . '.php'))
            throw new ArgumentException('无效的协议文件! (Protocol: ' . $this->_protocolName . ')', -1);

        $cls_name = __NAMESPACE__ . '\Protocol\\' . $this->_protocolName;

        $this->_protocol = new $cls_name($this);

        if (!($this->_protocol instanceof IProtocol))
            throw new RuntimeException('协议类 ' . $this->_protocolName . ' 必须实现 IProtocol 接口。', -1);

        // 实例化 Swoole 对象 ...
        $this->swoole = swoole_server_create($this->_host, $this->_port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        swoole_server_set($this->swoole, $this->cfgs['default']['swoole']);

        swoole_server_handler($this->swoole, 'onStart', array($this->_protocol, 'onStart'));
        swoole_server_handler($this->swoole, 'onShutdown', array($this->_protocol, 'onShutdown'));
        swoole_server_handler($this->swoole, 'onConnect', array($this->_protocol, 'onConnect'));
        swoole_server_handler($this->swoole, 'onClose', array($this->_protocol, 'onClose'));
        swoole_server_handler($this->swoole, 'onWorkerStart', array($this->_protocol, 'onWorkerStart'));
        swoole_server_handler($this->swoole, 'onWorkerStop', array($this->_protocol, 'onWorkerStop'));
        swoole_server_handler($this->swoole, 'onMasterConnect', array($this->_protocol, 'onMasterConnect'));
        swoole_server_handler($this->swoole, 'onMasterClose', array($this->_protocol, 'onMasterClose'));
        swoole_server_handler($this->swoole, 'onReceive', array($this->_protocol, 'onReceive'));
        swoole_server_handler($this->swoole, 'onTimer', array($this->_protocol, 'onTimer'));
        swoole_server_handler($this->swoole, 'onTask', array($this->_protocol, 'onTask'));
        swoole_server_handler($this->swoole, 'onFinish', array($this->_protocol, 'onFinish'));

        swoole_server_start($this->swoole);
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
        $sp = PHP_EOL;

        echo '[代码] ', $errno, $sp;
        echo '[错误] ', $errstr, $sp;
        echo '[文件] ', $errfile, ' (第 ', $errline, ' 行)', $sp;
        Application::bye(-2);
    }

    /**
     * 设置协议名称。
     * 
     * @param string $value
     * @return \LeePHP\Bootstrap
     */
    function setProtocolName($value) {
        $this->_protocolName = $value;
        return $this;
    }

    /**
     * 获取进程名称。
     * 
     * @return string
     */
    function getProcessName() {
        return $this->_processName;
    }

    /**
     * 设置进程名称。
     * 
     * @param string $value
     * @return \LeePHP\Bootstrap
     */
    function setProcessName($value) {
        $this->_processName = $value;
        return $this;
    }

    /**
     * 获取监听主机地址。
     * 
     * @return string
     */
    function getHost() {
        return $this->_host;
    }

    /**
     * 设置监听主机地址。
     * 
     * @param string $value
     * @return \LeePHP\Bootstrap
     */
    function setHost($value) {
        $this->_host = $value;
        return $this;
    }

    /**
     * 获取服务监听端口号。
     * 
     * @return int
     */
    function getPort() {
        return $this->_port;
    }

    /**
     * 设置服务监听端口号。
     * 
     * @param int $value
     * @return \LeePHP\Bootstrap
     */
    function setPort($value) {
        $this->_port = $value;
        return $this;
    }

    /**
     * 获取 SSL 授权服务监听端口号。
     * 
     * @return int
     */
    function getTrustPort() {
        return $this->_trustPort;
    }

    /**
     * 设置 SSL 授权服务监听端口号。
     * 
     * @param int $value
     * @return \LeePHP\Bootstrap
     */
    function setTrustPort($value) {
        $this->_trustPort = $value;
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
     * 设置应用程序控制器名称空间。
     * 
     * @param string $value
     * @return \LeePHP\Bootstrap
     */
    function setControllerNs($value) {
        $this->_controllerNs = $value;
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

    /**
     * 设置系统配置 INI 文件路径。
     * 
     * @param string $files
     * @return \LeePHP\Bootstrap
     */
    function setIniFiles($files) {
        $this->_iniFiles = $files;
        return $this;
    }

    /**
     * 检查 Pecl 扩展依赖。
     * 
     * @throws RuntimeException
     */
    private function checkExtensionDepend() {
        if (!empty($this->_depends)) {
            $extension_s = explode(',', $this->_depends);

            if (!empty($extension_s)) {
                foreach ($extension_s as $ext_name) {
                    if (!extension_loaded(trim($ext_name)))
                        throw new RuntimeException('Pecl 扩展 `' . $ext_name . '` 尚未安装!', -1);
                }
            }
        }
    }
}
