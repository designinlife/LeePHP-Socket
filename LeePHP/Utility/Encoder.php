<?php
namespace LeePHP\Utility;

/**
 * 编码/解码工具类。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0
 */
class Encoder {
    /** 编码格式: JSON */
    const JSON = 1;

    /** 编码格式: MessagePack */
    const MSGPACK = 2;

    /** 编码格式: igbinary */
    const IGBINARY = 3;

    /** 编码格式: XML */
    const XML = 4;

    /**
     * 生成基于 MD5 算法的字符串。
     * 
     * @param string $str       需要加密的字符串。
     * @param boolean $is_16bit 指示是否生成 16 位长度的加密字符串？
     * @return string
     */
    static function MD5($str, $is_16bit = false) {
        $s = \md5($str);

        if ($is_16bit)
            return substr($s, 8, 16);
        else
            return $s;
    }

    /**
     * 生成基于 SHA-1 算法的字符串。
     * 
     * @param string $str
     * @return string
     */
    static function SHA1($str) {
        return \sha1($str);
    }

    /**
     * 数据编码。
     * 
     * @param array $data  指定数据对象。
     * @param int $format  指定编码格式。(默认值: 1 | JSON, 其它可用值: Encoder::MSGPACK, Encoder::XML)
     * @param int $options 指定参数选项。(注: 此参数仅对 JSON 编码有效.)
     * @return string
     * @throws NotImplementedException
     */
    static function encode($data, $format = self::JSON, $options = 320) {
        $s = false;

        switch ($format) {
            case self::JSON:
                $s = json_encode($data, $options);
                break;
            case self::MSGPACK:
                $s = msgpack_serialize($data);
                break;
            case self::IGBINARY:
                $s = igbinary_serialize($data);
                break;
            case self::XML:
                throw new NotImplementedException('尚未实现此接口。', -1);
                break;
        }

        return $s;
    }

    /**
     * 数据解码。
     * 
     * @param string $data 指定数据对象。
     * @param int $format 指定编码格式。(默认值: 1 | JSON, 其它可用值: Encoder::MSGPACK, Encoder::XML)
     * @return array|mixed
     * @throws NotImplementedException
     */
    static function decode($data, $format = 1) {
        $s = false;

        switch ($format) {
            case self::JSON:
                $s = json_decode($data, true);
                break;
            case self::MSGPACK:
                $s = msgpack_unserialize($data);
                break;
            case self::IGBINARY:
                $s = igbinary_unserialize($data);
                break;
            case self::XML:
                throw new NotImplementedException('尚未实现此接口。', -1);
                break;
        }

        return $s;
    }
}
