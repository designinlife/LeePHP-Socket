<?php
namespace LeePHP;

/**
 * 系统常量定义。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class C {
    // [Constant] - 数据库
    /* -------------------------------------------------- */
    /** PDO */
    const DB_PDO          = 'PDO';
    /** MySQLi */
    const DB_MySQLi       = 'MySQLi';
    
    // [Constant] - HTTP 网络
    /* -------------------------------------------------- */
    /** Pecl HTTP 扩展 */
    const NET_HTTP        = 'http';
    /** Pecl cURL 扩展 */
    const NET_CURL        = 'curl';
    
    // [Constant] - 日志级别
    /* -------------------------------------------------- */
    /** DEBUG 数据 */
    const L_DEBUG         = 1;
    /** 信息 */
    const L_INFO          = 2;
    /** 警告 */
    const L_WARNING       = 3;
    /** 错误 */
    const L_ERROR         = 4;
}
