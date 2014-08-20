<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yipeecaiey
 * Date: 10/18/13
 * Time: 7:14 AM
 * To change this template use File | Settings | File Templates.
 */
class ei8XmlrpcFloodgateDbTable extends ei8XmlrpcFloodgateDb
{
    public $table_ident;
    public $table_name;
    public $table_sql;
    public $table_prefix;
    public $tables          = array(
        'options'   => 'ei8_xmlrpc_options',
        'targets'   => 'ei8_floodgate_targets'
    );

    public function __construct($ident='') {
        parent::__construct();
        $this->table_prefix = $this->db_prefix.$this->table_pre;
        if($ident!='' && in_array($ident,array_keys($this->tables))) {
            $this->table_ident = $ident;
            $this->table_name = $this->get_table_name($ident);
        }
    }

    public function get_table_name($ident='',$prefix='') {
        if($ident=='')  $ident = $this->table_ident;
        if($prefix=='') $prefix = $this->table_prefix;
        $name = $prefix.$this->tables[$ident];
        return $name;
        //return ($this->exists($name)) ? $name : false ;
    }

    //create table and store in db
    private function install() {

    }

    //check if table exists in db
    //return boolean
    private function exists($table='') {
        if($table=='') $table = $this->table_name;
        return ($this->db->get_var("SHOW TABLES LIKE '$table'") == $table);
    }

    //check if db stored sql matches class sql
    //return boolean
    private function matches() {

    }
}

class ei8XmlrpcFloodgateDbTableOptions extends ei8XmlrpcFloodgateDbTable
{
    public function __construct() {
        parent::__construct('options');
        
        $this->table_sql = "CREATE TABLE `{$this->table_name}` (
            `ID` BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
            `option_name` VARCHAR( 100 ) NOT NULL ,
            `option_value` TEXT NOT NULL,
            PRIMARY KEY ( `ID` ),
            UNIQUE ( `option_name` )
            );";
    }

    public function flush_options($name) {
        $sql     = "DELETE FROM {$this->table_name} WHERE option_name LIKE '$name%'";
        return ei8_xmlrpc_admin_query($sql);
    }

    public function get_option($name) {
        $this->db->flush();
        $sql     = "SELECT option_value FROM {$this->table_name} WHERE option_name='$name' LIMIT 1";
        $results = $this->db->get_results($sql);
        if(!$results) return '';
        $result = stripslashes($results[0]->option_value);
        return $result;
    }

    public function update_option($name,$value) {
        //check first to see if the option already exists
        $sql = "SELECT ID FROM {$this->table_name} WHERE option_name='$name'";
        $results = $this->db->get_results($sql);
        $option_id = $results[0]->ID;
        $value = addslashes($value);
        if(!empty($option_id)) {
            $sql = $this->db->prepare(
                "UPDATE {$this->table_name} SET option_value='%s' WHERE ID='%s'",
                $value,
                $option_id
            );
        } else {
            $sql = $this->db->prepare(
                "INSERT INTO {$this->table_name} SET option_name='%s', option_value='%s' ON DUPLICATE KEY UPDATE option_value='%s'",
                $name,
                $value,
                $value
            );
        }
        ei8_xmlrpc_admin_query($sql);
        $this->db->flush();
        return true;
    }
}

class ei8XmlrpcFloodgateDbTableTargets extends ei8XmlrpcFloodgateDbTable
{
    public function __construct() {
        parent::__construct('targets');
        
        $this->table_sql = "CREATE TABLE `{$this->table_name}` (
            `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
            `title` VARCHAR( 100 ) NOT NULL ,
            `target` TEXT NOT NULL,
            `media_type` TEXT NOT NULL,
            `orderer` INT( 3 ) NOT NULL DEFAULT  '5',
            PRIMARY KEY ( `id` )
            );";
    }
    
    public function create_target($data) {
        $sql = $this->db->prepare("INSERT INTO {$this->table_name} SET
                title ='%s',
                target ='%s',
                media_type = '%s',
                orderer = %d",
            $data->title,
            addslashes($data->target),
            $data->media_type,
            $data->orderer
        );
        ei8_xmlrpc_admin_query($sql);
        return $this->db->insert_id;
    }

    public function delete_target($id) {
        $sql = $this->db->prepare("DELETE FROM {$this->table_name} WHERE id=%d",
            $id
        );
        ei8_xmlrpc_admin_query($sql);
        //$this->db->flush();
        return $this->id;
    }

    public function get_target($id) {
        $sql     = $this->db->prepare("SELECT * FROM {$this->table_name} WHERE id=%d LIMIT 1", $id);
        $result = $this->db->get_results($sql);
        if($result===FALSE) ei8_xmlrpc_admin_log("<p style='color:red'><b>SQL ERROR: </b>\"".$this->db->last_error."\"(SQL: $sql)</p>");
        //echo "<p>RESULT:<pre>"; print_r($result); echo "</pre></p>";
        return $result[0];
    }

    public function update_target($data) {
        $sql = $this->db->prepare("UPDATE {$this->table_name} SET
                title ='%s',
                target ='%s',
                media_type ='%s',
                orderer = %d
             WHERE id = %d",
            $data->title,
            addslashes($data->target),
            $data->media_type,
            $data->orderer,
            $data->id
        );
        ei8_xmlrpc_admin_query($sql);
        //$data->db->flush();
        return $data->id;
    }

    public function update_order($id, $col, $position) {
        $sql = $this->db->prepare("UPDATE {$this->table_name} SET $col=%d WHERE id=%d", $position, $id);
        ei8_xmlrpc_admin_query($sql);
        //$this->db->flush();
        return $id;
    }
}

