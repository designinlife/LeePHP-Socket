<?php
/*
 * This file is part of the GetOptionKit package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace GetOptionKit;

use GetOptionKit\OptionSpec;
use GetOptionKit\OptionSpecCollection;
use GetOptionKit\OptionResult;
use GetOptionKit\OptionParser;
use Exception;

class GetOptionKit {
    public $parser;
    public $specs;

    function __construct() {
        $this->specs  = new OptionSpecCollection;
        $this->parser = new OptionParser($this->specs);
    }
    /*
     * return current parser 
     * */

    function getParser() {
        return $this->parser;
    }

    /**
     * 获取全部选项对象集合。
     * 
     * @return OptionSpecCollection
     */
    function getSpecs() {
        return $this->specs;
    }
    
    /**
     * A helper to build option specification object from string spec.
     * 
     * @param string $specString
     * @param string $description
     * @param string $key
     * @return OptionSpec
     */
    function add($specString, $description, $key = null) {
        $spec = $this->specs->add($specString, $description, $key);
        return $spec;
    }

    /**
     * 获取 OptionSpec 对象。
     * 
     * @param string $id
     * @return OptionSpec
     */
    function get($id) {
        return $this->specs->get($id);
    }
    
    /**
     * 获取选项参数值。
     * 
     * @param string $id
     * @return string|int
     */
    function getValue($id) {
        return $this->specs->get($id)->value;
    }

    function parse($argv) {
        return $this->parser->parse($argv);
    }

    function printOptions($class = 'GetOptionKit\OptionPrinter') {
        $this->specs->printOptions($class);
    }
}
