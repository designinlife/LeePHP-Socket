<?php
namespace Application\Controller;

use LeePHP\Base\ControllerBase;

class Demo extends ControllerBase {

    function start() {
        $this->send(array(
            'msg' => '我的个神啦!~'
        ));
    }

    function terminate($execute_ms) {
        echo '<br/>耗费时间: ', $execute_ms;
    }
}
