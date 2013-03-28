<?php

//set up customFolders for submit pages
$customFolderSettings = array(
    'floodgate'     => 'Floodgate (default)',
    'testimonials'  => 'Testimonials',
    'podcast'       => 'Podcast',
    'resumes'       => 'Video Resumes',
    'twitter'       => 'Twitter',
    'video'         => 'Video Repositories (YouTube, Dailymotion)',
    'webtv'         => 'WebTV',
    'review'        => 'eInnov8 Review',
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

?>