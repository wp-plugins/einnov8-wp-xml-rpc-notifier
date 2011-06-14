<?php
/*
Plugin Name: eInnov8 WP XML-RPC Notifier
Plugin URI: http://wordpress.org/extend/plugins/einnov8-wp-xml-rpc-notifier/
Plugin Description: Custom settings for posts received via XML-RPC.
Version: 2.1.2
Author: Tim Gallaugher
Author URI: http://wordpress.org/extend/plugins/profile/yipeecaiey
License: GPL2 

Copyright 2010 eInnov8 Marketing  (email : timg@einnov8.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//uncomment this line for testing/debugging purposes
//define('WP_DEBUG', true);

// internationalization 
//load_plugin_textdomain('ei8-xmlrpc-notifier', ei8_xmlrpc_get_plugin_url()."/languages/");

//white list options
/*function ei8_xmlrpc_register_settings() {
    register_setting('general', 'ei8_xmlrpc_post_status');
    register_setting('general', 'ei8_xmlrpc_email_notify');
    //register_setting('general', 'ei8_xmlrpc_ping');
}
add_action('init', 'ei8_xmlrpc_register_settings');
*/

//whitelist variable names
//No longer needed as we have moved away from standard wp options storage to custom db table so that editors can update necessary settings
/*
function ei8_xmlrpc_register_settings() {
    register_setting('ei8_xmlrpc','ei8_xmlrpc_email_notify');
    register_setting('ei8_xmlrpc','ei8_xmlrpc_post_status');
    register_setting('ei8_xmlrpc','ei8_xmlrpc_ping');
}
add_action('admin_init', 'ei8_xmlrpc_register_settings');
*/

//process new posts
function ei8_xmlrpc_publish_post($post_id) {
    global $wpdb;
    
    //load the post object 
    $post = get_post($post_id);
    
    //check if email should be sent
    $tEmail = ei8_xmlrpc_get_option('ei8_xmlrpc_email_notify');
    if(!empty($tEmail)) ei8_email_notify($post_id, $tEmail);
     
    //force status update
    $tStatus = ei8_xmlrpc_get_option('ei8_xmlrpc_post_status');
    if(!empty($tStatus)) ei8_update_post_status($post_id, $tStatus);
    
    //check if ping should be sent
    $tPing = ei8_xmlrpc_get_option('ei8_xmlrpc_ping');
    if(!empty($tPing)) ei8_add_ping($post_id, $tPing);
    
    //exit quietly
   	die();
}
add_action('xmlrpc_publish_post', 'ei8_xmlrpc_publish_post');

function ei8_update_post_status($post_id, $tStatus) {
    global $wpdb;
    $wpdb->query( "UPDATE $wpdb->posts SET post_status = '$tStatus' WHERE ID = '$post_id'" );
}

function ei8_email_notify($post_id, $tEmail) {
    $post       = get_post($post_id);
    $blogname   = ei8_xmlrpc_get_blog_option('blogname');
    $tStatus    = ei8_xmlrpc_get_option('ei8_xmlrpc_post_status');
    $siteType   = ei8_xmlrpc_get_site_type();
    $author     = get_userdata($post->post_author);
    $nonce      = wp_create_nonce('my-nonce');
    
    //use this function to populate all message settings...this handles empty variables nicely
    $message_settings = ei8_xmlrpc_get_message_settings();
    
    //begin with message intro
    $message = $message_settings['message_intro'] . "\r\n\r\n";
    
    //build message re publishing status
    $message .= $message_settings['message_post_status_intro'];
    $pStatus = ($tStatus=='draft' || $tStatus=='publish') ? $tStatus : 'unknown';
    $message .= $message_settings['message_post_status_'.$pStatus] . "\r\n\r\n";
    
    //add the thank you message
    $message .= $message_settings['message_thank_you'] . "\r\n\r\n";
    
    //handle quick links if we should
    if($message_settings['message_quick_links_show']==1) {
        $message .= "-------------------------------------\r\n\r\n";
        $message .= $message_settings['message_quick_links_intro'] ."\r\n\r\n";
    
        //$message .= sprintf( __('Preview: %s'), get_permalink($post_id) ) . "\r\n\r\n";
        //$message .= sprintf( __('Edit: %s'), admin_url("post.php?action=edit&post=$post_id") ) . "\r\n\r\n";
        $message .= admin_url("post.php?action=edit&post=$post_id") . "\r\n\r\n";
        //$message .= sprintf( __('Delete: %s'), admin_url("post.php?action=delete&post=$post_id&_wpnonce=$nonce") ) . "\r\n";
        //$message .= sprintf( __('Spam it: %s'), admin_url("comment.php?action=cdc&dt=spam&c=$comment_id") ) . "\r\n";
        $siteName = ei8_xmlrpc_get_site_type_name($siteType);
        //if($siteType=="flood") $message .= sprintf( __('Update '.$siteName.' system settings: %s'), admin_url("options-general.php?page=" . plugin_basename( __FILE__ ) ) ) . "\r\n\r\n";
    }
    
    //handle referral text if we should
    if($message_settings['message_referral_show']==1) {
        $message .= "-------------------------------------\r\n\r\n";
        $message .= $message_settings['message_referral_text'] . "\r\n";
    }
    
    //filter custom tags
    $message = str_replace("[[post_title]]",$post->post_title,$message);
    
    $from    = sprintf("From: \"%s\" <%s>", $message_settings['email_from_name'], $message_settings['email_from_addr']);
    $subject = $message_settings['email_subject'];
    
    $message_headers = "$from\n"
        . "Content-Type: text/plain; charset=\"" . ei8_xmlrpc_get_blog_option('blog_charset') . "\"\n";

//    if ( isset($reply_to) )
//        $message_headers .= $reply_to . "\n";

    if(strstr($tEmail,',')) {
        $tEmails = explode(',',$tEmail);
        foreach($tEmails as $tEmail) @wp_mail(trim($tEmail), $subject, $message, $message_headers);
    } else @wp_mail($tEmail, $subject, $message, $message_headers);
}

function ei8_add_ping($post_id, $tPing) {
    add_ping($post_id, $tPing);
}

/*
 * FILTER FRONT END DISPLAY
*/

function ei8_xmlrpc_swf_wrap($url,$height,$width) {
    $html =<<<EOT
<p><object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="$width" height="$height" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="wmode" value="transparent" /><param name="src" value="$url" /><param name="allowfullscreen" value="true" /><embed type="application/x-shockwave-flash" width="$width" height="$height" src="$url" wmode="transparent" allowscriptaccess="always" allowfullscreen="true"></embed></object></p>
EOT;
    return $html;
}

