<?php
namespace LeePHP\Utility;

/**
 * Signature API 接口签名认证工具类。
 * 
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class Signature {
    /**
     * 签名认证必需的安全密钥字符串。
     *
     * @var string
     */
    private $_api_secret_key = '';

    /**
     * 签名参数 key 名称。
     *
     * @var string
     */
    private $_sign_key = 'sign';

    /**
     * 认证参数分隔符。
     *
     * @var string
     */
    private $_data_param_sp = '';

    /**
     * 签名认证的参数列表。
     *
     * @var array
     */
    private $_data_params = NULL;

    /**
     * 需要签名的 key 列表。
     *
     * @var array
     */
    private $_data_keys = array();

    /**
     * 用于签名的参数列表。
     *
     * @var array
     */
    private $_sign_datas = array();

    /**
     * Signature 静态实例变量。
     *
     * @var Signature
     */
    static private $_instance = NULL;

    /**
     * 静态创建 Signature (Singleton) 对象实例。
     * 
     * @param string $api_secret_key 指定签名验证密钥。
     * @param array $data_params     指定参数数据集合。
     * @param string $data_param_sp  指定 MD5 签名参数分隔字符。(默认值: 无分隔符)
     * @param string $sign_key       指定签名加密参数变量名称。(默认值: sign)
     * @return Signature
     */
    static function instance($api_secret_key, &$data_params = NULL, $data_param_sp = '', $sign_key = 'sign') {
        if (!self::$_instance)
            self::$_instance = new Signature($api_secret_key, $data_params, $data_param_sp, $sign_key);

        return self::$_instance;
    }

    /**
     * 构造函数。
     * 
     * @param string $api_secret_key 指定签名验证密钥。
     * @param array $data_params     指定参数数据集合。
     * @param string $data_param_sp  指定 MD5 签名参数分隔字符。(默认值: 无分隔符)
     * @param string $sign_key       指定签名加密参数变量名称。(默认值: sign)
     */
    function __construct($api_secret_key, &$data_params = NULL, $data_param_sp = '', $sign_key = 'sign') {
        $this->_api_secret_key = $api_secret_key;
        $this->_data_params    = &$data_params;
        $this->_data_param_sp  = $data_param_sp;
        $this->_sign_key       = $sign_key;
    }

    /**
     * 析构函数。
     */
    function __destruct() {
        
    }

    /**
     * 添加签名所需的 key 名称。
     * 
     * @param string $key        指定参数键名称。
     * @param string|int $value  指定参数值。(默认值: False | 当构建 API 请求地址时, 务必要传入此参数.)
     * @param boolean $signature 指示此参数值是否作为签名依据？(默认值: True | 当 $value != Null 时, 此参数有效.)
     * @return \Signature
     * @throws ArgumentException
     */
    function addKey($key, $value = false, $signature = true) {
        if (false !== $value) {
            $this->_sign_datas[$key] = $value;

            if ($signature) {
                $this->_data_keys[] = $key;
            }
        } else {
            if (!isset($this->_data_params[$key]))
                throw new ArgumentException('缺少必要参数!', 405);

            $this->_data_keys[] = $key;
        }

        return $this;
    }

    /**
     * 返回签名参数值对字符串。
     * 
     * @return string
     */
    function toUriParams() {
        $dr   = array();
        foreach ($this->_data_keys as $key)
            $dr[] = $this->_sign_datas[$key];

        $s    = implode($this->_data_param_sp, $dr);
        $sign = md5($s . $this->_data_param_sp . $this->_api_secret_key);

        $dp                   = array();
        $dp[$this->_sign_key] = $sign;

        foreach ($this->_sign_datas as $k => $v)
            $dp[$k] = $v;

        return http_build_query($dp);
    }

    /**
     * 验证签名。
     * 
     * @return boolean
     * @throws ArgumentException
     */
    function validate() {
        if (empty($this->_data_params))
            throw new ArgumentException('未指定参数数据源。', -1);

        $signs = array();

        foreach ($this->_data_keys as $key) {
            $signs[] = $this->_data_params[$key];
        }

        $sign = implode($this->_data_param_sp, $signs);
        $sign = md5($sign . $this->_data_param_sp . $this->_api_secret_key);

        if (0 == strcmp($sign, $this->_data_params[$this->_sign_key]))
            return true;
        else
            return false;
    }
}
