<?php
namespace LeePHP\Core;

use LeePHP\Interfaces\IRequest;
use LeePHP\Bootstrap;

/**
 * IRequest 对象管理工厂类。
 * 
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class RequestFactory {
    /** 使用 CURL 扩展 */
    const T_CURL = 'curl';
    /** 使用 HTTP 扩展 */
    const T_HTTP = 'http';
    /** HTTP 响应状态码 200 */
    const S_OK                   = 200;
    /** HTTP 响应状态码 404 */
    const S_NOT_FOUND            = 404;
    /** HTTP 响应状态码 500 */
    const S_EXCEPTION            = 500;
    /** 服务器未返回有效的 Mime 类型 */
    const E_NOT_MIME_TYPE        = 201;
    /** 不支持服务器返回的 Mime 类型 */
    const E_NOT_ACCEPT_MIME_TYPE = 202;
    
    /**
     * 实例列表。
     *
     * @var array
     */
    static private $insts = array();

    /**
     * 静态创建 IRequest (Singleton) 对象实例。
     * 
     * @param Bootstrap $scope 指定 Bootstrap 上下文对象引用。
     * @param string $engine   指定引擎名称。(默认值: curl | 可用值: curl, http)
     * @return IRequest
     */
    static function create($scope, $engine = 'curl') {
        if (!isset(self::$insts[$engine])) {
            if ($engine == 'http')
                self::$insts[$engine] = new HTTP($scope);
            elseif ($engine == 'curl')
                self::$insts[$engine] = new CUrl($scope);
        }

        return self::$insts[$engine];
    }

    /**
     * 释放资源。
     */
    static function dispose() {
        self::$insts = NULL;
    }
}
