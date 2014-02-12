<?php
namespace LeePHP\System;

use LeePHP\C;
use LeePHP\Bootstrap;
use LeePHP\Base\Base;
use LeePHP\IOException;
use LeePHP\PermissionException;

/**
 * 系统日志管理类。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class Logger extends Base {
    /**
     * 日志类型名称列表。
     *
     * @var array
     */
    private $types;

    /**
     * 构造函数。
     * 
     * @param Bootstrap $ctx
     */
    function __construct($ctx) {
        parent::__construct($ctx);

        $this->types = array(
            1 => '[调试]',
            2 => '[信息]',
            3 => '[警告]',
            4 => '[错误]'
        );
    }

    /**
     * 析构函数。
     */
    function __destruct() {
        parent::__destruct();

        $this->types = NULL;
    }

    /**
     * 写入 DEBUG 数据。(* 支持可变参数)
     * 
     * @throws IOException
     * @throws PermissionException
     */
    function debug() {
        $args = func_get_args();
        $this->wl(C::L_DEBUG, $args);
    }

    /**
     * 文本消息。(* 支持可变参数)
     * 
     * @throws IOException
     * @throws PermissionException
     */
    function info() {
        $args = func_get_args();
        $this->wl(C::L_INFO, $args);
    }

    /**
     * 警告信息。(* 支持可变参数)
     * 
     * @throws IOException
     * @throws PermissionException
     */
    function warning() {
        $args = func_get_args();
        $this->wl(C::L_WARNING, $args);
    }

    /**
     * 错误信息。(* 支持可变参数)
     * 
     * @throws IOException
     * @throws PermissionException
     */
    function error() {
        $args = func_get_args();
        $this->wl(C::L_ERROR, $args);
    }

    /**
     * 写入日志信息。
     * 
     * @param int $type
     * @param array $logs
     * @throws IOException
     * @throws PermissionException
     */
    private function wl($type, $logs) {
        $dir = $this->ctx->getLogDir();

        if (!is_dir($dir))
            throw new IOException('日志目录 `' . $dir . '` 不存在!');
        if (!is_writable($dir))
            throw new PermissionException('日志目录 `' . $dir . '` 不可写!');

        $file     = $dir . DIRECTORY_SEPARATOR . 'active.log';
        $c_date_s = strtotime('today 00:00:00');

        if (is_file($file)) {
            $fmtime = filemtime($file);

            if ($fmtime < $c_date_s) {
                rename($file, $dir . DIRECTORY_SEPARATOR . 'active.' . date('Ymd', $fmtime) . '.log');
            }
        }

        $s   = array();
        $s[] = date('Y-m-d H:i:s');
        $s[] = '[#' . $this->ctx->pid . ']';
        $s[] = $this->types[$type];

        foreach ($logs as $v) {
            if (is_array($v)) {
                $s[] = json_encode($v, 320);
            } else {
                $s[] = strval($v);
            }
        }

        $fp = fopen($file, 'a');
        if ($fp) {
            fwrite($fp, implode(' ', $s) . PHP_EOL);
            fclose($fp);
        }
    }
}
