<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yipeecaiey
 * Date: 10/12/13
 * Time: 7:52 AM
 * To change this template use File | Settings | File Templates.
 */
class ei8XmlrpcFloodgateTarget {
    public $id;
    public $title;
    public $target;
    public $media_type;
    public $orderer;
    public $remoteTargetExists;

    private $table;

    public function __construct($id='') {
        $this->table = new ei8XmlrpcFloodgateDbTableTargets();
        if($id!='' && is_numeric($id)) $this->get($id);
        return $this;
    }

    public function import($data,$update='') {
        foreach($data as $key=>$val) $this->$key=$val;
        if($update!='') $this->update();
    }

    public function is_video() {
        return ($this->media_type=='video');
    }

    public function is_audio() {
        return ($this->media_type=='audio');
    }

    public function is_text() {
        return ($this->media_type=='text');
    }

    public function is_image() {
        return ($this->media_type=='image');
    }

    public function get($id) {
        $array = $this->table->get_target($id);
        foreach($array as $key=>$val) $this->$key = stripslashes($val);
        return $this;
    }

    public function create() {
        $this->id = $this->table->create_target($this);
        return $this->id;
    }

    public function delete() {
        return $this->table->delete_target($this->id);
    }

    public function update() {
        if(empty($this->id) || !is_numeric($this->id)) return $this->create();
        return $this->table->update_target($this);
    }

    public function update_order($type,$position) {
        $col = $type.'_order';
        $this->$col = $position;
        return $this->table->update_order($this->id, $col, $position);
    }

    public function getGuidFromOldTarget($target,$type) {
        $guids = self::getGuidsFromOldTarget($target);
        return $guids[$target];
    }

    public function getGuidsFromOldTarget($target) {
        $target = html_entity_decode($target);
        $parts = (strstr($target,'&')) ? explode('&',$target) : explode(' ',$target) ;
        $guids = array();
        foreach($parts as $part) {
            list($t,$guid) = explode("=",$part);
            $type = ($t==v) ? 'video' : 'audio' ;
            $guids[$type] = $guid;
        }
        return $guids;
    }


}

class ei8XmlrpcFloodgateTargetRemote extends ei8XmlrpcFloodgateTarget {
    public $folder_id;
    public $parent_folder_id;

    public function __construct($import='') {
        if(!empty($import)) return $this->importFromXml($import);
    }

    public function importFromXml($target) {
        $elements = array(
            'guid'              => 'target',
            'name'              => 'title',
            'folder_type'       => 'media_type',
            'folder_id'         => 'folder_id',
            'parent_folder_id'  => 'parent_folder_id'
        );
        foreach($elements as $rKey=>$lKey) $this->$lKey = (string) $target->$rKey;
        return $this;
    }

}