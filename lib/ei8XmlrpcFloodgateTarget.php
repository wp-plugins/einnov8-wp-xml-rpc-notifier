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
    public $is_video;
    public $video_order;
    public $is_audio;
    public $audio_order;
    public $is_text;
    public $text_order;
    public $is_image;
    public $image_order;

    private $table;

    public function __construct($id='') {
        $this->table = new ei8XmlrpcFloodgateDbTableTargets();
        if($id!='') $this->get($id);
        return $this;
    }

    public function import($data,$update='') {
        foreach($data as $key=>$val) $this->$key=$val;
        if($update!='') $this->update();
    }

    public function is_video() {
        return (boolean) $this->is_video;
    }

    public function is_audio() {
        return (boolean) $this->is_audio;
    }

    public function is_text() {
        return (boolean) $this->is_text;
    }

    public function is_image() {
        return (boolean) $this->is_image;
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


}
