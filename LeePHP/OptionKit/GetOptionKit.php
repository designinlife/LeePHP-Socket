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
namespace LeePHP\OptionKit;

use LeePHP\OptionKit\OptionSpec;
use LeePHP\OptionKit\OptionSpecCollection;
use LeePHP\OptionKit\OptionParser;

class GetOptionKit {
    public $parser;
    public $specs;

    function __construct() {
        $this->specs  = new OptionSpecCollection();
        $this->parser = new OptionParser($this->specs);
    }

    function getParser() {
        return $this->parser;
    }

    /**
     * Gets a collection of all the options.
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
     * Get OptionSpec object.
     * 
     * @param string $id
     * @return OptionSpec
     */
    function get($id) {
        return $this->specs->get($id);
    }

    /**
     * Get Options parameter values.
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

    function printOptions($class = NULL) {
        if (!$class)
            $class = __NAMESPACE__ . '\OptionPrinter';
        
        $this->specs->printOptions($class);
    }
}
