<?php
namespace LeePHP\Entity;

/**
 * HTTP 响应结果对象。
 * 
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class HTTPResponseMessage {
    /**
     * 文件存储的子目录名称。(注: 此属性值对 downloadFile() 方法有效.)
     *
     * @var string
     */
    private $_subDirectory = NULL;

    /**
     * 文件扩展名。(注: 包含 . 字符)
     *
     * @var string
     */
    private $_extName = NULL;

    /**
     * 文件名称。(注: 包含扩展名)
     *
     * @var string
     */
    private $_fileName = NULL;

    /**
     * 文件名称。(注: 不包含扩展名)
     *
     * @var string
     */
    private $_filenameBase = NULL;

    /**
     * 文件相对路径。
     *
     * @var string
     */
    private $_filePath = NULL;

    /**
     * 文件存放的绝对路径。
     *
     * @var string
     */
    private $_absoluteFile = NULL;

    /**
     * 文件存放的物理目录路径。
     *
     * @var string
     */
    private $_absoluteDir = NULL;

    /**
     * 文件占用字节大小。
     *
     * @var int
     */
    private $_length = 0;

    /**
     * 文档内容 MIME 类型。
     *
     * @var string
     */
    private $_contentType = NULL;

    /**
     * 远程 HTTP 请求返回的消息内容。
     *
     * @var string
     */
    private $_content = NULL;

    /**
     * 文档内容 MIME 类型。
     *
     * @var string
     */
    private $_mimeType = NULL;

    /**
     * HTTP 响应头状态码。
     *
     * @var int
     */
    private $_statusCode = 200;

    /**
     * 构造函数。
     * 
     * @param int $status_code      指定 HTTP 响应头状态码。
     */
    function __construct($status_code) {
        $this->_statusCode = $status_code;
    }

    /**
     * 聚合属性字段。
     */
    function done() {
        $this->_fileName = $this->_filenameBase . $this->_extName;

        if (!empty($this->_subDirectory)) {
            $this->_filePath = $this->_subDirectory . '/' . $this->_fileName;
        } else {
            $this->_filePath = $this->_fileName;
        }

        $this->_absoluteFile = $this->_absoluteDir . DIRECTORY_SEPARATOR . $this->_subDirectory . DIRECTORY_SEPARATOR . $this->_filenameBase . $this->_extName;
    }

    /**
     * 获取子目录名称。(注: 不包含路径分隔符)
     * 
     * @return string
     */
    function getSubDirectory() {
        return $this->_subDirectory;
    }

    /**
     * 设置子目录名称。(注: 不包含路径分隔符)
     * 
     * @param string $subDirectory
     */
    function setSubDirectory($subDirectory) {
        $this->_subDirectory = $subDirectory;
    }

    /**
     * 获取文件扩展名。
     * 
     * @return string
     */
    function getExtName() {
        return $this->_extName;
    }

    /**
     * 设置文件扩展名。
     * 
     * @param string $extName
     */
    function setExtName($extName) {
        $this->_extName = $extName;
    }

    /**
     * 获取文件名称。(注: 不包含目录路径)
     * 
     * @return string
     */
    function getFileName() {
        return $this->_fileName;
    }

    /**
     * 获取文件名称。(注: 不包含目录路径及扩展名)
     * 
     * @return string
     */
    function getFilenameBase() {
        return $this->_filenameBase;
    }

    /**
     * 设置文件名称。(注: 不包含目录路径及扩展名)
     * 
     * @param string $filenameBase
     */
    function setFilenameBase($filenameBase) {
        $this->_filenameBase = $filenameBase;
    }

    /**
     * 获取文件相对访问路径。(注: 不包含物理存储目录路径)
     * 
     * @return string
     */
    function getFilePath() {
        return $this->_filePath;
    }

    /**
     * 获取文件存储的物理绝对路径。
     * 
     * @return string
     */
    function getAbsoluteFile() {
        return $this->_absoluteFile;
    }

    /**
     * 获取文件存储的物理目录绝对路径。
     * 
     * @return string
     */
    function getAbsoluteDir() {
        return $this->_absoluteDir;
    }

    /**
     * 设置文件存储的物理目录绝对路径。
     * 
     * @param string $absoluteDir
     */
    function setAbsoluteDir($absoluteDir) {
        $this->_absoluteDir = $absoluteDir;
    }

    /**
     * 获取内容长度。(单位: 字节)
     * 
     * @return int
     */
    function getLength() {
        return $this->_length;
    }

    /**
     * 设置内容长度。(单位: 字节)
     * 
     * @param int $length
     */
    function setLength($length) {
        $this->_length = $length;
    }

    /**
     * 获取文档类型标识。
     * 
     * @return string
     */
    function getContentType() {
        return $this->_contentType;
    }

    /**
     * 设置文档类型标识。
     * 
     * @param string $contentType
     */
    function setContentType($contentType) {
        $this->_contentType = $contentType;

        $smt = strtolower($contentType);

        if (false !== strpos($smt, ';')) {
            $s = explode(';', $smt);

            $this->_mimeType = trim($s[0]);
        } else {
            $this->_mimeType = trim($contentType);
        }
    }

    /**
     * 获取内容。
     * 
     * @return string
     */
    function getContent() {
        return $this->_content;
    }

    /**
     * 设置内容。
     * 
     * @param string $content
     */
    function setContent($content) {
        $this->_content = $content;
    }

    /**
     * 获取文档 MIME 类型标识。
     * 
     * @return string
     */
    function getMimeType() {
        return $this->_mimeType;
    }

    /**
     * 获取 HTTP 响应状态代码。
     * 
     * @return int
     */
    function getStatusCode() {
        return $this->_statusCode;
    }
}
