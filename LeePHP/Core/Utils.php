<?php
namespace LeePHP\Core;

use LeePHP\ArgumentException;
use LeePHP\NotSupportException;

/**
 * 工具辅助类。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class Utils {
    /**
     * 对称加密算法 - (加密)。
     *
     * @param string $s
     * @param string $secure_key
     * @return string
     * @throws NotSupportException
     */
    static function encrypt($s, $secure_key) {
        if (!extension_loaded('mcrypt'))
            throw new NotSupportException('Mcrypt extension not installed.');

        if (null == $s || !is_string($s))
            return false;

        $td      = mcrypt_module_open('tripledes', '', 'ecb', '');
        $td_size = mcrypt_enc_get_iv_size($td);
        $iv      = mcrypt_create_iv($td_size, MCRYPT_RAND);
        $key     = substr($secure_key, 0, $td_size);
        mcrypt_generic_init($td, $key, $iv);
        $ret     = base64_encode(mcrypt_generic($td, $s));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $ret;
    }

    /**
     * 对称加密算法 - (解密)。
     *
     * @param string $s
     * @param string $secure_key
     * @return string
     * @throws NotSupportException
     */
    static function decrypt($s, $secure_key) {
        if (!extension_loaded('mcrypt'))
            throw new NotSupportException('Mcrypt extension not installed.');

        if (null == $s)
            return false;

        $td      = mcrypt_module_open('tripledes', '', 'ecb', '');
        $td_size = mcrypt_enc_get_iv_size($td);
        $iv      = mcrypt_create_iv($td_size, MCRYPT_RAND);
        $key     = substr($secure_key, 0, $td_size);
        mcrypt_generic_init($td, $key, $iv);
        $ret     = trim(mdecrypt_generic($td, base64_decode($s)));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $ret;
    }
    
    /**
     * 规范化文件/目录路径字符串。
     * 
     * @param string $filepath 指定文件/文件夹路径。
     * @return string
     */
    static function standardize($filepath) {
        return preg_replace('/[\/\\\]{2,}/', DIRECTORY_SEPARATOR, $filepath);
    }

    /**
     * 字符串变量替换。(支持可变参数)
     *
     * @return string
     */
    static function substitute() {
        $args = func_get_args();
        $size = func_num_args();

        if ($size === 0)
            return '';

        $str = $args[0];

        for ($i = 1; $i < $size; $i++)
            $str = str_replace('{' . ($i - 1) . '}', $args[$i], $str);

        return $str;
    }

    /**
     * 获取字节单位转换后的字符串。
     * 
     * @param int $size 指定字节长度数值。
     * @return string   返回带有单位的字符串。(例如: 1.25MB ...)
     */
    static function size($size) {
        $unit = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        return round($size / pow(1024, ($i    = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

    /**
     * 生成 GUID 全球唯一标识。
     * 
     * @return string
     */
    static function guid() {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = '-';
        $uuid   = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);

        return $uuid;
    }

    /**
     * 抽奖计算。
     *
     * @param array $ratios 指定概率列表。
     * @return string       返回中奖的元素 key 值。
     */
    static function lottery($ratios) {
        $result = false;

        // 概率数组的总概率精度
        $proSum = array_sum($ratios);

        // 概率数组循环
        foreach ($ratios as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }

        unset($ratios);

        return $result;
    }

    /**
     * 概率计算函数。检测传入的概率值是否命中？
     * 
     * @param float $rate 指定概率值。(此值必须是 0~1 之间的浮点数。包含0,1两个整数.)
     * @return boolean    返回 True 时，表示已命中。
     * @throws ArgumentException
     */
    static function hit($rate) {
        if (is_string($rate))
            $rate = ( float ) $rate;

        if ($rate > 1)
            throw new ArgumentException('传入的概率值 $rate 必须是 0~1 之间的浮点数或整数(0|1)。', -1);

        $r = 100 * $rate;
        $v = mt_rand(1, 100);

        if ($v <= $r)
            return true;
        return false;
    }

    /**
     * IP 地址转换为整数。
     * 
     * @param string $ip_addr 指定 IP 地址。(默认值: NULL | 当前 REMOTE_ADDR 值。)
     * @return int
     */
    static function ip($ip_addr = NULL) {
        if (is_null($ip_addr))
            $ip_addr = $_SERVER['REMOTE_ADDR'];

        $ips = explode('.', $ip_addr);

        $v = ( int ) $ips[0] * 16777216;
        $v += ( int ) $ips[1] * 65536;
        $v += ( int ) $ips[2] * 256;
        $v += ( int ) $ips[3];

        return $v;
    }

    /**
     * 检查 $num 数值是否为素数？
     * 
     * @param int $num 指定要检测的数值。
     * @return boolean
     */
    static function isPrime($num) {
        if ($num == 1)
            return false;

        if ($num == 2)
            return true;

        if ($num % 2 == 0) {
            return false;
        }

        for ($i = 3; $i <= ceil(sqrt($num)); $i = $i + 2) {
            if ($num % $i == 0)
                return false;
        }

        return true;
    }
}
