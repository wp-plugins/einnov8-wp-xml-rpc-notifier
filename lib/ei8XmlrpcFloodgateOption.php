<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yipeecaiey
 * Date: 10/18/13
 * Time: 9:04 AM
 * To change this template use File | Settings | File Templates.
 */
class ei8XmlrpcFloodgateOption
{
    public  $table;
    public  $name;
    public  $value;

    public function __construct($name='',$value='') {
        $this->table = new ei8XmlrpcFloodgateDbTableOptions();
        if($name!='')   $this->set($name,$value);
    }

    public function load($name) {
        $this->set($name,$this->get($name));
    }

    public function get($name='') {
        if($name=='')   $name = $this->name;
        if($name=='')   return false;
        return $this->table->get_option($name);
    }

    public function set($name,$value='') {
        $this->name     = $name;
        $this->value    = $value;
    }

    public function update() {
        if(empty($this->name)) return false;
        return $this->table->update_option($this->name,$this->value);
    }

}

//this class only exists to handle specific options that have a special prefix for the option names
class ei8XmlrpcFloodgateOptionPrefixed extends ei8XmlrpcFloodgateOption
{
    public $prefix = "ei8_fg_prefix_";

    public function get($name='') {
        return parent::get($this->build_name($name));
    }

    public function load($name='') {
        parent::load($this->build_name($name));
    }

    public function set($name,$value='') {
        parent::set($this->build_name($name),$value);
    }

    public function set_prefix($prefix) {
        $this->prefix = $prefix;
    }

    public function build_name($name='') {
        if($name=='') $name = $this->name;
        if($name=='') return '';
        return (strstr($name,$this->prefix)) ? $name : $this->prefix.$name ;
    }
}

class ei8XmlrpcFloodgateOptionFG extends ei8XmlrpcFloodgateOptionPrefixed
{
    public function __construct($name='',$value='') {
        $this->set_prefix('ei8_floodgate_');
        parent::__construct($name,$value);
    }
}

class ei8XmlrpcFloodgateOptionCache extends ei8XmlrpcFloodgateOptionPrefixed
{
    public function __construct($name='',$value='') {
        $this->set_prefix('ei8_fgcache_');
        parent::__construct($name,$value);
    }

    public function flush() {
        $this->table->flush_options($this->prefix);
    }
}
