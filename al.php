<?php
if (!defined('SYS_ROOT'))
    define('SYS_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);

spl_autoload_register('defSplAutoLoadHandler');

/**
 * 类自动加载处理函数。
 * 
 * @param string $class_name
 * @return boolean
 */
function defSplAutoLoadHandler($class_name) {
    $file = SYS_ROOT . str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php';
    include ($file);
}
