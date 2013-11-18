<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yipeecaiey
 * Date: 10/12/13
 * Time: 9:02 AM
 * To change this template use File | Settings | File Templates.
 */
class ei8XmlrpcFloodgateTargets extends ei8XmlrpcFloodgateTarget
{
    public $type;
    public $targets;
    public $acct_guid;
    public $remoteTargets;


    public function __construct($type='') {
        global $wpdb;
        $this->db = &$wpdb;
        $this->table = $this->db->prefix . "ei8_floodgate_targets";
        $this->type = $type;
        $this->getTargets();
        $this->getAccountGuid();
        $this->getRemoteTargets();
        return $this;
    }

    public function getAccountGuid() {
        $op = ei8XmlrpcFloodgateOption('acct_guid');
        $this->acct_guid = $op->get();
        return $this->acct_guid;
    }

    public function getTargets(){
        //$this->db->flush();
        if(empty($this->type)) {
            $sql = sprintf(
                "SELECT %s FROM %s",
                'id',
                $this->table
            );
        } else {
            $sql = sprintf(
                "SELECT %s FROM %s WHERE %s=1 ORDER BY %s ASC",
                'id',
                $this->table,
                'is_'.$this->type,
                $this->type.'_order'
            );
        }
        $results = $this->db->get_results($sql);
        if($results===FALSE) ei8_xmlrpc_admin_log("<p style='color:red'><b>SQL ERROR: </b>\"".$this->db->last_error."\"</p>");
        $this->targets = array();
        //echo "<p>RESULTS:<pre>"; print_r($results); echo "</pre></p>";
        //echo "<p>SQL:<pre>"; print_r($sql); echo "</pre></p>";
        if(count($results)>=1) foreach($results as $result) $this->targets[$result->id] = new ei8XmlrpcFloodgateTarget($result->id);
        return $this->targets;
    }

    public function getRemoteTargets(){
        $api = new ei8XmlrpcFloodgateAPI($this->acct_guid);
        $info = $api->getAccountInfo();
        $this->remoteTargets = (empty($this->type)) ? $info->folders : $info->folders->{$this->type};
        return $this->remoteTargets;
    }

    public function importCustomFolders() {
        $customFolders = ei8_xmlrpc_getCustomFolders();
        $cfCT = count($customFolders);
        //ei8_xmlrpc_admin_log("<p>Found $cfCT Custom Folders</p>");
        if($cfCT>=1) foreach($customFolders as $folder) {
            //ei8_xmlrpc_admin_log("<p>Processing custom folder <pre>".print_r($folder)."</pre></p>",1);
            if($folder['value']!='') {
                $t = new ei8XmlrpcFloodgateTarget();
                $t->title = $folder['title'];
                ei8_xmlrpc_admin_log("<p>Importing custom folder: ".$t->title." (video)</p>",1);
                $t->target = $t->getGuidFromOldTarget($folder['value'],'video');
                $t->is_video = 1;
                $t->update();
                $t = new ei8XmlrpcFloodgateTarget();
                $t->title = $folder['title'];
                ei8_xmlrpc_admin_log("<p>Importing custom folder: ".$t->title." (audio)</p>",1);
                $t->target = $t->getGuidFromOldTarget($folder['value'],'audio');
                $t->is_audio = 1;
                $t->update();
            } //else ei8_xmlrpc_admin_log("<p>Skipping custom folder: ".$folder['title']." (no target to import)</p>");
        }
        //we could delete the custom folders at this point...but there isn't really a need, and they *could* be useful
    }

}
