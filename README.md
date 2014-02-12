LeePHP-Socket 框架
=============

基于 swoole 扩展的 PHP Socket 框架。


示例
=============
    运行启动命令: ./app
-------------
```php
#!/usr/bin/php
<?php
define('LINE_DELIMITER', "\n");
define('TAB_INDENT', "\t");
define('DS', DIRECTORY_SEPARATOR);
define('SYS_ROOT', dirname(__FILE__) . DS);
define('SYS_CONF', SYS_ROOT . 'etc' . DS);

include (SYS_CONF . 'config.inc.php');
include (SYS_ROOT . 'al.php');

use LeePHP\Bootstrap;

$dis = new Bootstrap();
$dis->setTimeZone('Asia/Shanghai')
    ->setErrorLevel(E_ALL ^ E_NOTICE)
    ->setLogLevel(0)
    ->setLogDir(SYS_ROOT . 'logs')
    ->setListener('0.0.0.0', 9501)
    ->setDbAutoCommit(true)
    ->setDbPersistent(false)
    ->dispatch($argv, $g_conf);

