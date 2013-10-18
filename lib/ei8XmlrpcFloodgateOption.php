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

//this class only exists to handle the floodgate specific options that have a special prefix for the option names
class ei8XmlrpcFloodgateOptionFG extends ei8XmlrpcFloodgateOption
{
    public function get($name) {
        return parent::get($this->build_name($name));
    }

    public function load($name) {
        parent::load($this->build_name($name));
    }

    public function set($name,$value='') {
        parent::set($this->build_name($name),$value);
    }

    public function build_name($name) {
        $prefix = 'ei8_floodgate_';
        return (strstr($name,$prefix)) ? $name : $prefix.$name ;
    }
}
