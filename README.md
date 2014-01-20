LeePHP-Socket 框架
=============

基于 swoole 扩展的 PHP Socket 框架。


示例
=============
    运行启动命令: ./app
-------------
    #!/usr/bin/php
    <?php
    define('LINE_DELIMITER', "\n");
    define('TAB_INDENT', "\t");
    define('DS', DIRECTORY_SEPARATOR);
    define('SYS_ROOT', dirname(__FILE__) . DS);
    define('SYS_CONF', SYS_ROOT . 'etc' . DS);

    header('Content-Type: text/html; CharSet=UTF-8');

    include (SYS_CONF . 'cmd.inc.php');
    include (SYS_ROOT . 'al.php');

    use LeePHP\Bootstrap;

    $dis = new Bootstrap();
    $dis->setTimeZone('Asia/Shanghai')
        ->setErrorLevel(E_ALL ^ E_NOTICE)
        ->setLogLevel(0)
        ->setLogDir(SYS_ROOT . 'logs')
        ->setControllerNs('Application\Controller')
        ->setDbAutoCommit(true)
        ->setDbPersistent(false)
        ->setHost('0.0.0.0')
        ->setPort(9501)
        ->setProtocolName('Socket')
        ->setDepends('swoole, zmq, pdo_mysql, curl')
        ->setIniFiles(array(
            'default' => SYS_CONF . 'config.ini'
        ))
        ->dispatch($argv, $g_cmd_hash);

