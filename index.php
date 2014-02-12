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
use LeePHP\C;

$dis = new Bootstrap();
$dis->setTimeZone('Asia/Shanghai')
    ->setErrorLevel(E_ALL ^ E_NOTICE)
    ->setLogLevel(0)
    ->setLogDir(SYS_ROOT . 'logs')
    ->setControllerNs('Application\Controller')
    ->setDbAutoCommit(true)
    ->setDbPersistent(false)
    ->setTemplateCacheEnable(true)
    ->setTemplateAutoReload(true)
    ->setTemplateEngine(C::TPL_ENGINE_TWIG)
    ->setTemplateDirectory(SYS_ROOT . 'templates' . DS)
    ->setCompileDirectory(SYS_ROOT . 'templates_c' . DS)
    ->setIniFiles(array(
        'default' => SYS_CONF . 'config.ini'
    ))
    ->dispatch($argv, $g_cmd_hash);