function ei8_xmlrpc_recorder_wrap($type, $vars='') {
    $service = "http://www.ei8t.com/";
    $doError = false;
    if(empty($vars)) $doError = true;
    switch($type) {
        case 'mini' :
            $folder = "swfmini";
            $height = 355;
            $width  = 340;
            break;
        case 'tall' :
            $folder = "swftall";
            $height = 440;
            $width  = 340;
            break;
        case 'wide' :
            $folder = "swfrec";
            $height = 360;
            $width  = 650;
            break;
        case 'media' :
            $folder = "uploader";
            $height = 300;
            $width  = 350;
            break;
        default :
            $doError = true;
    } 
    if($doError){
        $showType = ucwords($type);
        $html = "<p style='color: red; size: 13px; font-weight: bold;'>ERROR LOADING eInnov8 Tech $showType Recorder - please notify website administrator support@einnov8.com</p>";
    } elseif($type=="media") {
        parse_str($vars);
        $url = "{$service}{$folder}/{$a}/{$v}/";
        $html = "<iframe src ='$url' width='$height' height='$width' frameborder='0'><p>Your browser does not support iframes.</p></iframe>";
    } else {
        $url = "{$service}{$folder}/{$vars}";
        $html = ei8_xmlrpc_swf_wrap($url,$height,$width);
    }
    return $html;
}
    

function ei8_xmlrpc_get_plugin_dir() {
    $pathinfo = pathinfo( plugin_basename( __FILE__ ) );
    $pluginDir = "/wp-content/plugins/" . $pathinfo['dirname'] . '/';
    return $pluginDir;
}


function ei8_xmlrpc_get_plugin_url() {
    $wpurl = get_bloginfo('wpurl');
    return $wpurl . ei8_xmlrpc_get_plugin_dir();
}


function ei8_xmlrpc_conf_message($success=true,$title='default',$text='default') {
    if($title == 'default') $title  = "Submission Received";
    if($text == 'default')  $text   = "Your submission was saved successfully and will be processed shortly.";
    
    $pluginDir = ei8_xmlrpc_get_plugin_url();
    $confImg   = ($success) ? "success.png" : "error.png";    
    $title     = ($success) ? "<span style='color:red;'>$title</span>" : $title ;
    
    $confMessage =<<<EOT
<div style='border:1px solid #CCC; padding:5px; height: 70px; background-color: #E5EEE1;'>
    <img src="{$pluginDir}{$confImg}" align="left" style="padding-right: 10px;">
    <strong>$title</strong><br>$text
</div>
EOT;
    
    return $confMessage;
}

function ei8_xmlrpc_filter_tags($content) {
    $siteType  = ei8_xmlrpc_get_site_type();

    $ei8tVars          = ei8_xmlrpc_get_option('ei8_xmlrpc_recorder_vars');
    $ei8tMiniRecorder  = ei8_xmlrpc_recorder_wrap('mini', $ei8tVars);
    $ei8tTallRecorder  = ei8_xmlrpc_recorder_wrap('tall', $ei8tVars);
    $ei8tWideRecorder  = ei8_xmlrpc_recorder_wrap('wide', $ei8tVars);
    $ei8tMediaUploader = ei8_xmlrpc_recorder_wrap('media', $ei8tVars);

    if(1==ei8_xmlrpc_get_option('ei8_xmlrpc_use_captcha')) {
        $captchaSubmitForm = '<tr>
        <td><img src="'.ei8_xmlrpc_get_plugin_url().'php_captcha.php" alt="" /></td>
        <td>Please enter the code you see: <input id="\&quot;number\&quot;/" name="number" type="text" /></td>
    </tr>';
    } else $captchaSubmitForm = '';


    $submitFormLink   = ei8_xmlrpc_get_plugin_url() . "contentsave.php";
    $textBoxTitle     = ($siteType=="flood") ? "Text Box" : "Comment";
    $aName            = "ei8xmlrpcsimplesubmit";
    $showConf         = ($_REQUEST['success']==$aName) ? ei8_xmlrpc_conf_message() : "" ;
    $simpleSubmitForm =<<<EOT
<a name="$aName"></a>
$showConf
<form action="$submitFormLink" enctype="multipart/form-data" method="post">
<table class="text" border="0" width="95%">
    <tbody>
    <tr>
        <td>Title</td>
        <td><input name="title" size="40" type="text" /></td>
    </tr>
    <tr valign="top">
        <td valign="top">{$textBoxTitle}:</td>
        <td><textarea cols="40" rows="10" name="comment"></textarea></td>
    </tr>
    <tr>
        <td colspan="2">Select image file to send: <input name="uploadedfile" type="file" /></td>
    </tr>
    $captchaSubmitForm
    <tr>
        <td align="center"></td>
        <td align="center"></td>
    </tr>
    <tr>
        <td></td>
        <td> <input type="hidden" name="fileaction" value="embed_image"><input type="hidden" name="ei8_xmlrpc_a" value="$aName"><input name="Submit" type="submit" value="Save" /> <input onclick="javascript:reset();" type="button" value="Cancel" /></td>
    </tr>
    </tbody>
</table>
</form>
EOT;
    if(empty($submitFormLink)) $simpleSubmitForm = "<p style='color: red; size: 13px; font-weight: bold;'>ERROR LOADING Simple Submit Form - please notify website administrator</p>";

    $aName            = "ei8xmlrpcattachmentsubmit";
    $showConf         = ($_REQUEST['success']==$aName) ? ei8_xmlrpc_conf_message() : "" ;
    $attachmentSubmitForm =<<<EOT
<a name="$aName"></a>
$showConf
<form action="$submitFormLink" enctype="multipart/form-data" method="post">
<table class="text" border="0" width="95%">
    <tbody>
    <tr>
        <td>Title</td>
        <td><input name="title" size="40" type="text" /></td>
    </tr>
    <tr>
        <td>Description:</td>
        <td><input name="comment" size="40" type="text" /></td>
    </tr>
    <tr>
        <td colspan="2">Select file to upload: <input name="uploadedfile" type="file" /></td>
    </tr>
    $captchaSubmitForm
    <tr>
        <td align="center"></td>
        <td align="center"></td>
    </tr>
    <tr>
        <td></td>
        <td> <input type="hidden" name="fileaction" value="attached_doc"><input type="hidden" name="ei8_xmlrpc_a" value="$aName"><input name="Submit" type="submit" value="Save" /> <input onclick="javascript:reset();" type="button" value="Cancel" /></td>
    </tr>
    </tbody>
</table>
</form>
EOT;
    if(empty($submitFormLink)) $attachmentSubmitForm = "<p style='color: red; size: 13px; font-weight: bold;'>ERROR LOADING Attachment Submit Form - please notify website administrator</p>";

    $aName = "ei8xmlrpctwitterform";
    if ($_REQUEST['success']==$aName) {
        $showConf = ($_REQUEST['errorMessage']) ? ei8_xmlrpc_conf_message(false,'Twitter Error',$_REQUEST['errorMessage']) : ei8_xmlrpc_conf_message(true,'Success','Your twitter status has been updated');
    } else $showConf = "" ;
    $twitterForm =<<<EOT
<style>
#bar
{
background-color:#5fbbde;
width:0px;
height:16px;
}
#barbox
{
float:left;
height:16px;
background-color:#FFFFFF;
width:100px;
border:solid 2px #000;
margin-left:3px;
-webkit-border-radius:5px;-moz-border-radius:5px;
}
#count, #counter
{
float:left;
margin-left:8px;
margin-right:8px;
font-family:'Georgia', Times New Roman, Times, serif;
font-size:15px;
font-weight:bold;
color:#666666
}

#character-count {
//margin-right:300px;
}
</style>
<a name="$aName"></a>
$showConf
<form action="$submitFormLink" enctype="multipart/form-data" method="post">
<table class="text" border="0" width="95%">
    <tbody>
    <tr>
        <td colspan=2>
            <textarea  name="ei8_xmlrpc_tweet" cols="50" rows="5" id="tweet" ></textarea>
            
        </td>
    </tr>
    $captchaSubmitForm
    <tr>
        <td align="center"></td>
        <td align="center"></td>
    </tr>
    <tr>
        <td colspan=2>
            <div id="counter">Character count:</div>
            <div id="barbox"><div id="bar"></div></div>
            <div id="count">140</div>
            <input type="hidden" name="ei8_xmlrpc_twitter_post" value="1"><input type="hidden" name="ei8_xmlrpc_a" value="$aName"><input name="Submit" type="submit" value="Tweet" /> <input onclick="javascript:reset();" type="button" value="Clear Form" /></td>
    </tr>
    </tbody>
