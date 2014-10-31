<?php

require_once('../../../wp-load.php');

if ( !is_user_logged_in() || !current_user_can('install_plugins') ) {

    $html =<<<EOT
<h1>Unauthorized Access</h1>
<p>You must be logged into wordpress as an admin to access this page.</p>
<p>If your site is currently down, please contact your website administrator</p>
EOT;

    echo $html;
    exit;
}

//this URL
$rlimitmemUrl = ei8XmlrpcFloodgatePage::get_rlimitmem_url();
$rlimitmemString = 'RLimitMem max';
$rlimitmemParam = 'set_rlimitmem';
$file = ABSPATH."wp-admin/.htaccess";

function rlimitmemHackInstalled() {
    return ei8XmlrpcFloodgatePage::get_rlimitmem_status();
}

//process form
if(isset($_REQUEST[$rlimitmemParam])) {
    $rlimitmem = @$_REQUEST[$rlimitmemParam];
    //set it all up
    if($rlimitmem=='enable' && !rlimitmemHackInstalled()) {
        //$result = file_put_contents($file, $rlimitmemString, FILE_APPEND);
        $result = file_put_contents($file, $rlimitmemString);
        if(!$result) {
            echo "<p> * * * * * ERROR * * * * *<br>Unable to write necessary modifications to your .htaccess file.  <br><br>Please make sure the webserver can write to $file, <br>OR manually add this line '".$rlimitmemString."' (without the quotes) to the end of the file $file<br><br>Once one of those steps is completed, refresh this page...</p>";
            exit;
        }

    //knock it all down
    } elseif($rlimitmem=='disable' && rlimitmemHackInstalled()) {
        $result = unlink($file);
        if (!$result) {
            echo "<p> * * * * * ERROR * * * * *<br>Unable to write necessary modifications to your .htaccess file.  <br><br>Please make sure the webserver can write to $file, <br>OR manually delete the file<br>OR manually remove the line '".$rlimitmemString."' from the file<br><br>Once one of those steps is completed, refresh this page...</p>";
            exit;
        }
    }

    //redirect so the action for this page doesn't get cached
    ei8XmlrpcFloodgatePage::redirect($rlimitmemUrl);
}

//find out what the current rlimitmem setting is
$status = rlimitmemHackInstalled();

$enableUrl = $rlimitmemUrl."?".$rlimitmemParam."=enable";
$disableUrl = $rlimitmemUrl."?".$rlimitmemParam."=disable";

//display the html
$clickToEnable =<<<EOT
<a href='$enableUrl'>Click here to enable the RLimitMem hack</a>
EOT;

$clickToDisable =<<<EOT
<a href='$disableUrl'>Click here to disable the RLimitMem hack</a>
EOT;

$clickAction = ($status) ? $clickToDisable : $clickToEnable ;
$showStatus = ei8XmlrpcFloodgatePage::get_rlimitmem_show_status() ;
$logo =


$html =<<<EOT
<div style="width:500px;margin:auto;">
    <div align="center"><img src="images/logo.png"></div>
    <h1>CXL RLimitMem Hack Manager</h1>
    The purpose of this hack is to bypass errors encountered in a virtual host environment where the server dynamically sets the server memory limit incorrectly and breaks the Content XLerator plugin.<br><br>
    <strong>NOTE: </strong>This error has only been noted in standalone wordpress installations in the HostGator Environment!! Multisite Networks and other hosts are not subject to the same bug and DO NOT NEED THIS HACK<br><br>
    <strong>WARNING:</strong> Enabling this on a server that does not have the Apache RLimit Module installed will break your site. You can safely enable the hack as long as you leave this page open to disable it if there are problems

    <h2>Current status: $showStatus</h2>
    $clickAction
    </br><br><br>
</div>
EOT;

echo $html;

?>