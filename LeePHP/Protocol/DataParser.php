<?php
namespace LeePHP\Protocol;

use LeePHP\Utility\Encoder;

/**
 * 客户端数据解析器。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class DataParser {
    /**
     * 将发送回客户端的数据打包。
     * 
     * @param array $data_out
     * @return string
     */
    static function encode($data_out) {
        $s = Encoder::encode($data_out, Encoder::MSGPACK);
        $l = strlen($s);

        return pack('cNa*', false, $l, $s);
    }

    /**
     * 解析客户端传入的数据字符串。
     * 
     * @param string $data_str
     * @return array
     */
    static function decode($data_str) {
        $len = strlen($data_str);

        $s = substr($data_str, 0, $len - 3);
        $d = Encoder::decode($s, Encoder::MSGPACK);

        return $d;
    }
}
