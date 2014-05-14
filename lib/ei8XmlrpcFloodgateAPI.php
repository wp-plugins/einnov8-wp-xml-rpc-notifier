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
        //echo "<p>Running ei8XmlrpcFloodgateAPI::getInfo() for type: $type, guid: $guid</p>";
        if(empty($guid)) $guid = $this->guid;
        $key = 'list/'.$type.'/'.$guid.'/';
        //first check to see if this key is cached
        $cache = new ei8XmlrpcFloodgateCache($key);
        $data=$cache->get();
        if($data && !empty($data)) {
            //echo "<p>FOUND CACHED DATA</p>";
            //data exists...load into object
            $xml = simplexml_load_string($data);
        } else {
            //echo "<p>NO cache...loading from remote</p>";
            //data doesn't exist...load from url
            $url = $this->baseUrl.$key;
            $xml = simplexml_load_file(rawurlencode($url));
            //cache the data for later retrieval
            $cache->set($xml->asXML());
        }
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