</table>
</form>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function()
{
$("#tweet").keyup(function()
{
var box=$(this).val();
var main = box.length *100;
var value= (main / 140);
var count= 140 - box.length;

$('#count').html(count);
if(box.length <= 140)
{
$('#bar').css("background-color", "#5fbbde");
$('#bar').css("width",value+'%');
}
else
{
$('#bar').css("width",'100%');
$('#bar').css("background-color", "#f11");
alert('Character Limit Exceeded!');
}
return false;
});

});
</script>
EOT;
    $twitterToken  = ei8_xmlrpc_get_option('ei8_xmlrpc_twitter_token');
    $twitterSecret = ei8_xmlrpc_get_option('ei8_xmlrpc_twitter_secret');
    
    
    if(empty($submitFormLink) OR empty($twitterToken) OR empty($twitterSecret)) $twitterForm = "<p style='color: red; size: 13px; font-weight: bold;'>ERROR LOADING Twitter Form - please notify website administrator</p>";

    $twitterButton =<<<EOT
<a href="http://twitter.com/share" class="twitter-share-button" data-text="Enter Your Tweet Here" data-count="none">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
EOT;

    $content = str_replace('[[Load MiniRecorder]]', $ei8tMiniRecorder, $content);
    $content = str_replace('[[Load WideRecorder]]', $ei8tWideRecorder, $content);
    $content = str_replace('[[Load TallRecorder]]', $ei8tTallRecorder, $content);
    $content = str_replace('[[Load Simple Submit Form]]', $simpleSubmitForm, $content);
    $content = str_replace('[[Load Attachment Submit Form]]', $attachmentSubmitForm, $content);
    $content = str_replace('[[Load Twitter Button]]', $twitterButton, $content);
    $content = str_replace('[[Load Twitter Form]]', $twitterForm, $content);
    $content = str_replace('[[Load MediaUploader]]', $ei8tMediaUploader, $content);
    
    //deprecated
    $content = str_replace('[[Load Web Recorder]]', $ei8tWideRecorder, $content);
    $content = str_replace('[[Load PubClip MiniRecorder]]', $ei8tMiniRecorder, $content);
    $content = str_replace('[[Load Captcha Submit Form]]', $simpleSubmitForm, $content);
    
    //started, but not yet finished
    return $content;
}

add_filter( 'the_content', 'ei8_xmlrpc_filter_tags' );


/*
 *BEGIN ADMIN SECTION
*/

//validate data
add_action('admin_notices', 'ei8_xmlrpc_validate_data' );

function ei8_xmlrpc_validate_data($input) {
    //validate the email
    $tEmail = ei8_xmlrpc_get_option('ei8_xmlrpc_email_notify');
    if(!empty($tEmail) && !ei8_isValidEmails($tEmail)) {
        echo "<div id='akismet-warning' class='error fade'><b>At least one of these is not a valid email address.  Please fix or email notifications will not be sent. ($tEmail)</b></div>";
    }
    
    //validate the ping url
    $tPing = ei8_xmlrpc_get_option('ei8_xmlrpc_ping');
    if(!empty($tPing) && !ei8_isValidUrl($tPing)) {
        echo "<div id='akismet-warning' class='error fade'><b>This is not a valid URL to be pinged.  Please fix or ping notifications will not be sent. $tPing</b></div>";
    }
    
}

function ei8_isValidEmails($email){
    if(strstr($email,',')) {
        $emails = explode(',',$email);
        foreach ($emails as $piece) {
            if (!ei8_isValidEmail(trim($piece))) return false;
        }
        return true;
    } else return ei8_isValidEmail($email);
}

function ei8_isValidEmail($email){
	return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email);
}

