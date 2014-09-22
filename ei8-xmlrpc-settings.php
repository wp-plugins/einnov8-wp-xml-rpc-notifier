<?php
//uncomment this line below to enable verbose install logging & display sql errors
//$ei8_xmlrpc_debug = 1;


//set up customFolders for submit pages
$customFolderSettings = array(
    'floodgate'     => 'Floodgate (default)',
    'testimonials'  => 'Testimonials',
    'podcast'       => 'Podcast',
    'resumes'       => 'Video Resumes',
    'twitter'       => 'Twitter',
    'video'         => 'Video Repositories (YouTube, Dailymotion)',
    'webtv'         => 'WebTV',
    'review'        => 'CXL Review',
    'custom'        => 'Custom',
);
$customFolderDefault = 'floodgate';
$customFolderPre     = 'ei8_xmlrpc_folder_';

function ei8_xmlrpc_getCustomFolders($admin=true) {
    global $customFolderSettings;
    $customFolders = array();
    foreach($customFolderSettings as $folder=>$title) $customFolders[$folder] = ei8_xmlrpc_getCustomFolder($folder,$admin);
    return $customFolders;
}

function ei8_xmlrpc_getCustomFolder($folder='',$admin=false) {
    global $customFolderSettings, $customFolderPre;
    if($folder!='') return array(
        'title' =>$customFolderSettings[$folder],
        'value' =>ei8_xmlrpc_getCustomFolderValue($folder,$admin),
        'var'   => $customFolderPre.$folder,
    );
    else return array(
        'title' => 'UNKNOWN',
        'value' => '',
        'var'   => '',
    );
}

function ei8_xmlrpc_getCustomFolderValue($folder,$admin=false) {
    global $customFolderDefault, $customFolderPre;
    $value = ei8_xmlrpc_get_option($customFolderPre.$folder);
    //see if we need to force the default value...buy default we do :)
    if (empty($value) && $admin==false) $value = ei8_xmlrpc_get_option($customFolderPre.$customFolderDefault);
    return $value;
}

function ei8_xmlrpc_storeCustomFolder($folder,$val) {
    global $customFolderPre;
    ei8_xmlrpc_update_option($customFolderPre.$folder, $val);
}

//Admin options for page navigation
$optionP = 'ei8-xmlrpc-options';
$optionE = 'ei8-xmlrpc-email-options';
$optionF = 'ei8-xmlrpc-floodgate-options';
$optionL = 'ei8-xmlrpc-legacy-options';




//set up floodgate options
//$floodgateOptionPre     = 'ei8_floodgate_';
$floodgateOptionSettings= array(
    //$name         => array($title,$default_value,$extra),
    'name'          => array('Floodgate URL', 'floodgate2', 'ie: http://yoursite.com/<span style="color:red">floodgate2</span>/'),
    'pass'          => array('Floodgate Password', 'floodgate'),
    'acct_guid'     => array('cxl1.net Account GUID', '','ie: 8hjGfHJCkKJ'),
    'client_name'   => array('Client\'s Name', 'Client\'s Name'),
    'reseller_name' => array('Reseller Name', 'FLOODtech'),
    'logo'          => array('Company Logo', ei8_plugins_url('/images/logo.png'), 'ie: http://yoursite.com/images/logo.png'),
);
$floodgateOptionDefaults= array();
foreach($floodgateOptionSettings as $key=>$vals) $floodgateOptionDefaults[$key]=$vals[1];
$floodgateOptions = array_keys($floodgateOptionDefaults);

$floodgateMediaTypes = array(
    'video' => 'Video',
    'audio' => 'Audio',
    'image' => 'Image',
    'text'  => 'Text'
);

function ei8_xmlrpc_floodgate_get_media_types($getAll='') {
    global $floodgateMediaTypes;
    if(empty($getAll)) return $floodgateMediaTypes;
    $myFgTypes = array_merge(
        array('home'=>'Home'),
        $floodgateMediaTypes,
        array('support'=>'Support')
    );
    return $myFgTypes;
}

function ei8_xmlrpc_build_floodgate_option_name($name){
    $option = new ei8XmlrpcFloodgateOptionFG();
    return $option->build_name($name);
}

function ei8_xmlrpc_get_floodgate_option($name) {
    global $floodgateOptionDefaults;
    $option = new ei8XmlrpcFloodgateOptionFG();
    $value = $option->get($name);
    //$value = ei8_xmlrpc_get_option(ei8_xmlrpc_build_floodgate_option_name($name));
    //see if we need to force the default value...buy default we do :)
    if (empty($value) && ($admin==false || $name=='name')) $value = $floodgateOptionDefaults[$name];
    //echo "<p>ei8_xmlrpc_get_floodgate_option ( $name ) :: ".ei8_xmlrpc_build_floodgate_option_name($name)." ($value)</p>";
    return stripslashes($value);
}

function ei8_xmlrpc_update_floodgate_option($name,$val) {
    $option = new ei8XmlrpcFloodgateOptionFG($name,$val);
    return $option->update();
    //return ei8_xmlrpc_update_option(ei8_xmlrpc_build_floodgate_option_name($name), $val);
}

?>