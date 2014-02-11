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
    //TODO Make sure this is connecting to the correct dev/live API
    public $baseUrl = 'http://www.ei8t.com/api/';
    //public $baseUrl = 'http://www.dev.ei8t.com/api/';

    public function __construct($guid='') {
        if(!empty($guid)) $this->guid=$guid;
    }

    public function buildUploadUrl($type,$guid) {
        $url = $this->baseUrl."upload/".$type."/".$guid."/";
        return $url;
    }

    public function getInfo($type,$guid='') {
        if(empty($guid)) $guid = $this->guid;
        $url = $this->baseUrl.'list/'.$type.'/'.$guid.'/';
        //load the xml from the url
        $xml = simplexml_load_file(rawurlencode($url));
        //parse the xml
        return $xml;
    }

    public function getAccountInfo($guid='') {
        if(empty($guid)) $guid = $this->guid;
        if(empty($guid)) return false;
        return $this->getInfo('account',$guid);
    }

    public function getFolderInfo($guid='') {
        if(empty($guid)) $guid = $this->guid;
        if(empty($guid)) return false;
        return $this->getInfo('folder',$guid);
    }

    public function getMediaInfo($guid='') {
        if(empty($guid)) $guid = $this->guid;
        if(empty($guid)) return false;
        return $this->getInfo('media',$guid);
    }


}
