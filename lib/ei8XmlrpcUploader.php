<?php

class ei8XmlrpcUploader extends ei8XmlrpcFloodgateFormUploader
{
    public function __construct($vars)
    {
        //parse the vars from the shortcode (should look something like 'v=8mGCvmv3X a=d3hQHKcR8DR')
        parse_str($vars,$parts);
        $myVars = array();

        //first see if there is a custom folder set
        //if so...retrieve the values and set them here
        if(in_array('cf',array_keys($parts))) {
            $oVars = ei8_xmlrpc_string_to_array(ei8_xmlrpc_getCustomFolderValue($parts['cf']));
            foreach($oVars as $k=>$v) $myVars[$k] = "$k=$v";
        }
        //now overwrite any cf preset values with any other manual values passed
        foreach($parts as $key=>$val) {
            if($key=='cf') continue;
            $myVars[$key] = "$key=$val";
        }
        parent::__construct('multi',implode('&',$myVars),'');

        //make sure we are including the uploadfile javascript
        wp_enqueue_script( 'jquery-uploadfile' );
        wp_enqueue_script( 'floodgate' );
    }

    public function render() {
        $html = parent::render();

        $showSuccess    = ei8_xmlrpc_conf_message(true,'%title%','%msg%',false);
        $showError      = ei8_xmlrpc_conf_message(false,'%title%','%msg%',false);
        $successDefaults= ei8_xmlrpc_conf_message_defaults(true);
        $errorDefaults  = ei8_xmlrpc_conf_message_defaults(false);

        $html .=<<<EOT
            <script type="text/javascript">
                //set some things up for the floodgate.js
                var showSuccess         = '$showSuccess';
                var showError           = '$showError';
                var successDefaultTitle = '$successDefaults[0]';
                var successDefaultMsg   = '$successDefaults[1]';
                var errorDefaultTitle   = '$errorDefaults[0]';
                var errorDefaultMsg     = '$errorDefaults[1]';
            </script>

EOT;
        return $html;

    }
}
