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

    function __construct($key='') {
        $this->sess_pre = 'ei8_fgcache_';
        if(!empty($key)) $this->set_key($key);
    }

    function check($key='') {
        if(empty($key)) $key = $this->key;
        return (isset($_SESSION[$this->sess_pre.$key]));
    }

    function get($key='') {
        if(empty($key)) $key = $this->key;
        return ($this->check($key)) ? $_SESSION[$this->sess_pre.$key] : false ;
    }

    function set($val='') {
        $this->val = $_SESSION[$this->sess_pre.$this->key] = $val;
    }

    function set_key($key='') {
        $this->key = $key;
    }
}
?>