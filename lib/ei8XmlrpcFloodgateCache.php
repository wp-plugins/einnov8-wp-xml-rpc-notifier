<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yipeecaiey
 * Date: 5/14/14
 * Time: 12:19 PM
 * To change this template use File | Settings | File Templates.
 */
class ei8XmlrpcFloodgateCache
{
    private $sess_pre;
    public $key;
    public $val;

    function __construct($key) {
        $this->sess_pre = 'ei8_fgcache_';
        $this->key = $key;
    }

    function check() {
        return (isset($_SESSION[$this->sess_pre.$this->key]));
    }

    function get() {
        return ($this->check()) ? $_SESSION[$this->sess_pre.$this->key] : false ;
    }

    function set($val='') {
        $this->val = $_SESSION[$this->sess_pre.$this->key] = $val;
    }
}
?>