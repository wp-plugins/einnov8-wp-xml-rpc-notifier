<?php
/** Include the bootstrap for setting up WordPress environment */
require('../../../wp-load.php');

//send back to plugin
//$url = "options-general.php?page=einnov8-wp-xml-rpc-notifier/ei8-xmlrpc-notifier.php";
$url = "admin.php?page=einnov8-wp-xml-rpc-notifier/ei8-xmlrpc-notifier.php";
$url .= "&oauth_token=".$_GET['oauth_token'];
$url .= "#ei8xmlrpctwittersettings";
$url = admin_url($url);
header("Location: ".$url);

echo "<p>Redirecting to $url</p>";

?>