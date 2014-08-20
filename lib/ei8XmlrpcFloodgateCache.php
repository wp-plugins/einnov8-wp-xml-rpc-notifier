<?php
class ei8XmlrpcFloodgateCache
{
    private $sess_pre;
    public $key;
    public $val;
    private $option;

    function __construct($key='',$flush='') {
        $this->sess_pre = 'ei8_fgcache_';
        if(!empty($key)) $this->set_key($key);
        $this->option = new ei8XmlrpcFloodgateOptionCache();
        if(!empty($flush)) $this->reset();
    }

    function check($key='') {
        if(empty($key)) $key = $this->key;
        //only checking the session here
        //return (isset($_SESSION[$this->sess_pre.$key]));
        return false;
    }

    function get($key='') {
        if(empty($key)) $key = $this->key;
        //first check the session
        if ($this->check($key)) return $_SESSION[$this->sess_pre.$key];
        //if not, check the db
        return $this->option->get($key);

    }

    function reset() {
        $this->option->flush();
    }

    function set($val='') {
        $this->option->set($this->key,$val);
        $this->option->update();
        //$_SESSION[$this->sess_pre.$this->key] = $val;
        $this->val = $val;
    }

    function set_key($key='') {
        $this->key = $key;
    }
}
?>