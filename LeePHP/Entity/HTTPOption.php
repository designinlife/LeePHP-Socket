<?php
namespace LeePHP\Entity;

/**
 * HTTP 请求参数选项。
 * 
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class HTTPOption {
    private $_timeout         = 120;
    private $_connectTimeout  = 60;
    private $_dnsCacheTimeout = 3600;
    private $_userAgent       = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:25.0) Gecko/20100101 Firefox/25.0';
    private $_headers         = array();
    private $_onProgress      = NULL;
    private $_encodeCookies   = false;
    private $_cookies         = NULL;
    private $_proxyHost       = NULL;
    private $_acceptMimeTypes = array();
    private $_postFields      = NULL;

    /**
     * 析构函数。
     */
    function __destruct() {
        unset($this->_onProgress, $this->_headers, $this->_acceptMimeTypes);
    }

    /**
     * 返回 http_get() 函数所需的请求参数集合。(注: 此方法仅适用于 Pecl HTTP 扩展. cURL 方式请使用 getXXX() 获取参数值.)
     * 
     * @return array
     */
    function getOptions() {
        $opts = array(
            'timeout'           => $this->_timeout,
            'connecttimeout'    => $this->_connectTimeout,
            'dns_cache_timeout' => $this->_dnsCacheTimeout,
            'useragent'         => $this->_userAgent,
            'headers'           => $this->_headers
        );

        if ($this->_cookies) {
            $opts['encodecookies'] = $this->_encodeCookies;

            $cks = http_parse_cookie($this->_cookies, 0);

            $opts['cookies'] = $cks->cookies;
        }

        if ($this->_proxyHost) {
            $opts['proxyhost'] = $this->_proxyHost;
        }

        if ($this->_onProgress)
            $opts['onprogress'] = $this->_onProgress;

        return $opts;
    }

    /**
     * 获取超时时长。(单位: 秒)
     * 
     * @return int
     */
    function getTimeout() {
        return $this->_timeout;
    }

    /**
     * 设置超时时长。(单位: 秒)
     * 
     * @param int $timeout
     */
    function setTimeout($timeout) {
        $this->_timeout = $timeout;
    }

    /**
     * 获取连接超时。(单位: 秒)
     * 
     * @return int
     */
    function getConnectTimeout() {
        return $this->_connectTimeout;
    }

    /**
     * 设置连接超时。(单位: 秒)
     * 
     * @param int $connectTimeout
     */
    function setConnectTimeout($connectTimeout) {
        $this->_connectTimeout = $connectTimeout;
    }

    /**
     * 获取 DNS 缓存超时。(单位: 秒)
     * 
     * @return int
     */
    function getDnsCacheTimeout() {
        return $this->_dnsCacheTimeout;
    }

    /**
     * 设置 DNS 缓存超时。(单位: 秒)
     * 
     * @param int $dnsCacheTimeout
     */
    function setDnsCacheTimeout($dnsCacheTimeout) {
        $this->_dnsCacheTimeout = $dnsCacheTimeout;
    }

    /**
     * 获取客户端浏览器版本标识字符串。
     * 
     * @return string
     */
    function getUserAgent() {
        return $this->_userAgent;
    }

    /**
     * 客户端浏览器版本标识字符串。
     * 
     * @param string $userAgent
     */
    function setUserAgent($userAgent) {
        $this->_userAgent = $userAgent;
    }

    /**
     * 获取 HTTP 请求头集合。
     * 
     * @return array
     */
    function getHeaders() {
        return $this->_headers;
    }

    /**
     * 添加 HTTP 请求头。
     * 
     * @param string $key
     * @param string $value
     */
    function addHeader($key, $value) {
        $this->_headers[] = $key . ': ' . $value;
    }

    /**
     * 获取回调函数。
     * 
     * @return callable
     */
    function getOnProgress() {
        return $this->_onProgress;
    }

    /**
     * 设置回调函数。
     * 
     * @param callable $onProgress
     */
    function setOnProgress($onProgress) {
        $this->_onProgress = $onProgress;
    }

    /**
     * 指示 Cookie 是否需要 RFC 编码？
     * 
     * @return boolean
     */
    function getEncodeCookies() {
        return $this->_encodeCookies;
    }

    /**
     * 指示 Cookie 是否需要 RFC 编码？
     * 
     * @param boolean $encodeCookies
     */
    function setEncodeCookies($encodeCookies) {
        $this->_encodeCookies = $encodeCookies;
    }

    /**
     * 获取 Cookie 集合。
     * 
     * @return string
     */
    function getCookies() {
        return $this->_cookies;
    }

    /**
     * 设置 Cookie 集合。
     * 
     * @param string $cookies
     */
    function setCookies($cookies) {
        $this->_cookies = $cookies;
    }

    /**
     * 获取代理服务器主机信息。
     * 
     * @return string
     */
    function getProxyHost() {
        return $this->_proxyHost;
    }

    /**
     * 设置代理服务器主机信息。
     * 
     * @param string $proxyHost
     */
    function setProxyHost($proxyHost) {
        $this->_proxyHost = $proxyHost;
    }

    /**
     * 指示是否支持 MIME 类型？
     * 
     * @param string $mime_type 指定 MIME 类型名称。
     * @return boolean
     */
    function isAcceptMimeType($mime_type) {
        if (empty($this->_acceptMimeTypes))
            return true;

        if (false !== strpos($mime_type, ';')) {
            $s         = explode(';', $mime_type);
            $mime_type = $s[0];
        }

        $mime_type = strtolower(trim($mime_type));

        $ok = in_array($mime_type, $this->_acceptMimeTypes, true);

        return $ok;
    }

    /**
     * 添加支持的 MIME 类型。
     * 
     * @param string $mime_type 指定服务端响应的可靠 MIME 类型。(注: 多个类型之间使用半角逗号分隔)
     */
    function setAcceptMimeType($mime_type) {
        if (false !== strpos($mime_type, ',')) {
            $ss = explode(',', $mime_type);

            foreach ($ss as $s) {
                $this->_acceptMimeTypes[] = strtolower(trim($s));
            }
        } else {
            $this->_acceptMimeTypes[] = strtolower(trim($mime_type));
        }
    }

    /**
     * 添加 POST 参数值。
     * 
     * @param string $key
     * @param string $value
     */
    function addPostField($key, $value) {
        if (!$this->_postFields)
            $this->_postFields = array();

        $this->_postFields[$key] = $value;
    }

    /**
     * 获取 POST 请求键值对。
     * 
     * @return array|boolean
     */
    function getPostFields() {
        if (!$this->_postFields)
            return false;

        return $this->_postFields;
    }
}
