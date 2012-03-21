<?php
/** Include the bootstrap for setting up WordPress environment */
@include('../../../wp-load.php');

//in case the bootstrap didn't load
if(!function_exists('admin_url')) {
    function admin_url($url) {
        return "../../../wp-admin/".$url;
    }
}

//send back to plugin
//$url = "options-general.php?page=einnov8-wp-xml-rpc-notifier/ei8-xmlrpc-notifier.php";
$url = "admin.php?page=ei8-xmlrpc-options";
$url .= "&oauth_token=".$_GET['oauth_token'];
$url .= "#ei8xmlrpctwittersettings";
$url = admin_url($url);
header("Location: ".$url);

echo "<p>Redirecting to $url</p>";

?>