function ei8_isValidUrl($url){
    if(strstr($url, ' ')) return false;
	return preg_match('/^http(s?):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $url);
}


//create admin link to settings from main app plugins page
add_filter( 'plugin_action_links', 'ei8_xmlrpc_settings_link', 10, 2 );

function ei8_xmlrpc_settings_link($links, $file) {  
    $file_name   = plugin_basename( __FILE__ );
	if ( $file == $file_name ) {
		array_unshift( $links, sprintf( '<a href="options-general.php?page=%s">%s</a>', $file_name, __('Settings') ) );
	}	
	return $links;
}


//create options page
add_action('admin_menu', 'ei8_xmlrpc_options_menu');

function ei8_xmlrpc_options_menu() {
    if(!function_exists('ei8_parent_menu')) {
        function ei8_parent_menu() {
            add_menu_page('eInnov8 Settings', 'eInnov8 Options', 'activate_plugins', 'einnov8', 'ei8_xmlrpc_admin_options');
        }
        ei8_parent_menu();
    }
    add_submenu_page( 'einnov8', 'eInnov8 XMLRPC Preferences', 'ei8t-xmlrpc Preferences', 'activate_plugins', __FILE__, 'ei8_xmlrpc_admin_options');
}


function ei8_xmlrpc_admin_options() {
    $postStatus      = ei8_xmlrpc_get_option('ei8_xmlrpc_post_status');
    $ei8AdminUrl     = "options-general.php?page=" . plugin_basename( __FILE__ );
    $defaultSettings = ei8_xmlrpc_get_message_defaults($siteType);
    
    if($_POST['action']=="update") {
        //print_r($_POST);
        $var = 'ei8_xmlrpc_post_status';
        ei8_xmlrpc_update_option($var, $_POST[$var]);
        
        $var = 'ei8_xmlrpc_email_notify';
        ei8_xmlrpc_update_option($var, $_POST[$var]);

        $var = 'ei8_xmlrpc_ping';
        ei8_xmlrpc_update_option($var, $_POST[$var]);
        
        if (current_user_can('level_8')) {     
            $var = 'ei8_xmlrpc_site_type';
            ei8_xmlrpc_update_option($var, $_POST[$var]);
                   
            $var = 'ei8_xmlrpc_recorder_vars';
            ei8_xmlrpc_update_option($var, ei8_xmlrpc_parse_recorder_vars($_POST[$var]));
            
            $var = 'ei8_xmlrpc_submit_form';
            ei8_xmlrpc_update_option($var, $_POST[$var]);
            
            $var = 'ei8_xmlrpc_use_captcha';
            ei8_xmlrpc_update_option($var, $_POST[$var]);
        
            if($_POST['ei8_xmlrpc_reset_to_defaults']==1) {
                $defaults = ei8_xmlrpc_get_message_defaults();
                foreach($defaults as $var=>$val) {
                    ei8_xmlrpc_update_option($var, $val);
                }
            } else {
                $vars = ei8_xmlrpc_get_message_variables();
                foreach($vars as $var) {
                    ei8_xmlrpc_update_option($var, $_POST[$var]);
                }
            }
        }
        
        $siteName = ei8_xmlrpc_get_site_type_name();
        ei8_xmlrpc_admin_log("<p>Your $siteName preferences have been updated.</p>",1);
        
        //echo "<div id='akismet-warning' class='updated fade'><p>$msg</p></div>";
        
        //force page reload
        if ( !headers_sent() ) {
			wp_redirect($ei8AdminUrl);
		} else {
			$ei8AdminUrl = admin_url($ei8AdminUrl);

?>

			<meta http-equiv="Refresh" content="0; URL=<?php echo $ei8AdminUrl; ?>">
			<script type="text/javascript">
			<!--
				document.location.href = "<?php echo $ei8AdminUrl; ?>"
			//-->
			</script>
			</head>
			<body>
			Sorry. Please use this <a href="<?php echo $ei8AdminUrl; ?>" title="New Post">link</a>.
			</body>
			</html>

<?php
		}
		exit();
        
        
    }

    $postStatus      = ei8_xmlrpc_get_option('ei8_xmlrpc_post_status');
?>
<div class="wrap">
	<?php screen_icon(); ?>
	
    <h2>Preferences:</h2>
    <form method="post" action="<?php echo $ei8AdminUrl; ?>">
    <?php wp_nonce_field('update-options'); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Status of posts upon receipt: </th>
            <td><select name='ei8_xmlrpc_post_status'>
                    <option value="" <?php if(empty($postStatus)) echo "SELECTED"; ?>>system default</option>
                    <option value="draft" <?php if('draft'==$postStatus) echo "SELECTED"; ?>>Draft</option>
                    <option value="publish" <?php if('publish'==$postStatus) echo "SELECTED"; ?>>Publish</option>
                </select></td>
        </tr>
        <tr valign="top">
            <th scope="row">Post-notification email address:</th>
            <td>
                <input type="text" name="ei8_xmlrpc_email_notify" size=55 value="<?php echo ei8_xmlrpc_get_option('ei8_xmlrpc_email_notify'); ?>" /><br><small>(separate multiple email addresses with commas)</small>
            </td>
        </tr>
        <!-- <tr valign="top">
            <th scope="row">Ping this url when an XML-RPC testimonial is received:</th>
            <td>
                <input type="text" name="ei8_xmlrpc_ping" size=55 value="<?php echo ei8_xmlrpc_get_blog_option('ei8_xmlrpc_ping'); ?>" />
            </td>
        </tr> -->
<?php 
    if (current_user_can('level_8')) {  
        $siteType           = ei8_xmlrpc_get_site_type();
        $useCaptcha         = ei8_xmlrpc_get_option('ei8_xmlrpc_use_captcha');
        $f_submitForm       = 'ei8_xmlrpc_submit_form';
        $v_submitForm       = ei8_xmlrpc_get_option($f_submitForm);
        $f_recorderVars     = 'ei8_xmlrpc_recorder_vars'; 
        $v_recorderVars     = ei8_xmlrpc_get_option($f_recorderVars);
        $f_twitterUser      = 'ei8_xmlrpc_twitter_username';
        $v_twitterUser      = ei8_xmlrpc_get_option($f_twitterUser);
        $f_twitterPass      = 'ei8_xmlrpc_twitter_password';
        $v_twitterPass      = ei8_xmlrpc_get_option($f_twitterPass);
        if(empty($v_submitForm)) $v_submitForm = '/submit/' ;
?> 
        <tr><td><h3>Admin Specific Settings</h3></td></tr>
        <tr valign="top">
            <th scope="row">Submission Form Tags:</th>
            <td>
                [[Load MiniRecorder]]<br>
                [[Load WideRecorder]]<br>
                [[Load TallRecorder]]<br>
                [[Load Simple Submit Form]]<br>
                [[Load Attachment Submit Form]]<br>
                [[Load Twitter Button]]<br>
                [[Load Twitter Form]]<br>
                [[Load MediaUploader]]
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Web Recorder Settings:</th>
            <td><?php echo ei8_xmlrpc_form_text($f_recorderVars,$v_recorderVars); ?><br>
                <small>ex. http://www.ei8t.com/swfmini/<span style="color: red;">v=8mGCvmv3X&amp;a=d3hQHKcR8DR</span></small>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Form-Submit redirect URL:</th>
            <td><?php echo ei8_xmlrpc_form_text($f_submitForm,$v_submitForm); ?><!-- <br>
                <small>This is where the user is sent after a form has been successfully submitted. <br>This CAN but does NOT HAVE TO be the original submit form location<br>
                    ex. /submit/ OR http://domain.com/subfolder/submit/ OR http://domain.com/confirmation/</small> -->
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><a name="ei8xmlrpctwittersettings"></a>Twitter Account:</th>
            <td>
<?php
        //handle twitter authentication
        
        $twitterToken  = ei8_xmlrpc_get_option('ei8_xmlrpc_twitter_token');
        $twitterSecret = ei8_xmlrpc_get_option('ei8_xmlrpc_twitter_secret');
                
        require 'lib/EpiCurl.php';
        require 'lib/EpiOAuth.php';
        require 'lib/EpiTwitter.php';
        require 'lib/secret.php';
        
        $twitterObj = new EpiTwitter($consumer_key, $consumer_secret);
        $twitterObj->setCallBack( ei8_xmlrpc_get_plugin_url() . "twitter_callback.php" );
        
        if($_REQUEST['resetTwitter']) {
            $twitterToken = $twitterSecret = "";
        	ei8_xmlrpc_update_option('ei8_xmlrpc_twitter_token', "");
            ei8_xmlrpc_update_option('ei8_xmlrpc_twitter_secret', "");    
            echo ei8_xmlrpc_conf_message(true,$title='Success',$text="Twitter connection reset");
        } elseif($_GET['oauth_token']) {
        	$twitterObj->setToken($_GET['oauth_token']);
        	$token = $twitterObj->getAccessToken();
        	$twitterToken  = $token->oauth_token;
            $twitterSecret = $token->oauth_token_secret;
            $twitterObj->setToken($twitterToken, $twitterSecret);
        	$twitterInfo= $twitterObj->get_accountVerify_credentials();
            //print("<p>TwitterObj: <pre>");
            //print_r($twitterObj);
            //print("</pre></p>");
            //print("<p>TwitterInfo: <pre>");
            //print_r($twitterInfo);
            //print("</pre></p>");
            //exit();
        	ei8_xmlrpc_update_option('ei8_xmlrpc_twitter_token', $twitterToken);
            ei8_xmlrpc_update_option('ei8_xmlrpc_twitter_secret', $twitterSecret);
            echo ei8_xmlrpc_conf_message(true,$title='Success',$text="Twitter connection established");
        }
        
        //echo ei8_xmlrpc_conf_message(false,$title='DEBUG Twitter connection settings',$text="token:$twitterToken secret:$twitterSecret");
        
        if(empty($twitterToken) || empty($twitterSecret)) {
        	//$token = $twitterObj->getAccessToken();
          	$url = $twitterObj->getAuthorizationUrl();
        	
            //print("<p>TwitterObj: <pre>");
            //print_r($twitterObj);
            //print("</pre></p>");
          	//$url .= (strstr($url,'?')) ? "&" : "?" ;
          	//$url .= "oauth_callback=".urlencode($ei8AdminUrl);
            echo "<a href='$url'>Authorize an account with Twitter</a>";
        } else {
            $twitterObj->setToken($twitterToken, $twitterSecret);
        	$twitterInfo= $twitterObj->get_accountVerify_credentials();
        	$twitterInfo->response;
            		
        	$username = $twitterInfo->screen_name;
        	$profilepic = $twitterInfo->profile_image_url;
        	
        /*    print("<p>TwitterObj: <pre>");
            print_r($twitterObj);
            print("</pre></p>");
            print("<p>TwitterInfo: <pre>");
            print_r($twitterInfo);
            print("</pre></p>");
        */    
            $resetUrl = $ei8AdminUrl."&resetTwitter=1#ei8xmlrpctwittersettings";
            echo "<img src='$profilepic' align='left' style='padding-right:10px;'> Screen name: $username <br><small><a href='$resetUrl'>Reset Twitter Credentials</a></small>";
        }
?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Require CAPTCHA on submit forms: </th>
            <td><?php echo ei8_xmlrpc_form_boolean('ei8_xmlrpc_use_captcha',$useCaptcha); ?></td>
        </tr>
        <tr><td><h3>Notification Email Settings</h3></td></tr>        
        <tr valign="top">
            <th scope="row">Website type: </th>
            <td><select name='ei8_xmlrpc_site_type'>
                    <option value="boon" <?php if('boon'==$siteType) echo "SELECTED"; ?>><?php echo ei8_xmlrpc_get_site_type_name('boon'); ?></option>
                    <option value="flood" <?php if('flood'==$siteType) echo "SELECTED"; ?>><?php echo ei8_xmlrpc_get_site_type_name('flood'); ?></option>
                    <option value="videoaudio" <?php if('videoaudio'==$siteType) echo "SELECTED"; ?>><?php echo ei8_xmlrpc_get_site_type_name('videoaudio'); ?></option>
                    <option value="soupedup" <?php if('soupedup'==$siteType) echo "SELECTED"; ?>><?php echo ei8_xmlrpc_get_site_type_name('soupedup'); ?></option>
                </select></td>
        </tr>
<?php
         $message_variables = ei8_xmlrpc_get_message_variables(1);
         $message_settings  = ei8_xmlrpc_get_message_settings($siteType);
         foreach($message_variables as $var=>$form_vars) {
             list($form_type,$form_title) = $form_vars;
             switch($form_type) {
                 case "boolean":
                     $form_input = ei8_xmlrpc_form_boolean($var,$message_settings[$var]);
                     break;
                 case "textarea":
                     $form_input = ei8_xmlrpc_form_textarea($var,$message_settings[$var]);
                     break;
                 case "text":
                 default:
                     $form_input = ei8_xmlrpc_form_text($var,$message_settings[$var]);
                     break;
             }
             $html = '<tr valign="top"><th scope="row">'.$form_title.':</th><td>'.$form_input.'</td></tr>';
             echo $html;
         }
?>
        <tr valign="top">
            <th scope="row">Return email text to default settings: </th>
            <td><input type="checkbox" name="ei8_xmlrpc_reset_to_defaults" value="1" onchange="this.form.submit();"></td>
        </tr>
<?php 
    } //end admin only options 
?>
    </table>    
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="page_options" value="ei8_xmlrpc_post_status,ei8_xmlrpc_email_notify,ei8_xmlrpc_ping" />
    <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
    </form>
</div>
<?php
}

function ei8_xmlrpc_form_text($var,$val) {
    $html = '<input type="text" name="'.$var.'" size=65 value="'.$val.'" />';
    return $html;
}

function ei8_xmlrpc_form_textarea($var,$val,$rows=3) {
    $html = '<textarea name="'.$var.'" rows="'.$rows.'" cols="50">'.$val.'</textarea>';
    return $html;
}

function ei8_xmlrpc_form_boolean($var,$val) {
    $selectNo  = ($val!=1) ? "SELECTED" : "" ;
    $selectYes = ($val==1) ? "SELECTED" : "" ;
    $html = "<select name='$var'>
                    <option value='2' $selectNo>No</option>
                    <option value=1 $selectYes>Yes</option>
                </select>";
    return $html;
}

function ei8_xmlrpc_get_blog_option($val) {
    global $wp_version;
    return ($wp_version >= 3) ? get_site_option($val) : get_blog_option($val) ;
} 

function ei8_xmlrpc_get_option($id) {
    global $wpdb;
    $wpdb->flush();
    $table   = $wpdb->prefix . "ei8_xmlrpc_options";
    $sql     = "SELECT option_value FROM $table WHERE option_name='$id' LIMIT 1";
    $results = $wpdb->get_results($sql);
    $result = stripslashes($results[0]->option_value);
    return $result;
}
    
function ei8_xmlrpc_update_option($id, $value) {
    global $wpdb;
    $table = $wpdb->prefix . "ei8_xmlrpc_options";
    //check first to see if the option already exists
    $sql = "SELECT ID FROM $table WHERE option_name='$id'";
    $results = $wpdb->get_results($sql);
    $option_id = $results[0]->ID;
    $value = addslashes($value);
    if(!empty($option_id)) {
        $sql = "UPDATE $table SET option_value='$value' WHERE ID='$option_id'";
    } else {
        $sql = "INSERT INTO $table SET option_name='$id', option_value='$value'
    ON DUPLICATE KEY UPDATE option_value='$value'";
    }
    $wpdb->query($sql);
    $wpdb->flush();
}

function ei8_xmlrpc_parse_recorder_vars($vars) {
    //parse as a url if it is one...
    if(strstr($vars,'http')) {
        $parts = parse_url($vars);
        $vars = $parts['path'];
    }
    //get down to the last slash
    if(strstr($vars,'/')) {
        $parts = explode('/', $vars);
        $vars = array_pop($parts);
    }
    //make sure there is no funny business going on
    $vars = urldecode(htmlspecialchars_decode($vars));
    return $vars;
}

function ei8_xmlrpc_get_site_type() {
    $siteType = ei8_xmlrpc_get_option('ei8_xmlrpc_site_type');
    if(empty($siteType)) {
        $domain = $_SERVER['HTTP_HOST'];
        if(ereg('1tme1',$domain))     $siteType = 'boon';
        elseif(ereg('1wcf1',$domain)) $siteType = 'flood';
        elseif(ereg('1vaf1',$domain)) $siteType = 'videoaudio';
        elseif(ereg('1blg1',$domain)) $siteType = 'soupedup';
        elseif(ereg('local',$domain)) $siteType = 'flood';
        else                          $siteType = 'boon';
    }
    return $siteType;
}

function ei8_xmlrpc_get_upload_dir($url='') {
    $uploadDirName  = "/telaric/";
    $uploadPath     = ei8_xmlrpc_get_blog_option( 'upload_path' );
    $uploadURL      = ei8_xmlrpc_get_blog_option( 'upload_url_path' );
    //if variables aren't set, try to get it a different way
    if(empty($uploadPath) || empty($uploadURL) ) {
        $uldir      = wp_upload_dir();
        $uploadPath = $uldir['basedir']; 
        $uploadURL  = $uldir['baseurl']; 
    }
    //if it is still not set, set it manually from where it *should* be
    if(empty($uploadPath) || empty($uploadURL)) {
        $uldir      = "/wp-content/uploads";
        $uploadURL  = ei8_xmlrpc_get_home_url() . $uldir ;
        list($uploadPath) = explode("/wp-content",$_SERVER["SCRIPT_FILENAME"]);
        list($uploadPath) = explode("/wp-admin",$uploadPath);
        $uploadPath .= $uldir ;        
    }
    //ei8_xmlrpc_admin_log("<p>TROUBLESHOOTING: uploads directory <br>".$uploadPath."</p>",1);
    $uploadPath    .= $uploadDirName ;
    $uploadURL     .= $uploadDirName ;
    return (empty($url)) ? $uploadPath : $uploadURL ;
}

function ei8_xmlrpc_get_login() {
    require_once ( ABSPATH . WPINC . '/registration.php' );
    
    //set username
    $userName = "xmlrpc";
    
    //determine what pass SHOULD be
    $passWord = "";
    foreach(explode(".",$_SERVER['HTTP_HOST']) AS $part) $passWord = $part.$passWord;  
    $passWord = "xmlrpc".$passWord;
    
    //ensure user exists and has the correct password and permissions
    $userID = username_exists( $userName );
    $userInfo = array(
        'ID'         => $userID,
        'user_login' => $userName,
        'user_pass'  => $passWord,
        'role'       => 'author'
    );
    //check user permissions if user exists
    if($userID) $userData = get_userdata($userID);
    
    //only do the update if necessary 
    if( !$userID || $userData->user_pass!=$userInfo->user_pass || $userData->user_level!=2 ) wp_update_user( $userInfo );
    
    return array($userName, $passWord);
}
    
function ei8_xmlrpc_get_site_type_name($siteType='') {
    if(empty($siteType)) $siteType = ei8_xmlrpc_get_site_type();
    //$siteName = ($siteType=="flood") ? 'webcontentFLOOD' : 'testiBOONials' ;
    switch ($siteType) {
        case "flood":
            $siteName = "webcontentFLOOD";
            break;
        case "videoaudio":
            $siteName = "Video-Audio Forums";
            break;
        case "soupedup":
            $siteName = "Souped-Up Blogs";
            break;
        case "boon":
        default:
            $siteName = "testiBOONials";
    }
            
    return $siteName;
}

function ei8_xmlrpc_get_message_defaults($siteType='') {
    $defaults = array();
    if(empty($siteType)) $siteType = ei8_xmlrpc_get_site_type();
    $siteName = ei8_xmlrpc_get_site_type_name($siteType);
    list($referral_id) = explode('.',$_SERVER['HTTP_HOST']);
    
    if($siteType=="flood") {
        $defaults['email_from_name']             = "Web Content Flood";
        $defaults['email_from_addr']             = "submit@webcontentflood.com";
        $defaults['email_subject']               = "New [Customer Name] Submission";
        $defaults['message_intro']               = "[Customer Name] has made a new flood submission:        
[[post_title]]";
        
        $defaults['message_post_status_intro']   = "This submission";
        $defaults['message_post_status_draft']   = " is waiting for review within the customer's WCF account."; 
        $defaults['message_post_status_publish'] = " has been published to the customer's WCF public page.";
        $defaults['message_post_status_unknown'] = " is available for review within the customer's WCF account.";
        
        $defaults['message_thank_you']           = "Let's \"FLOOD\" it!";
        $defaults['message_quick_links_show']    = 1;
        $defaults['message_quick_links_intro']   = "Here is the link to edit the submission:";
        $defaults['message_referral_show']       = 0; 
        $defaults['message_referral_text']       = "";
    } elseif ($siteType=="boon") {
        $defaults['email_from_name']             = "testiBOONials";
        $defaults['email_from_addr']             = "submit@testiboonials.com";
        $defaults['email_subject']               = "New Testimonial Submission";
        $defaults['message_intro']               = "A new testimonial has been submitted with this title:
[[post_title]]";
        
        $defaults['message_post_status_intro']   = "This testimonial";
        $defaults['message_post_status_draft']   = " is waiting for review within your testiBOONials account."; 
        $defaults['message_post_status_publish'] = " has been published to your testiBOONials public page.";
        $defaults['message_post_status_unknown'] = " is available for review within your testiBOONials account.";
        
        $defaults['message_thank_you']           = "Thank you for being a testiBOONials subscriber";
        $defaults['message_quick_links_show']    = 1;
        $defaults['message_quick_links_intro']   = "Here is the link to edit the testimonial:";
        $defaults['message_referral_show']       = 1; 
        $defaults['message_referral_text']       = "If you know of others who'd benefit from subscribing to 
testiBOONials, we'd very much appreciate a referral. 
Just send them to http://testiboonials.com.";
    } elseif ($siteType=="videoaudio") {
        $defaults['email_from_name']             = "Video-Audio Forums";
        $defaults['email_from_addr']             = "submit@videoaudioforums.com";
        $defaults['email_subject']               = "New Forum Entry";
        $defaults['message_intro']               = "A new forum entry has been submitted with this title:
[[post_title]]";
        
        $defaults['message_post_status_intro']   = "This entry";
        $defaults['message_post_status_draft']   = " is waiting for review within your Video-Audio Forums account."; 
        $defaults['message_post_status_publish'] = " has been published to your Video-Audio Forums public page.";
        $defaults['message_post_status_unknown'] = " is available for review within your Video-Audio Forums account.";
        
        $defaults['message_thank_you']           = "Thank you for being a Video-Audio Forums subscriber";
        $defaults['message_quick_links_show']    = 1;
        $defaults['message_quick_links_intro']   = "Here is the link to edit the entry:";
        $defaults['message_referral_show']       = 1; 
        $defaults['message_referral_text']       = "If you know of others who'd benefit from subscribing to 
Video-Audio Forums, we'd very much appreciate a referral.
Just send them to: http://videoaudioforums.com.";
    } elseif ($siteType=="soupedup") {
        $defaults['email_from_name']             = "Souped-Up Blogs";
        $defaults['email_from_addr']             = "submit@soupedupblogs.com";
        $defaults['email_subject']               = "Your New Blog Post";
        $defaults['message_intro']               = "Your Souped-Up Blogs post has been successfully processed:
[[post_title]]";
        
        $defaults['message_post_status_intro']   = "This post";
        $defaults['message_post_status_draft']   = " is waiting for review within your Souped-Up Blogs account."; 
        $defaults['message_post_status_publish'] = " has been published to your Souped-Up Blogs public page.";
        $defaults['message_post_status_unknown'] = " is available for review within your Souped-Up Blogs account.";
        
        $defaults['message_thank_you']           = "Thank you for being a Souped-Up Blogs subscriber.";
        $defaults['message_quick_links_show']    = 1;
        $defaults['message_quick_links_intro']   = "Here is the link to edit the post:";
        $defaults['message_referral_show']       = 1; 
        $defaults['message_referral_text']       = "If you know of others who'd benefit from subscribing to 
Souped-Up Blogs, we'd very much appreciate a referral.
Just send them to: http://soupedupblogs.com.";
    }

/*    
    $defaults['email_from_name']             = "{$siteName}.com";
    $defaults['email_from_addr']             = "admin@{$siteName}.com";
    $defaults['email_subject']               = "New {$siteName} Post";
    $defaults['message_intro']               = "A new {$postName} was submitted a few minutes ago to your {$siteName} system -- with the following title:
    
Title: [[post_title]]";
    
    $defaults['message_post_status_intro']   = "According to your {$siteName} system settings, this {$postName}";
    $defaults['message_post_status_draft']   = " is waiting in Draft status within your {$siteName} account."; 
    $defaults['message_post_status_publish'] = " will be published immediately to your {$siteName} posting page.";
    $defaults['message_post_status_unknown'] = " is either:
* Published immediately to your {$siteName} posting page
* Is waiting in Draft status within your {$siteName} account
In either case, you can now modify/delete the {$postName} from with-in your {$siteName} account.";
    
    $defaults['message_thank_you']           = "Thank you for being a {$siteName} subscriber.";
    $defaults['message_quick_links_show']    = 1;
    $defaults['message_quick_links_intro']   = "Quick Links:";
    $defaults['message_referral_show']       = 1; 
    $defaults['message_referral_text']       = "Earn $25.00 for each new {$siteName} subscriber who signs up via this link: http://{$siteName}.com/{$referral_id}";
*/

    return $defaults; 
}

function ei8_xmlrpc_get_message_variables($form='') {
    $vars = array('email_from_name' => array('text', 'From (name)'), 
        'email_from_addr' => array('text', 'From (address)'), 
        'email_subject' => array('text', 'Subject'), 
        'message_intro' => array('textarea', 'Message intro'), 
        'message_post_status_intro' => array('textarea', 'Intro to post-status'), 
        'message_post_status_draft' => array('textarea', 'If status is "Draft"'), 
        'message_post_status_publish' => array('textarea', 'If status is "Publish"'), 
        'message_post_status_unknown' => array('textarea', 'If status is "unknown"'), 
        'message_quick_links_show' => array('boolean', 'Show quick links'), 
        'message_quick_links_intro' => array('textarea', 'Quick links text'), 
        'message_thank_you' => array('textarea', 'Thank you text'), 
        'message_referral_show' => array('boolean', 'Show referral text'), 
        'message_referral_text' => array('textarea', 'Referral text'),
      );
    return (empty($form)) ? array_keys($vars) : $vars ; 
}

function ei8_xmlrpc_get_message_settings() {
    $vars     = ei8_xmlrpc_get_message_variables();
    $defaults = ei8_xmlrpc_get_message_defaults();
    
    $message_settings = array();
    foreach($vars as $var) {
        $val = ei8_xmlrpc_get_option($var);
        if(empty($val)) $val = $defaults[$var];
        $message_settings[$var] = $val;
    }
    return $message_settings;
}
    
    
//handle db table installs and updates
function ei8_xmlrpc_admin_install() {
    global $wpdb, $wp_version;
    
    $table1 = $wpdb->prefix . "ei8_xmlrpc_options";

    $table1_sql = "CREATE TABLE `{$table1}` (
        `ID` BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
        `option_name` VARCHAR( 100 ) NOT NULL ,
        `option_value` TEXT NOT NULL,
        PRIMARY KEY ( `ID` ),
        UNIQUE ( `option_name` )
        );";

    if($wp_version < 3) require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $ei8_xmlrpc_db_sql   = $table1_sql ;
    
    $wpdb->flush();
    $errs = 0;
    
    //first check for old testiboonials settings and update if necessary
    $table2 = $wpdb->prefix . "testiboonials_xmlrpc_options";
    if($wpdb->get_var("SHOW TABLES LIKE '$table2'")==$table2 && $wpdb->get_var("SHOW TABLES LIKE '$table1'") != $table1) {
        ei8_xmlrpc_admin_log("<p>Converting database from older version.</p>",1);

        if ($wpdb->get_var("SHOW TABLES LIKE '$table1'") != $table1) {
            $sql = "CREATE TABLE $table1 LIKE $table2";
            $errs += ei8_xmlrpc_admin_query($sql);
            
            if($errs<1) {
                $sql = "SELECT * FROM $table2";
                $myrows = $wpdb->get_results($sql);
                foreach($myrows AS $row) {
                    
                //ei8_xmlrpc_admin_log("<p class='abq-error'>row: $row</p>",1);
                    //list($id,$name,$val) = $row;
                    $id   = $row->ID;
                    $name = $row->option_name;
                    $val  = $row->option_value;
                    $name = str_replace('testiboonials','ei8',$name);
                    $val  = str_replace('testiboonials','ei8',$val);
                    $sql  = "INSERT INTO $table1 SET ID=$id, option_name='$name', option_value='$val'";
                    $errs += $wpdb->query($sql);
                }
                $sql = "DROP table $table2";
                $errs += ei8_xmlrpc_admin_query($sql);
                
                ei8_xmlrpc_admin_log("<p>Storing new table structure</p>");
                ei8_xmlrpc_update_option("ei8_xmlrpc_db_sql",$ei8_xmlrpc_db_sql);
                
                ei8_xmlrpc_admin_log("<p>Database tables converted</p>",1);
            }
        } else {
            ei8_xmlrpc_admin_log("<p class='abq-error'>Errors converting database</p>",1);
        }                
    
    //handle first time installs
    } elseif($wpdb->get_var("SHOW TABLES LIKE '$table1'") != $table1) {
        ei8_xmlrpc_admin_log("<p>Performing initial database installation.</p>",1);
            
        ei8_xmlrpc_admin_log("<p>Creating new tables</p>");
        $errs += ei8_xmlrpc_admin_query($table1_sql);
        
        if($errs<1) {
            ei8_xmlrpc_admin_log("<p>Storing new table structure</p>");
            ei8_xmlrpc_update_option("ei8_xmlrpc_db_sql",$ei8_xmlrpc_db_sql);
            
            ei8_xmlrpc_admin_log("<p>New database tables installed</p>",1);
        } else {
            ei8_xmlrpc_admin_log("<p class='abq-error'>Errors installing new database tables</p>",1);
        }

    //handle upgrades
    } elseif( ei8_xmlrpc_get_option( "ei8_xmlrpc_db_sql" ) != $ei8_xmlrpc_db_sql ) {
        ei8_xmlrpc_admin_log("<p>Previous database version found...performing database upgrade</p>",1);
        //ei8_xmlrpc_admin_log("<p>CURRENT ei8_xmlrpc_db_sql :: <pre>".ei8_xmlrpc_get_blog_option( "ei8_xmlrpc_db_sql" )."</pre></p>",1);
        //ei8_xmlrpc_admin_log("<p>CURRENT ei8_xmlrpc_db_sql :: <pre>".ei8_xmlrpc_get_option( "ei8_xmlrpc_db_sql" )."</pre></p>",1);
        //ei8_xmlrpc_admin_log("<p>NEW ei8_xmlrpc_db_sql :: <pre>{$ei8_xmlrpc_db_sql}</pre></p>",1);
        
        //create table backups
        ei8_xmlrpc_admin_log("<p>Backing up current tables</p>");        
        $table1_bak = $table1."_bak";
        $errs += ei8_xmlrpc_admin_query( "RENAME TABLE $table1 TO {$table1}_bak;" );
        
        //create new tables
        ei8_xmlrpc_admin_log("<p>Creating new tables</p>");
        $errs += ei8_xmlrpc_admin_query($table1_sql);
        
        //copy data from backups
        ei8_xmlrpc_admin_log("<p>Copying old data into new tables</p>");        
        $errs += ei8_xmlrpc_admin_query( "INSERT INTO $table1 SELECT * FROM {$table1}_bak;" );
        
        //drop backup tables
        ei8_xmlrpc_admin_log("<p>Dropping backup tables</p>");        
        $errs += ei8_xmlrpc_admin_query( "DROP TABLE {$table1}_bak;" );
        
        //update options db_version
        if($errs<1) {
            ei8_xmlrpc_admin_log("<p>Storing new table structure</p>");
            ei8_xmlrpc_update_option("ei8_xmlrpc_db_sql",$ei8_xmlrpc_db_sql);
            ei8_xmlrpc_admin_log("<p>Database tables updated to current version</p>",1);
        } else {
            ei8_xmlrpc_admin_log("<p class='abq-error'>Errors updating database</p>",1);
        }
        
    } else {
        //ei8_xmlrpc_admin_log("<p>Database is up to date. No updates performed.</p>",1);
    }
    
    //check for deprecated named options and update as necessary
    if(!(ei8_xmlrpc_get_option('ei8_xmlrpc_recorder_vars')) && (ei8_xmlrpc_get_option('ei8_xmlrpc_pubClip_minirecorder'))) {
        ei8_xmlrpc_update_option('ei8_xmlrpc_recorder_vars', ei8_xmlrpc_get_option('ei8_xmlrpc_pubClip_minirecorder'));
        ei8_xmlrpc_update_option('ei8_xmlrpc_pubClip_minirecorder', '');
        ei8_xmlrpc_admin_log("<p>Updated recorder settings to current version</p>",1);
    }
    
    //make sure xmlrpc user exists and has the right permissions
    ei8_xmlrpc_get_login();
    
    //make sure the dir exists and is writable
    $uploadPath = ei8_xmlrpc_get_upload_dir();
    if(!is_dir($uploadPath)) wp_mkdir_p($uploadPath);
    if(!is_dir($uploadPath)) {
        $upload_path = ei8_xmlrpc_get_blog_option( 'upload_path' );
        if(!is_dir($upload_path)) @mkdir($upload_path, 0777);
        if(!is_writable($upload_path)) @chmod($upload_path, 0777);
        @mkdir($uploadPath, 0777);
    }
    if(!is_dir($uploadPath)) @mkdir($uploadPath);
    if(!is_dir($uploadPath)) {
        ei8_xmlrpc_admin_log("<p class='abq-error'>Error: uploads directory cannot be created.  <br><font style='color: black;'>Use this command to create the directory: mkdir $uploadPath</font></p>",1);
    }
    if(!is_writable($uploadPath)) @chmod($uploadPath, 0777);
    if(!is_writable($uploadPath)) {
        ei8_xmlrpc_admin_log("<p class='abq-error'>Error: uploads directory is not writable.  <br><font style='color: black;'>Use this command to make it writable: chmod -R 777 $uploadPath</font></p>",1);
    }
    
    //try to make sure xml-rpc is enabled
    update_site_option('enable_xmlrpc',1);

}


