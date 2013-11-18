<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yipeecaiey
 * Date: 11/12/13
 * Time: 10:52 PM
 * To change this template use File | Settings | File Templates.
 */
class ei8XmlrpcFloodgateAPI
{
    public $guid;
    public $baseUrl = 'http://www.dev.ei8t.com/api/';

    public function __construct($guid='') {
        if(!empty($guid)) $this->guid=$guid;
    }

    public function getInfo($type) {
        $url = $this->baseUrl.'list/'.$type.'/'.$this->guid.'/';
        //load the xml from the url
        $xml = simplexml_load_file(rawurlencode($url));
        //parse the xml
        return $xml;
    }

    public function getAccountInfo($guid='') {
        if(empty($guid)) $guid = $this->guid;
        if(empty($guid)) return false;
        return $this->getInfo('account');
    }

    public function getFolderInfo($guid='') {
        if(empty($guid)) $guid = $this->guid;
        if(empty($guid)) return false;
        return $this->getInfo('folder');
    }

    public function getMediaInfo($guid='') {
        if(empty($guid)) $guid = $this->guid;
        if(empty($guid)) return false;
        return $this->getInfo('media');
    }


}
