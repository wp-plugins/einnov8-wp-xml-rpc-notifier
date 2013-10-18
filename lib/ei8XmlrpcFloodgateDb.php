<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yipeecaiey
 * Date: 10/18/13
 * Time: 7:03 AM
 * To change this template use File | Settings | File Templates.
 */
class ei8XmlrpcFloodgateDb
{
    public $db;
    public $db_prefix;

    public function __construct() {
        global $wpdb;
        $this->db = &$wpdb;
        $this->db_prefix = $this->db->prefix;
    }

}
