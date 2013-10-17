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
    private $db;

    public function __construct($id='') {
        global $wpdb;
        $this->db = &$wpdb;
        $this->table = $this->db->prefix . "ei8_floodgate_targets";
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
        $sql     = $this->db->prepare("SELECT * FROM $this->table WHERE ID=%d LIMIT 1", $id);
        $result = $this->db->get_results($sql);
        if($result===FALSE) ei8_xmlrpc_admin_log("<p style='color:red'><b>SQL ERROR: </b>\"".$this->db->last_error."\"(SQL: $sql)</p>");
        //echo "<p>RESULT:<pre>"; print_r($result); echo "</pre></p>";
        foreach($result[0] as $key=>$val) $this->$key = stripslashes($val);
        return $this;
    }

    public function create() {
        $sql = $this->db->prepare("INSERT INTO $this->table SET
                title ='%s',
                target ='%s',
                is_video = %d,
                video_order = %d,
                is_audio = %d,
                audio_order = %d,
                is_text = %d,
                text_order = %d,
                is_image = %d,
                image_order = %d",
            $this->title,
            addslashes($this->target),
            $this->is_video,
            $this->video_order,
            $this->is_audio,
            $this->audio_order,
            $this->is_text,
            $this->text_order,
            $this->is_image,
            $this->image_order
        );
        ei8_xmlrpc_admin_query($sql);
        $this->id=$this->db->insert_id;
        //$this->db->flush();
        return $this->id;
    }

    public function delete() {
        $sql = $this->db->prepare("DELETE FROM $this->table WHERE id=%d",
            $this->id
        );
        ei8_xmlrpc_admin_query($sql);
        //$this->db->flush();
        return $this->id;
    }

    public function update() {
        if(empty($this->id) || !is_numeric($this->id)) return $this->create();

        $sql = $this->db->prepare("UPDATE $this->table SET
                title ='%s',
                target ='%s',
                is_video = %d,
                video_order = %d,
                is_audio = %d,
                audio_order = %d,
                is_text = %d,
                text_order = %d,
                is_image = %d,
                image_order = %d
             WHERE id = %d",
            $this->title,
            addslashes($this->target),
            $this->is_video,
            $this->video_order,
            $this->is_audio,
            $this->audio_order,
            $this->is_text,
            $this->text_order,
            $this->is_image,
            $this->image_order,
            $this->id
        );
        ei8_xmlrpc_admin_query($sql);
        //$this->db->flush();
        return $this->id;
    }

    public function update_order($type,$position) {
        $col = $type.'_order';
        $sql = $this->db->prepare("UPDATE $this->table SET $col=%d WHERE id=%d", $position, $this->id);
        ei8_xmlrpc_admin_query($sql);
        //$this->db->flush();
        return $this->id;
    }


}