//logging functions
//install messages are stored as an option in the db as a page refresh occurs after the plugin is installed and before the page is renedered...effectively disabling any install reporting

//add msg to stored list
function ei8_xmlrpc_admin_log( $msg, $level=2 ) {
    global $ei8_xmlrpc_debug;
    if( $level<2 || isset($ei8_xmlrpc_debug) ) {
        //$logMsg .= $msg;
        update_site_option('ei8_xmlrpc_admin_log', ei8_xmlrpc_get_blog_option('ei8_xmlrpc_admin_log') . $msg);
    }
}

//execute sql queries, check for and log any sql errors
//returns 1 if errors are found, 0 if none
function ei8_xmlrpc_admin_query($sql) {
    global $wpdb, $ei8_xmlrpc_debug;
    
    //conditionally turn on error reporting
    //NOTE: there has got to be a better way to catch and display sql errors...but I ran out of time...
    if (isset($ei8_xmlrpc_debug)) $wpdb->show_errors(); 
       
    return ( $wpdb->query($sql) === FALSE ) ? 1 : 0 ;
}

//retrieve and display logMsg if it exists
function ei8_xmlrpc_admin_notices() {
    //$title = "<p><strong>Testiboonials XMLRPC notifier has been updated.</strong></p>";
    $msg   = ei8_xmlrpc_get_blog_option('ei8_xmlrpc_admin_log');
    if(!empty($msg)) {
        echo "<div id='akismet-warning' class='updated fade'>" . $title . $msg . "</div>";
        update_site_option( 'ei8_xmlrpc_admin_log', '' );
    }
}

//uncomment this line below to enable verbose install logging & display sql errors
$ei8_xmlrpc_debug = 1;
    
add_action('admin_init', 'ei8_xmlrpc_admin_install');
add_action('admin_notices', 'ei8_xmlrpc_admin_notices');

?>