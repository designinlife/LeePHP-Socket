<?php
namespace Application\Controller;

use LeePHP\Base\ControllerBase;

class Demo extends ControllerBase {

    function start() {
        $this->send(array(
            'msg' => '我的个神啦!~'
        ));
    }

    function hasParams($data) {
        $this->send(array(
            'msg'  => '收到数据了。',
            'data' => $data
        ));
    }

    function terminate($execute_ms) {
        echo '<br/>耗费时间: ', $execute_ms;
    }
}
