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
    public $baseUrl = 'http://www.cxl1.net/api/';
    //public $baseUrl = 'http://www.ei8t.com/api/';
    //public $baseUrl = 'http://www.dev.ei8t.com/api/';

    public function __construct($guid='') {
        if(!empty($guid)) $this->guid=$guid;
    }

    public function buildUploadUrl($type,$guid) {
        $url = $this->baseUrl."upload/".$type."/".$guid."/";
        return $url;
    }

    public function getInfo($type,$guid='',$flush='') {
        //$flush=true;
        //echo "<p>Running ei8XmlrpcFloodgateAPI::getInfo() for type: $type, guid: $guid</p>";
        if(empty($guid)) $guid = $this->guid;
        $key = 'list/'.$type.'/'.$guid.'/';
        //first check to see if this key is cached
        $cache = new ei8XmlrpcFloodgateCache($key,$flush);
        $data=$cache->get();
        if($data && !empty($data)) {
            //echo "<p>FOUND CACHED DATA</p>";
            //data exists...load into object
            //$xml = simplexml_load_string($data);
            $xml = $this->load_xml($data);
        } elseif ( is_multisite() || !is_admin() ) {
            //echo "<p>NO cache...loading from remote</p>";
            //data doesn't exist...load from url
            $url = $this->baseUrl.$key;
            //$xml = simplexml_load_file(rawurlencode($url));
            $xml = $this->load_remote_xml($url);
            //cache the data for later retrieval
            $cache->set($xml->asXML());
        } else {
            //echo "<p>NO cache...loading from remote</p>";
            //first make sure the RLimit max is set
            //echo "<p>ABSPATH: ".ABSPATH."</p>";
            $externalDomain = false;
            $externalDomains = array('historicalhighlands.net','localwp');
            foreach($externalDomains as $dom) if(strstr($_SERVER['HTTP_HOST'],$dom)) {
                $externalDomain = $dom;
                break;
            }
            if($externalDomain) {
                //echo "<p>found external domain match for $externalDomain</p>";
            } else {
                //echo "<p>no domain match for $externalDomain (".$_SERVER['HTTP_HOST'].")</p>";
                $file = ABSPATH.".htaccess";
                $contents = file_get_contents($file);
                //echo "<p>.htaccess contents: <pre>"; print_r($contents); echo "</pre></p>";
                if(!strstr($contents,'RLimitMem max')) {
                    $result = file_put_contents($file, 'RLimitMem max', FILE_APPEND);
                    //echo "<p>.htaccess contents: <pre>"; print_r(file_get_contents($file)); echo "</pre></p>";
                    if($result) {
                        list($currentTab,$currentTitle,$ei8AdminUrl) = ei8_xmlrpc_floodgate_get_tab();
                        echo "<p><br>Added 'RLimitMem max' to .htaccess file<br>...redirecting to $ei8AdminUrl...</p>";
                        //force page reload
                        ei8XmlrpcFloodgatePage::redirect($ei8AdminUrl);
                    } else {
                        echo "<p> * * * * * ERROR * * * * *<br>Unable to write necessary modifications to your .htaccess file.  <br><br>Please make sure the webserver can write to $file, <br>OR add this line 'RLimitMem max' (without the quotes) to the end of the file $file<br><br>Once one of those steps is completed, refresh this page...</p>";
                        exit;
                    }
                }
            }

            //data doesn't exist...load from url
            $url = $this->baseUrl.$key;
            //echo "<p>url: $url</p>";
            //file_put_contents("temp.xml", file_get_contents($url));
            //$xml = simplexml_load_file("temp.xml");
            //$xml = simplexml_load_file(rawurlencode($url));
            $xml = $this->load_remote_xml($url);
            //cache the data for later retrieval
            $cache->set($xml->asXML());
            //should we reload the page here if admin?
            //echo "<p>LOADED NEW DATA FROM CXL1.NET</p>";

        }
        //parse the xml
        return $xml;
    }

    public function getAccountInfo($guid='',$flush='') {
        if(empty($guid)) $guid = $this->guid;
        if(empty($guid)) return false;
        return $this->getInfo('account',$guid,$flush);
    }

    public function getFolderInfo($guid='',$flush='') {
        if(empty($guid)) $guid = $this->guid;
        if(empty($guid)) return false;
        return $this->getInfo('folder',$guid,$flush);
    }

    public function getMediaInfo($guid='',$flush='') {
        if(empty($guid)) $guid = $this->guid;
        if(empty($guid)) return false;
        return $this->getInfo('media',$guid,$flush);
    }

    public static function load_remote($url) {
        //echo "<p>Remote load of url: $url</p>";
        $response = wp_remote_get($url);
        //echo "<p>response:<pre>"; print_r($response); echo "</pre></p>";
        $body     = wp_remote_retrieve_body($response);
        //echo "<p>body:<pre>"; print_r($body); echo "</pre></p>";
        return $body;
    }

    public static function load_remote_xml($url) {
        $body = self::load_remote($url);
        $xml  = self::load_xml($body);
        return $xml;
    }

    public static function load_xml($string='') {
        //echo "<p>loading string into xml: $string</p>";
        $xml = simplexml_load_string($string);
        //echo "<p>xml:<pre>"; print_r($xml); echo "</pre></p>";
        return $xml;
    }


}
