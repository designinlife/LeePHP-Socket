<?php
namespace Application\Task;

use LeePHP\Interfaces\IAsyncTask;
use LeePHP\Utility\Encoder;

class AsyncTask implements IAsyncTask {
    private $data;

    function __construct() {
        $this->data = array(
            'id'   => 1001,
            'name' => 'll001 - 小石头'
        );
    }

    /**
     * 执行任务。
     */
    function execute() {
        \LeePHP\Utility\Console::debug($this->data);
        $this->data['name'] = $this->data['name'] . ' / ' . date('Y-m-d H:i:s');
    }

    function __sleep() {
        return array('data');
    }

    function serialize() {
        return Encoder::encode($this->data, Encoder::MSGPACK);
    }

    function unserialize($serialized) {
        $this->data = Encoder::decode($serialized, Encoder::MSGPACK);
        return $this;
    }

    function __toString() {
        return json_encode($this->data, 320);
    }
}
