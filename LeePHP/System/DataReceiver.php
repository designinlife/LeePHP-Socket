<?php
namespace LeePHP\System;

use LeePHP\Bootstrap;
use LeePHP\Base\Base;

/**
 * HTTP 客户端数据接收器。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class DataReceiver extends Base {
    /**
     * $_GET 参数集合。
     *
     * @var array
     */
    public $gets = NULL;

    /**
     * $_POST 参数集合。
     *
     * @var array
     */
    public $posts = NULL;

    /**
     * $_FILES 参数集合。
     *
     * @var array
     */
    public $files = NULL;

    /**
     * 构造函数。
     * 
     * @param Bootstrap $ctx
     * @param array $gets
     * @param array $posts
     * @param array $files
     */
    function __construct($ctx, &$gets, &$posts, &$files) {
        parent::__construct($ctx);

        $this->gets  = &$gets;
        $this->posts = &$posts;
        $this->files = &$files;
    }

    /**
     * 析构函数。
     */
    function __destruct() {
        parent::__destruct();

        unset($this->gets, $this->posts, $this->files);
    }

    /**
     * 获取 GET 参数值。
     * 
     * @param string $key          指定参数名称。
     * @param string|int $defaults 指定缺省值。
     * @param int $data_type       指定返回的数据类型。(可用值: 0,字符串 1,整型 2,浮点型 3,布尔值)
     * @param boolean $allow_null  指示是否允许 Null 值？(默认值: False | 当此值为 True 时, 空字符串亦返回 $defaults 值.)
     * @return string|int|array
     */
    function get($key, $defaults = NULL, $data_type = 0, $allow_null = false) {
        if (!isset($this->gets[$key]))
            return $defaults;

        if (1 == $data_type) {
            return ( int ) $this->gets[$key];
        } elseif (2 == $data_type) {
            return ( float ) $this->gets[$key];
        } elseif (2 == $data_type) {
            return ( bool ) $this->gets[$key];
        } else {
            $v = trim($this->gets[$key]);

            if ($allow_null && 0 == strlen($v))
                return $defaults;
            else
                return $v;
        }
    }

    /**
     * 获取 POST 参数值。
     * 
     * @param string $key          指定参数名称。
     * @param string|int $defaults 指定缺省值。
     * @param int $data_type       指定返回的数据类型。(可用值: 0,字符串 1,整型 2,浮点型 3,布尔值)
     * @param boolean $allow_null  指示是否允许 Null 值？(默认值: False | 当此值为 True 时, 空字符串亦返回 $defaults 值.)
     * @return string|int|array
     */
    function post($key, $defaults = NULL, $data_type = 0, $allow_null = false) {
        if (!isset($this->posts[$key]))
            return $defaults;

        if (1 == $data_type) {
            return ( int ) $this->posts[$key];
        } elseif (2 == $data_type) {
            return ( float ) $this->posts[$key];
        } elseif (2 == $data_type) {
            return ( bool ) $this->posts[$key];
        } else {
            $v = trim($this->posts[$key]);

            if ($allow_null && 0 == strlen($v))
                return $defaults;
            else
                return $v;
        }
    }

    /**
     * 获取 FILE 文件上传参数值。
     * 
     * @param string $key
     * @return string
     */
    function file($key) {
        return 'false';
    }
}
