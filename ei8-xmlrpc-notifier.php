<?php
/*
Plugin Name: Content XLerator Plugin
Plugin URI: http://wordpress.org/extend/plugins/einnov8-wp-xml-rpc-notifier/
Plugin Description: This plugin provides integration with eInnov8's Content XLerator system at cxl1.net as well as the wp native xml-rpc functionality.
Version: 3.7.3
Author: Tim Gallaugher
Author URI: http://wordpress.org/extend/plugins/profile/yipeecaiey
License: GPL2

Copyright 2010 Content XLerator  (email : timg@einnov8.com)

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


//include admin settings and functions
//if ( is_admin()) { //tagged out because contentsave.php uses some of these methods
    include_once( dirname(__FILE__) . '/ei8-xmlrpc-settings.php' );
    include_once( dirname(__FILE__) . '/ei8-xmlrpc-admin.php' );
    include_once( dirname(__FILE__) . '/ei8-xmlrpc-floodgate-controller.php' );
//}

//filter all post transitions to see if they are new xmlrpc posts
//and if so...handle pings and emails accordingly
function ei8_filter_xmlrpc_new_post( $new_status, $old_status, $post ) {
    //check for the xmlrpc flag
    $xmlrpcNewPost = ei8_xmlrpc_get_option('ei8_xmlrpc_new_post');
    if(!empty($xmlrpcNewPost)) {
        //check if email should be sent
        $tEmail = ei8_xmlrpc_get_option('ei8_xmlrpc_email_notify');
        if(!empty($tEmail)) ei8_email_notify($post->ID, $tEmail);

        //check if ping should be sent
        $tPing = ei8_xmlrpc_get_option('ei8_xmlrpc_ping');
        if(!empty($tPing)) ei8_add_ping($post->ID, $tPing);

        //debugging to know what happened
        ei8_xmlrpc_update_option('ei8_xmlrpc_new_post_last_processed', date('m/d/Y H:i:s'));

        //turn back on html filtering
        kses_init_filters();

        //clear flag for xmlrpc post handling
        ei8_xmlrpc_update_option('ei8_xmlrpc_new_post', '');
    }
}
add_action('transition_post_status',  'ei8_filter_xmlrpc_new_post', 10, 3 );

//pre-filter all xmlrpc new posts before they are added.
//THESE SETTINGS DO NOT OVERRIDE the xmlrpc source
function ei8_xmlrpc_new_post($post, $raw_post) {

    //disable html filtering
    kses_remove_filters();

    //update post type
    $postType = ei8_xmlrpc_get_option('ei8_xmlrpc_post_type');
    if(!empty($postType) && empty($raw_post['post_type'])) $post['post_type'] = $postType;

    //force status update
    $tStatus = ei8_xmlrpc_get_option('ei8_xmlrpc_post_status');
    //TODO: When CXL option to set post_status is reinstated, change this line
    //if(!empty($tStatus) && empty($raw_post['post_status'])) $post['post_status'] = $tStatus;
    if(!empty($tStatus)) $post['post_status'] = $tStatus;

    //filter the post content
    if(!empty($post['post_content'])) $post['post_content'] = ei8_autolink_safe($post['post_content']);

    //$post['post_content'] .= "POST:<pre>".print_r($post,true)."</pre><br>RAW POST:<pre>".print_r($raw_post,true);

    //add flag for xmlrpc post handling
    ei8_xmlrpc_update_option('ei8_xmlrpc_new_post', 'true');

    return $post;
}
add_filter('xmlrpc_wp_insert_post_data', 'ei8_xmlrpc_new_post', 99, 2);

function ei8_update_post_status($post_id, $tStatus) {
    //global $wpdb;
    //$wpdb->query( "UPDATE $wpdb->posts SET post_status = '$tStatus' WHERE ID = '$post_id'" );

    // Update post
      $my_post = array();
      $my_post['ID'] = $post_id;
      $my_post['post_status'] = $tStatus;

    // Update the post into the database
      wp_update_post( $my_post );
}

function ei8_update_post_content($post_id, $tContent) {
    global $wpdb;
    $wpdb->query(
        $wpdb->prepare("UPDATE $wpdb->posts SET post_content = '%s' WHERE ID = '%s'",
            $tContent,
            $post_id
        )
    );
}
/*add_action( 'init', 'ei8_create_post_types' );
function ei8_create_post_types() {
	register_post_type( 'test_product',
		array(
			'labels' => array(
				'name' => __( 'Products' ),
				'singular_name' => __( 'Product' ),
                //'taxonomies' => array('category')
			),
		'public' => true,
		'has_archive' => true,
		)
	);
	register_post_type( 'ei8_test_testimonial',
		array(
			'labels' => array(
				'name' => __( 'Testimonials' ),
				'singular_name' => __( 'Testimonial' )
			),
		'public' => true,
		'has_archive' => true,
		)
	);
    register_taxonomy( 'product-category', 'test_product',
        array(
            'labels' => array(
                'name' => __( 'Categories' ),
                'singular_name' => __( 'Category' )
            )
        )
    );
    register_taxonomy( 'testimonial-category', 'ei8_test_testimonial',
        array(
            'labels' => array(
                'name' => __( 'Categories' ),
                'singular_name' => __( 'Category' )
            )
        )
    );
    register_taxonomy( 'testimonial-endorsement', 'ei8_test_testimonial',
        array(
            'labels' => array(
                'name' => __( 'Endorsements' ),
                'singular_name' => __( 'Endorsement' )
            )
        )
    );
    //register_taxonomy_for_object_type( 'product-category', 'test_product' );
    //register_taxonomy_for_object_type( 'testimonial-category', 'ei8_test_testimonial' );
    //register_taxonomy_for_object_type( 'testimonial-category2', 'ei8_test_testimonial' );
    //register_taxonomy_for_object_type( 'post_tag', 'ei8_test_testimonial' );
}
*/
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
        $siteName = ei8_xmlrpc_get_site_type_name();
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

    //debugging to know what happened
    ei8_xmlrpc_update_option('ei8_email_last_sent', $tEmail." - ".date('m/d/Y H:i:s'));
}

function ei8_add_ping($post_id, $tPing) {
    add_ping($post_id, $tPing);
}


function ei8_autolink_safe($content) {
    //make sure we only run this once
    //$stamp = "<!-- PARSED BY ei8_autolink_safe() -->";
    //if (strstr($content,$stamp)) return $content;
    //return $stamp.$content;

    $parts = explode('<', $content);
    $content = "";
    foreach($parts as $part) {
        //echo "<p>processing part: <pre>$part</pre></p>";
        //handle the first part that precedes the shortcode
        if(empty($content)) {
            $content = ei8_autolink_no_shortcodes($part);
        } else {
            //pull out the shortcode from the 'other' part of the content
            list($tag, $working) = explode(">", $part, 2);
            $content .= '<'.$tag.'>'.ei8_autolink_no_shortcodes($working);
        }
    }
    return $content;
    //return $stamp.$content;
}

function ei8_autolink_no_shortcodes($content) {
    $parts = ei8_xmlrpc_explode_string($content);
    $content_bak = $content; //make a copy before we start just in case we need to roll back
    $content = "";
    foreach($parts as $part) {
        //echo "<p>processing part: <pre>$part</pre></p>";
        //handle the first part that precedes the shortcode
        if(empty($content)) {
            $content = ei8_autolink($part);
        } else {
            //pull out the shortcode from the 'other' part of the content
            list($shortcode, $working) = explode("]", $part, 2);
            $content .= '[cxl'.$shortcode.']'.ei8_autolink($working);
        }
    }
    return $content;
}

function ei8_autolink( &$text, $target='_blank', $nofollow=true )
{
    //for some reason this is getting called repeatedly...so skip if it has already been called
    //$stamp = "<!-- PARSED BY ei8_autolink() -->";
    //if (strstr($text,$stamp)) return $text;

    //$text = htmlentities($text);
    // grab anything that looks like a URL...
    $urls  =  ei8_autolink_find_URLS( $text );
    if( !empty($urls) ) // i.e. there were some URLS found in the text
    {
        array_walk( $urls, 'ei8_autolink_create_html_tags', array('target'=>$target, 'nofollow'=>$nofollow) );
        $text  =  strtr( $text, $urls );
    }
    $text = ei8_autolink_make_complete_URLS($text);
    //$text = $stamp.$text;
    return $text;
}

function ei8_autolink_find_URLS( $text )
{
    // build the patterns
    $scheme         =       '(http:\/\/|https:\/\/)';
    $www            =       'www\.';
    $ip             =       '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
    $subdomain      =       '[-a-z0-9_]+\.';
    $name           =       '[a-z][-a-z0-9]+\.';
    $tld            =       '[a-z]+(\.[a-z]{2,2})?';
    $the_rest       =       '\/?[a-z0-9._\/~#&=;%+?-]+[a-z0-9\/#=?]{1,1}';
    $pattern        =       "$scheme?(?(1)($ip|($subdomain)?$name$tld)|($www$name$tld))$the_rest";

    $pattern        =       '/'.$pattern.'/is';
    $c              =       preg_match_all( $pattern, $text, $m );
    unset( $text, $scheme, $www, $ip, $subdomain, $name, $tld, $the_rest, $pattern );
    if( $c )
    {
        return( array_flip($m[0]) );
    }
    return( array() );
}

function ei8_autolink_make_complete_URLS( $text )
{
    $text = str_replace('href="', 'href="http://', $text);
    $text = str_replace('href="http://http', 'href="http', $text);
    return $text;
}

function ei8_autolink_create_html_tags( &$value, $key, $other=null )
{
    $target = $nofollow = null;
    if( is_array($other) )
    {
        $target      =  ( $other['target']   ? " target=\"$other[target]\"" : null );
        // see: http://www.google.com/googleblog/2005/01/preventing-comment-spam.html
        $nofollow    =  ( $other['nofollow'] ? ' rel="nofollow"'            : null );
    }
    $value = (stristr($key,'youtube.com') || stristr($key,'youtu.be')) ? "\n$key\n" : "<a href=\"$key\"$target$nofollow>$key</a>";
    //$value = "<a href=\"$key\"$target$nofollow>$key</a>";
}


/*
 * FILTER FRONT END DISPLAY
*/

function ei8_xmlrpc_swf_wrap($url,$height,$width) {
    /*$html =<<<EOT
<p><object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="$width" height="$height" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="wmode" value="transparent" /><param name="src" value="$url" /><param name="allowfullscreen" value="true" /><embed type="application/x-shockwave-flash" width="$width" height="$height" src="$url" wmode="transparent" allowscriptaccess="always" allowfullscreen="true"></embed></object></p>
EOT;*/
    $id = uniqid("ei8swf_");
    $html =<<<EOT
    <div id="{$id}"><p><object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="$width" height="$height" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="wmode" value="transparent" /><param name="src" value="$url" /><param name="allowfullscreen" value="true" /><embed type="application/x-shockwave-flash" width="$width" height="$height" src="$url" wmode="transparent" allowscriptaccess="always" allowfullscreen="true"></embed></object></p></div>
<script type="text/javascript">
    swfobject.embedSWF("$url", "$id", $width, $height, "9");
</script>
EOT;
    return $html;
}

function ei8_xmlrpc_string_to_array($string) {
    $myVars = array();
    if(is_array($string)) {
        foreach($string as $s) {
            $myVars = array_merge($myVars,ei8_xmlrpc_string_to_array($s));
        }
    } elseif(strstr($string,'&')) {
        $string = html_entity_decode($string);
        $els = explode('&',$string);
        foreach($els as $el) {
            list($name,$val) = explode('=',$el);
            $myVars[trim($name)] = trim($val);
        }
    } elseif(strstr($string,'=')) {
        list($name,$val) = explode('=',$string);
        $myVars[trim($name)] = trim($val);
    }
    return $myVars;
}

function ei8_xmlrpc_parse_recorder_vars($defaultVars='',$overrideVars='') {
    //make sure the inputs are arrays
    $dVars    = ei8_xmlrpc_string_to_array($defaultVars); //deprecated...but still left in just in case
    $oVars   = ei8_xmlrpc_string_to_array($overrideVars);

    //check for custom folder settings...add in those variables if they are set
    if(in_array('cf',array_keys($oVars))) $dVars = ei8_xmlrpc_string_to_array(ei8_xmlrpc_getCustomFolderValue($oVars['cf']));

    //merge the two arrays together (with precedence for the overrideVars)
    $myVars = array_merge($dVars,$oVars);
/*
    echo "<p>defaultVars: <pre>"; print_r($defaultVars); echo "</pre></p>";
    echo "<p>overrideVars: <pre>"; print_r($overrideVars); echo "</pre></p>";
    echo "<p>dVars: <pre>"; print_r($dVars); echo "</pre></p>";
    echo "<p>oVars: <pre>"; print_r($oVars); echo "</pre></p>";
    echo "<p>myVars: <pre>"; print_r($myVars); echo "</pre></p>";
*/

    //now collapse it all back into a string
    $stringVars = array();
    foreach($myVars as $key=>$val) $stringVars[$key] = "$key=$val";
    $string = implode("&",$stringVars);

    return $string;
}

function ei8_xmlrpc_recorder_wrap($type, $vars='') {
    //$service = "http://www.cxl1.net/";
    $domain = (strstr($_SERVER['HTTP_HOST'],'localwp') || strstr($_SERVER['HTTP_HOST'],'1dev1')) ? 'www.dev.cxl1.net' : 'www.cxl1.net' ;
    $service = "https://$domain/";
    $doError = false;
    if(empty($vars)) $doError = true;
    switch($type) {
        case 'ft' :
            $folder = "swffloodtech";
            $height = 430;
            $width  = 296;
            break;
        case 'fta' :
            $folder = "swffloodtechaudio";
            $height = 220;
            $width  = 296;
            break;
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
            $height = 355;
            $width  = '100%';
            break;
        default :
            $folder  = $height = $width = '';
            $doError = true;
    }
    if($doError){
        $showType = ucwords($type);
        $html = "<p style='color: #ff0000; size: 13px; font-weight: bold;'>ERROR LOADING Content XLerator $showType Recorder - please notify website administrator support@einnov8.com</p>";
    /*} elseif($type=="media") {
        parse_str($vars);
        $css = urlencode(ei8_coalesce(ei8_xmlrpc_get_option('ei8_xmlrpc_file_uploader_css'), ei8_plugins_url('/css/ei8-file-uploader.css')));
        $url = "{$service}{$folder}/{$a}/{$v}/?externcss={$css}";
        $html = "<iframe src ='$url' class='ei8-form-iframe' frameborder='0'><p>Your browser does not support iframes.</p></iframe>";
    */} elseif($type=="media") {
        $uploader = new ei8XmlrpcUploader($vars);
        $html = $uploader->render();
    } else {
        $url = "{$service}{$folder}/{$vars}";
        //if (strstr($_SERVER['HTTP_HOST'],'localwp')) $url = 'http://localwp/flash/webrecmv_new/webrec.swf?'.$vars;
        $html = "<div class='ei8-shortcode-wrapper'><div class='ei8-web-recorder ei8-web-recorder-$type'>".ei8_xmlrpc_swf_wrap($url,$height,$width)."</div></div>";
    }
    return $html;
}


function ei8_xmlrpc_get_plugin_dir() {
    //$pathinfo = pathinfo( plugin_basename( __FILE__ ) );
    //$pluginDir = "/wp-content/plugins/" . $pathinfo['dirname'] . '/';
    $pluginDir = "/wp-content/plugins/einnov8-wp-xml-rpc-notifier/";
    return $pluginDir;
}


function ei8_xmlrpc_get_plugin_url() {
    $wpurl = get_bloginfo('wpurl');
    return $wpurl . ei8_xmlrpc_get_plugin_dir();
}

function ei8_xmlrpc_conf_message_defaults($success=true) {
    if($success) {
        $title  = "Submission Received";
        $text   = "Your submission was submitted successfully and will be processed shortly.";
    } else {
        $title  = "We encountered an error while processing your submission";
        $text   = "An unknown error has occurred";
    }
    return array($title,$text);
}

function ei8_xmlrpc_conf_message($success=true,$title='default',$text='default',$whiteSpace=true) {
    $defaults = ei8_xmlrpc_conf_message_defaults($success);
    if($title == 'default') $title  = $defaults[0];
    if($text == 'default')  $text   = $defaults[1];

    $pluginDir = ei8_xmlrpc_get_plugin_url();
    $confImg   = ($success) ? "success.png" : "error.png";
    $title     = ($success) ? $title : '<span style="color:red;">'.$title.'</span>' ;

    $confMessage =<<<EOT
<div class="ei8-confirmation" id="ei8-confirmation">
    <div class="ei8-confirmation-img"><img src="{$pluginDir}images/{$confImg}"></div>
    <div class="ei8-confirmation-msg"><strong>$title</strong><br>$text</div>
</div>
EOT;

    if($whiteSpace!=true) $confMessage = preg_replace('~>\s+<~', '><', $confMessage);

    return $confMessage;
}

function ei8_xmlrpc_do_expand() {
    /*$expandThis =<<<EOT
        <script type="text/javascript">expandThis();</script>
EOT;
    return $expandThis;*/
}

function ei8_xmlrpc_filter_tags($content) {
    $siteType  = ei8_xmlrpc_get_site_type();

    $ei8tVars          = ei8_xmlrpc_get_option('ei8_xmlrpc_recorder_vars');
    $ei8tMiniRecorder  = ei8_xmlrpc_recorder_wrap('mini', $ei8tVars);
    $ei8tTallRecorder  = ei8_xmlrpc_recorder_wrap('tall', $ei8tVars);
    $ei8tWideRecorder  = ei8_xmlrpc_recorder_wrap('wide', $ei8tVars);
    $ei8tMediaUploader = ei8_xmlrpc_recorder_wrap('media', $ei8tVars);

    $ei8tPluginUrl     = ei8_xmlrpc_get_plugin_url();

    //if(1==ei8_xmlrpc_get_option('ei8_xmlrpc_use_captcha')) {
        $ident = "ei8-captcha-".date("U");
        $captchaSubmitForm = '<div class="ei8-form-line">
        <div class="ei8-form-label"><img src="'.ei8_xmlrpc_get_plugin_url().'php_captcha.php" alt="" class="ei8-captcha-image" /></div>
        <div class="ei8-form-field"><label>Please enter the code you see: </label><input id="\&quot;number\&quot;/" name="number" type="text" /></div>
    </div>';
    //} else $captchaSubmitForm = '';


    $submitFormLink   = ei8_xmlrpc_get_plugin_url() . "contentsave.php";
    //$textBoxTitle     = ($siteType=="flood") ? "Text Box" : "Comment";
    $textBoxTitle     = "Content";
    $aName            = "ei8xmlrpcsimplesubmit";
    $showConf         = ($_REQUEST['success']==$aName) ? ei8_xmlrpc_conf_message().ei8_xmlrpc_do_expand() : "" ;
    $simpleSubmitForm =<<<EOT
<a name="$aName"></a>
$showConf
<form action="$submitFormLink" enctype="multipart/form-data" method="post">
<div class="ei8-form-wrapper">
    <div class="ei8-form-line">
        <div class="ei8-form-label">Title:</div>
        <div class="ei8-form-field"><input name="title" size="40" type="text" /></div>
    </div>
    <div class="ei8-form-line">
        <div class="ei8-form-label">{$textBoxTitle}:</div>
        <div class="ei8-form-field"><textarea class="ei8-textarea-simple-submit" name="comment"></textarea></div>
    </div>
    <div class="ei8-form-line">
        <div class="ei8-form-line-double">Select image file to send: <input name="uploadedfile" type="file" /></div>
    </div>
    $captchaSubmitForm
    <div class="ei8-form-line-spacer"></div>
    <div class="ei8-form-line">
        <div class="ei8-form-label"></div>
        <div class="ei8-form-field">
            <input type="hidden" name="fileaction" value="embed_image">
            <input type="hidden" name="ei8_xmlrpc_a" value="$aName">
            <input name="Submit" type="submit" value="Submit" />
        </div>
    </div>
</div>
</form>
EOT;
    if(empty($submitFormLink)) $simpleSubmitForm = "<p style='color: red; size: 13px; font-weight: bold;'>ERROR LOADING Simple Submit Form - please notify website administrator</p>";

    $aName            = "ei8xmlrpcattachmentsubmit";
    $showConf         = ($_REQUEST['success']==$aName) ? ei8_xmlrpc_conf_message() : "" ;
    $attachmentSubmitForm =<<<EOT
<a name="$aName"></a>
$showConf
<form action="$submitFormLink" enctype="multipart/form-data" method="post">
<div class="ei8-form-wrapper">
    <div class="ei8-form-line">
        <div class="ei8-form-label">Title:</div>
        <div class="ei8-form-field"><input name="title" size="40" type="text" /></div>
    </div>
    <div class="ei8-form-line">
        <div class="ei8-form-label">Content:</div>
        <div class="ei8-form-field"><textarea class="ei8-textarea-attachment-submit" name="comment"></textarea></div>
    </div>
    <div class="ei8-form-line">
        <div class="ei8-form-line-double">Select file to upload: <input name="uploadedfile" type="file" /></div>
    </div>
    $captchaSubmitForm
    <div class="ei8-form-line-spacer"></div>
    <div class="ei8-form-line">
        <div class="ei8-form-label"></div>
        <div class="ei8-form-field">
            <input type="hidden" name="fileaction" value="attached_doc">
            <input type="hidden" name="ei8_xmlrpc_a" value="$aName">
            <input name="Submit" type="submit" value="Submit" />
        </div>
    </div>
</div>
</form>
EOT;
    if(empty($submitFormLink)) $attachmentSubmitForm = "<p style='color: red; size: 13px; font-weight: bold;'>ERROR LOADING Attachment Submit Form - please notify website administrator</p>";

    $aName = "ei8xmlrpctwitterform";
    if ($_REQUEST['success']==$aName) {
        $showConf = ($_REQUEST['errorMessage']) ? ei8_xmlrpc_conf_message(false,'Twitter Error',$_REQUEST['errorMessage']) : ei8_xmlrpc_conf_message(true,'Success','Your twitter status has been updated');
    } else $showConf = "" ;
    $twitterForm =<<<EOT
<a name="$aName"></a>
$showConf
<form action="$submitFormLink" enctype="multipart/form-data" method="post">
<div class="ei8-form-wrapper">
    <div class="ei8-form-line">
        <div class="ei8-form-label">Content:</div>
        <div class="ei8-form-field">
            <textarea class="ei8-textarea-tweet" name="ei8_xmlrpc_tweet" id="tweet" ></textarea>
        </div>
    </div>
    $captchaSubmitForm
    <div class="ei8-form-line-spacer"></div>

    <div class="ei8-form-line">
        <div class="ei8-form-label">
            <div id="counter">Character count:</div>
        </div>
        <div class="ei8-form-field">
            <div id="barbox"><div id="bar"></div></div>
            <div id="count">140</div>
            <input type="hidden" name="ei8_xmlrpc_twitter_post" value="1">
            <input type="hidden" name="ei8_xmlrpc_a" value="$aName">
            <input name="Submit" type="submit" value="Submit" />
        </div>
    </div>
</div>
</form>
EOT;
    $twitterToken  = ei8_xmlrpc_get_option('ei8_xmlrpc_twitter_token');
    $twitterSecret = ei8_xmlrpc_get_option('ei8_xmlrpc_twitter_secret');


    if(empty($submitFormLink) OR empty($twitterToken) OR empty($twitterSecret)) $twitterForm = "<p style='color: red; size: 13px; font-weight: bold;'>ERROR LOADING Twitter Form - please notify website administrator</p>";

    $twitterButton =<<<EOT
<a href="http://twitter.com/share" class="twitter-share-button" data-text="Enter Your Tweet Here" data-count="none">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
EOT;


    //do some prelimiunary string sanitizing
    $content = str_replace('[ei8', '[cxl', $content);
    //$content = str_replace('&nbsp;[cxl', ' [cxl', $content);

    //actually do the parsing
    $content = str_replace('[cxl MiniRecorder]', $ei8tMiniRecorder, $content);
    $content = str_replace('[cxl WideRecorder]', $ei8tWideRecorder, $content);
    $content = str_replace('[cxl TallRecorder]', $ei8tTallRecorder, $content);
    $content = str_replace('[cxl Simple Submit Form]', $simpleSubmitForm, $content);
    $content = str_replace('[cxl Attachment Submit Form]', $attachmentSubmitForm, $content);
    $content = str_replace('[cxl Twitter Button]', $twitterButton, $content);
    $content = str_replace('[cxl Twitter Form]', $twitterForm, $content);
    $content = str_replace('[cxl MediaUploader]', $ei8tMediaUploader, $content);

    //handle any video shortcodes
    $content = ei8_xmlrpc_filter_shortcode($content);

    //deprecated
    $content = str_replace('[[Load MiniRecorder]]', $ei8tMiniRecorder, $content);
    $content = str_replace('[[Load WideRecorder]]', $ei8tWideRecorder, $content);
    $content = str_replace('[[Load TallRecorder]]', $ei8tTallRecorder, $content);
    $content = str_replace('[[Load Simple Submit Form]]', $simpleSubmitForm, $content);
    $content = str_replace('[[Load Attachment Submit Form]]', $attachmentSubmitForm, $content);
    $content = str_replace('[[Load Twitter Button]]', $twitterButton, $content);
    $content = str_replace('[[Load Twitter Form]]', $twitterForm, $content);
    $content = str_replace('[[Load MediaUploader]]', $ei8tMediaUploader, $content);
    $content = str_replace('[[Load Web Recorder]]', $ei8tWideRecorder, $content);
    $content = str_replace('[[Load PubClip MiniRecorder]]', $ei8tMiniRecorder, $content);
    $content = str_replace('[[Load Captcha Submit Form]]', $simpleSubmitForm, $content);

    //started, but not yet finished
    return $content;
}

add_filter( 'the_content', 'ei8_xmlrpc_filter_tags', 11111 );
//add_filter('the_excerpt', 'ei8_xmlrpc_filter_tags', 11111);

function remove_shortcode_from_excerpt($content) {
    $content = strip_shortcodes( $content );
    return $content;//always return $content
}

//load js
function ei8_enqueue_scripts() {
    //load the js available from the google api

    //wp_deregister_script( 'jquery' );
    //wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js');
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'swfobject' );

    //wp_register_script( $handle, $src, $deps, $ver, $in_footer );

    //now load the local js
    wp_register_script( 'ei8-tweet-script', ei8_plugins_url('/lib/js/ei8-xmlrpc-tweet.js'), array('jquery'), false, true );
    wp_enqueue_script( 'ei8-tweet-script' );

    //wp_register_script( 'ei8-xmlrpc-notifier', ei8_plugins_url('/ei8-xmlrpc-notifier.js') , array('jquery', 'jquery-ui-core','jquery-effects-core','jquery-effects-fade','jquery-effects-slide','jquery-ui-slider') );
    wp_register_script( 'ei8-xmlrpc-notifier', ei8_plugins_url('/lib/js/ei8-xmlrpc-notifier.js') , array('jquery'), false, true );
    wp_enqueue_script( 'ei8-xmlrpc-notifier' );

    //jwplayer
    wp_register_script( 'ei8-xmlrpc-jwplayer', 'https://ssl.p.jwpcdn.com/6/12/jwplayer.js' );
    //wp_register_script( 'ei8-xmlrpc-jwplayer', ei8_plugins_url('/lib/js/jwplayer-3.5.1.js') );
    wp_enqueue_script( 'ei8-xmlrpc-jwplayer' );
    wp_enqueue_script('ei8-xmlrpc-jwplayer-key',ei8_plugins_url('/lib/js/jwplayer.key.js'));

    //thumbnail scroller
    wp_register_script( 'ei8-jquery-custom', ei8_plugins_url('/lib/js/jquery-ui-1.8.13.custom.min.js'), array('jquery'), false, true );
    wp_enqueue_script( 'ei8-jquery-custom' );

    //qtip for scroller title/description rollovers
    wp_register_script('jquery-qtip', ei8_plugins_url('/lib/js/jquery.qtip.min.js'), array('jquery'), false, true);
    wp_enqueue_script( 'jquery-qtip' );

    //uploadfile for the media uploader
    wp_register_script('jquery-uploadfile', ei8_plugins_url('/lib/js/jquery.uploadfile.min.js'), array('jquery'), false, true);
    //wp_enqueue_script( 'jquery-uploadfile' ); //only include this when needed

    //specific floodgate js
    wp_register_script('floodgate', ei8_plugins_url('/lib/js/floodgate.js'), array('jquery'), false, true);
    //wp_enqueue_script( 'floodgate' ); //only include this when needed

    //thumbnail scroller
    wp_register_script( 'ei8-thumbnail_scroller', ei8_plugins_url('/lib/js/jquery.thumbnailScroller.js'), array('jquery'), false, true );
    wp_enqueue_script( 'ei8-thumbnail_scroller' );

}
add_action('wp_enqueue_scripts', 'ei8_enqueue_scripts');

//add styles to admin
function ei8_register_head() {
    //$url = ei8_plugins_url('/css/ei8-xmlrpc-notifier.css');
    //echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
    wp_enqueue_style('ei8-xmlrpc-notifier', ei8_plugins_url('/css/ei8-xmlrpc-notifier.css'), null, false, false);

    //$url = 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css';
    //echo "<link rel='stylesheet' type='text/css' href='$url' />\n";

    //qtip for scroller title/description rollovers
    wp_enqueue_style('qtip', ei8_plugins_url('/css/jquery.qtip.min.css'), null, false, false);

    //echo '<script type="text/javascript">jwplayer.key="CrmSh4fiXjB2MrwBht0Q3pjOqppvu+U+as8bcQ==";</script>';
}
//add_action('admin_head', 'ei8_register_head');
add_action('wp_head', 'ei8_register_head');


function ei8_plugins_url($newFile,$currentFile=__FILE__) {
    return (!strstr($currentFile,' ')) ? plugins_url($newFile,$currentFile) : get_option('siteurl').'/wp-content/plugins/einnov8-wp-xml-rpc-notifier'.$newFile;
}

function ei8_coalesce() {
    $args = func_get_args();
    foreach ($args as $arg) {
        if (!empty($arg)) {
            return $arg;
        }
    }
    return $args[0];
}

function ei8_xmlrpc_filter_shortcode($content,$type='') {
    //filter any deprecated shortcode references
    $content = str_replace('[ei8', '[cxl', $content);

    //filter for upload folder overrides
    $content = ei8_xmlrpc_parse_uploader_shortcode($content);

    //filter for expander
    $content = ei8_xmlrpc_parse_expander_shortcode($content);

    //filter out html comment tags around shortcodes (used in syndication)
    $content = ei8_xmlrpc_parse_commented_shortcode($content);

    //filter out and handle playlists
    $content = ei8_xmlrpc_parse_playlist_shortcode($content);

    //filter embed shortcodes
    $content = ei8_xmlrpc_parse_embed_shortcode($content);

    //filter for player shortcodes
    return ei8_xmlrpc_parse_shortcode($content, $type);
}

function ei8_xmlrpc_parse_expander_shortcode($content) {
    $expanderTitle     = '<h2 class="expand">';
    $expanderTitleEnd  = '</h2><div class="collapse">';
    $expanderBody      = '';
    $expanderBodyEnd   = '</div>';

    //$content = str_replace('[cxl Expander]', $expanderOpen, $content);
    //$content = str_replace('[cxl ExpanderEnd]', $expanderEnd, $content);
    $content = str_replace('[cxl ExpanderTitle]', $expanderTitle, $content);
    $content = str_replace('[cxl ExpanderTitleEnd]', $expanderTitleEnd, $content);
    $content = str_replace('[cxl ExpanderBody]', $expanderBody, $content);
    $content = str_replace('[cxl ExpanderBodyEnd]', $expanderBodyEnd, $content);

    //filter deprecated tags
    //$content = str_replace('[cxl accordion]', $expanderOpen, $content);
    //$content = str_replace('[cxl accordionEnd]', $expanderEnd, $content);
    $content = str_replace('[cxl accordionTitle]', $expanderTitle, $content);
    $content = str_replace('[cxl accordionTitleEnd]', $expanderTitleEnd, $content);
    $content = str_replace('[cxl accordionBody]', $expanderBody, $content);
    $content = str_replace('[cxl accordionBodyEnd]', $expanderBodyEnd, $content);


    return $content;
}

function ei8_xmlrpc_parse_uploader_shortcode($content) {
    $parts = ei8_xmlrpc_explode_string($content);
    $content_bak = $content; //make a copy before we start just in case we need to roll back
    $content = "";
    foreach($parts as $part) {
        //echo "<p>processing part: <pre>$part</pre></p>";
        //handle the first part that precedes the shortcode
        if(empty($content)) {
            $content = $part;
            continue;
        }

        //now pull out the shortcode from the 'other' part of the content
        list($working, $other) = explode("]", $part, 2);

        $working_bak = $working;

        //remove unneeded whitespace
        $working = trim($working);
        $working = htmlspecialchars_decode($working);
        $working = htmlspecialchars_decode($working);
        $working = strip_tags($working);

        //determine if this is a shortcode we are trying to parse...if not, skip it
        if (!preg_match('/(MiniRecorder|TallRecorder|WideRecorder|MediaUploader)/',$working)) {
            //echo "<p>Skipping this part</p>";
            $content .= '[cxl'.$part;
            continue;
        }

        //split up the shortcode into the different values we have to work with
        $values = explode(" ",$working);
        //echo "<p>values: <pre>"; print_r($values); echo "</pre></p>";

        $myValues = array();
        $customFolders = ei8_xmlrpc_getCustomFolders();
        foreach($values as $statement) if(!strstr($statement,"=")) $typeName = $statement; else $myValues[] = $statement;

        $ei8tVars   = ei8_xmlrpc_get_option('ei8_xmlrpc_recorder_vars');
        $myVars     = ei8_xmlrpc_parse_recorder_vars($ei8tVars,$myValues);
        //echo "<p>typeName: $typeName, myVars: $myVars";
        switch($typeName) {
            case 'MiniRecorder':
                $type = 'mini';
                break;
            case 'WideRecorder':
                $type = 'wide';
                break;
            case 'TallRecorder':
                $type = 'tall';
                break;
            case 'MediaUploader':
            default:
                $type = 'media';
                break;
        }
        $final      = ei8_xmlrpc_recorder_wrap($type, $myVars);

        $content .= $final.$other;
    }
    return $content;
}

function ei8_xmlrpc_parse_commented_shortcode($content) {
    $parts = explode('<!--[cxl', $content);
    $content_bak = $content; //make a copy before we start just in case we need to roll back
    $content = "";
    foreach($parts as $part) {
        //handle the first part that precedes the shortcode
        if(empty($content)) {
            $content = $part;
            continue;
        }

        //now pull out the shortcode from the 'other' part of the content
        list($working, $other) = explode("]-->", $part, 2);

        $content .= "[cxl".$working."]".$other;
    }
    return $content;
}

function ei8_xmlrpc_get_guid_from_url($url) {
    //$url = "http://www.dev.ei8t.com/swf/5MKzFmmnhFY&w=480&h=290&bm=td&cp=000000-FFFFFF-FFBB00-000000";
    list($url) = explode('&',$url);
    $parts = explode('/',$url);
    $guid = array_pop($parts);
    return $guid;
}

function ei8_xmlrpc_get_first_valid_element_from_array($array, $key) {
    foreach($array as $part) if ($part[$key] && $part[$key]!='') return $part[$key];
    //hopefully you don't get here
    return "";
}

function ei8_xmlrpc_filter_url($url) {
    $parsed = parse_url($url);
    $oldHost = $parsed['host'];
    $newHost = ei8_xmlrpc_filter_host($oldHost);
    if($oldHost!=$newHost) $url = str_replace($oldHost,$newHost,$url);
    return $url;
}

function ei8_xmlrpc_filter_host($host) {
    $host = str_replace('vam1.com','cxl1.net',$host);
    $host = str_replace('ei8t.com','cxl1.net',$host);
    if(strstr($host,'cxl1.net')) {
        $host = 'www.'.$host;
        $host = str_replace('www.www', 'www', $host);
    }
    return $host;
}


function ei8_xmlrpc_parse_playlist_shortcode($content,$type='') {
    $parts = ei8_xmlrpc_explode_string($content, '[cxl Playlist');
    $content_bak = $content; //make a copy before we start just in case we need to roll back
    $content = "";
    $playList = array();
    foreach($parts as $part) {
        //handle the first part that precedes the shortcode
        if(empty($content)) {
            $content = $part;
            continue;
        }

        //now pull out the shortcode from the 'other' part of the content
        list($pWorking, $working) = explode("]", $part, 2);

        //echo "<p>pWorking: <pre>$pWorking</pre></p>";
        //echo "<p>working: <pre>$working</pre></p>";

        //remove unneeded whitespace, ensure correct formatting of needed whitespace
        $pWorking = trim($pWorking);
        $pWorking = htmlspecialchars_decode($pWorking);
        $pWorking = htmlspecialchars_decode($pWorking);
        $pWorking = strip_tags($pWorking);

        if($pWorking=='End') {
            //echo '<p>encountered "ei8 PlaylistEnd"</p>';
            $content .= $working;
            continue;
        } else {
            //echo '<p>trying to work with the ei8 Playlist"</p>';
        }

        //split up the shortcode into the different values we have to work with
        $values = explode(" ",$pWorking);

        $playlistClass      = 'ei8-embedded-content';
        $myDefaults = array(
            'class' => $playlistClass,
        );
        $myAlign = '';
        foreach($values as $statement) {
            list($name,$val) = explode("=",$statement,2);
            if($name=="align") $name = 'class';
            $val = trim($val);
            $qArr = array("'", '"');
            if(in_array(substr($val, -1),$qArr) && in_array(substr($val,0,1),$qArr)) $val = substr($val,1,-1);
            $myDefaults[trim($name)] = $val;
        }
        //echo "<p>myDefaults<pre>"; print_r($myDefaults); echo "</pre></p>";
        
        $playlistAlign      = ei8_coalesce($myDefaults['class'], ei8_xmlrpc_get_option('ei8_xmlrpc_playlist_align'), 'left');
        if($playlistAlign!='') $playlistClass .= '-'.$playlistAlign;

        //now pull out the other shortcodes from 'the rest' of the content
        //list($working, $other) = explode("[cxl PlaylistEnd]", $theRest, 2);

        $shortcode_bak = "<!--[cxl Playlist ".$pWorking."]".$working."[cxl PlaylistEnd]-->";

        $working_bak = $working;

        //remove unneeded whitespace, ensure correct formatting of needed whitespace
        $working = trim($working);

        //get the parsed shortcodes
        $shortcodes = ei8_xmlrpc_parse_shortcodes($working);

        //only keep the ones we want
        $myShortcodes = array();
        foreach($shortcodes as $shortcode) if($shortcode['values'] && is_array($shortcode['values'])) $myShortcodes[] = $shortcode['values'];

        //set the important defaults
        $url = ei8_xmlrpc_get_first_valid_element_from_array($myShortcodes,'url');
        $urlParts = parse_url($url);
        $host = "https://".ei8_xmlrpc_filter_host($urlParts['host']);
        $url_player = $host."/jw6player/";
        $url_playlist = $host."/jw6playlist/";
        $url_playlistinfo = $host."/jw6playlistinfo/";
        //echo "<p>shortcodes<pre>"; print_r($shortcodes); echo "</pre></p>";
        //echo "<p>myShortcodes<pre>"; print_r($myShortcodes); echo "</pre></p>";
        //echo "<p>urlParts<pre>"; print_r($urlParts); echo "</pre></p>";
        //echo "<p>url: $url<br>url_playlist: $url_playlist<br>url_player: $url_player</p>";

        $defaults = array('class', 'width', 'height', 'skin', 'autostart', 'repeat', 'affiliate', 'guid');
        //$myDefaults = array();
        foreach($defaults as $param) if(!isset($myDefaults[$param])) $myDefaults[$param] = ei8_xmlrpc_get_first_valid_element_from_array($myShortcodes,$param);
        //echo "<p>myDefaults<pre>"; print_r($myDefaults); echo "</pre></p>";

        //set up the query string we will be using
        $query_str = "";
        foreach($myDefaults as $key=>$val) if($key!='class' && $key!='guid') {
            if($query_str!='') $query_str .= "&";
            $query_str .= "$key=$val";
        }
        //$jwplayerEl = "Player".$myDefaults['guid'];
        $jwplayerEl = "Player".uniqid();
        $query_str .= "&playerid=".$jwplayerEl;

        $QS = $query_str;
        foreach($myShortcodes as $myValues) $QS .= "&guid[]=".$myValues['guid'];
        //add back in the other defaults so that they are recognized as the defaults
        $url_player .= urlencode($myDefaults['guid']."&".$query_str);
        $url_playlist .= urlencode($QS);
        $url_playlistinfo .= urlencode($QS);
        //echo "<p>url_player:$url_player</p>";
        //echo "<p>url_playlist:$url_playlist</p>";
        //echo "<p>url_playlistinfo:$url_playlistinfo</p>";

        //get the jwplayer embed code
        //$jwplayer = file_get_contents($url_player);
        //$jwplayer = file_get_contents($url_playlist);
        $jwplayer = ei8XmlrpcFloodgateAPI::load_remote($url_playlist);

        //get the jwplaylist xml code
        //$playlist_xml  = simplexml_load_file($url_playlistinfo);
        $playlist_xml = ei8XmlrpcFloodgateAPI::load_remote_xml($url_playlistinfo);

        //now start building the actual display and js
        $jwplaylist = $jwplaylist2 = $jwplaylist3 = $jwplaylist4 = "";
        //$jwplayerEl = "Player".$myDefaults['guid']; //this was set earlier to allow for multiple players with playlists
        $jwplayerPlaylistEl = $jwplayerEl."Playlist";
        $jwplayerPlaylistIndex = 0;
        $qTipTitle = ei8_xmlrpc_get_option('ei8_xmlrpc_playlist_show_title');
        $qTipDesc = ei8_xmlrpc_get_option('ei8_xmlrpc_playlist_show_description');
        $showQTipTitle = (isset($myDefaults['show_title'])) ? ($myDefaults['show_title']=='true') : ($qTipTitle==1);
        $showQTipDesc = (isset($myDefaults['show_description'])) ? ($myDefaults['show_description']=='true') : ($qTipDesc==1);
        foreach($playlist_xml->media as $media) {
///ADD IN PREFERENCES HERE??
            $myFile1 = $media->sources->source[0]->file;
            $myFile2 = $media->sources->source[1]->file;
            $myImage = $media->image;
            $myTitle = $media->title;
            $myDesc  = $media->description;
            $myTitleSafe = addslashes($myTitle);
            $myDescSafe = addslashes($myDesc);
            $myElId  = $jwplayerEl.$jwplayerPlaylistIndex;
            $jwplaylist .=<<<EOT

            <li>
                <div class='ei8-playlist-item'>
                    <a href="javascript:ei8PlaylistLoad('$jwplayerEl','$myFile1','$myFile2','$myImage')">
                        <div class="ei8-playlist-item-image"><img src='$myImage' border='0'></div>
                        <div class="ei8-playlist-item-info">
                            <div class="ei8-playlist-item-title wrapword">$myTitle</div>
                            <!-- <div class="ei8-playlist-item-description wrapword">$myDesc</div> -->
                        </div>
                    </a>
                </div>
            </li>
EOT;

            $jwplaylist2 .=<<<EOT
                <a href="javascript:ei8PlaylistLoad('$jwplayerEl','$myFile1','$myFile2','$myImage');" title="$myTitleSafe"><img src='$myImage' border='0'></a>
EOT;

/*            $jwplaylist3 .=<<<EOT

                <a href="javascript:ei8PlaylistItem('$jwplayerEl','$jwplayerPlaylistIndex');" title="$myTitleSafe"><img src='$myImage' alt="$myTitleSafe"></a>
EOT;*/

            $jwplaylist3 .=<<<EOT

                <a href="javascript:ei8PlaylistItem('$jwplayerEl','$jwplayerPlaylistIndex');"><img src='$myImage' id='$myElId'></a>
EOT;

            //build qtip content
            //show nothing
            if(!$showQTipTitle && !$showQTipDesc) {
                $jwplaylist4 .= '';

            //show both title and description
            } elseif($showQTipTitle && $showQTipDesc) {
                $jwplaylist4 .=<<<EOT
                    $('#$myElId').qtip({
                        content: {
                            title: '$myTitleSafe',
                            text: '$myDescSafe'
                        },
                        style: { classes: 'qtip-light qtip-rounded ei8t-qtip' }
                    });

EOT;
            //show only the description area of the qtip and put the relevant content type in there
            } else {
                $myContentSafe = ($showQTipDesc) ? $myDescSafe : $myTitleSafe ;

                $jwplaylist4 .=<<<EOT
                    $('#$myElId').qtip({
                        content: {
                            text: '$myContentSafe'
                        },
                        style: { classes: 'qtip-light qtip-rounded ei8t-qtip' }
                    });

EOT;

            }

            $jwplayerPlaylistIndex++;
        }

        //build the final rendering components
        $thePlayer =<<<EOT
    <div class='ei8-playlist-player %playerClass%' style='%playerStyle%'>
        <div class='%class%' style="%playerHeightWidth%">
            %jwplayer%
        </div>
    </div>
EOT;

        $thePreview =<<<EOT
    <div id="%jwplaylistID%" class="jThumbnailScroller %scrollerClass%" style="%scrollerHeightWidth% %previewStyle%">
        <div id="%jwplaylistID%ScrollerContainer" class="jTscrollerContainer">
            <div id="%jwplaylistID%Scroller" class="jTscroller">
                %jwplaylist3%
            </div>
        </div>
        <a href="#" class="jTscrollerPrevButton %scrollerClass%"></a>
        <a href="#" class="jTscrollerNextButton %scrollerClass%"></a>
    </div>
EOT;


        $final =<<<EOT
<script type="text/javascript">
    if(!(typeof(ei8PlaylistLoad) == "function")) {
        function ei8PlaylistLoad(myPlayer,myFile1,myFile2,myImage) {
            jwplayer(myPlayer).load([{
                    sources: [
                        { file: myFile1 },
                        { file: myFile2 },
                    ],
                    image: myImage
            }]);
            jwplayer(myPlayer).play(true);
        }
    }
    if(!(typeof(ei8PlaylistItem) == "function")) {
        function ei8PlaylistItem(myPlayer,myItem) {
            jwplayer(myPlayer).playlistItem(myItem);
            jwplayer(myPlayer).play(true);
        }
    }
    jQuery.noConflict();
    (function($){
        window.onload=function(){
            $("#%jwplaylistID%").thumbnailScroller({
                scrollerType:"clickButtons",
                scrollerOrientation:"%previewOrientation%",
                scrollSpeed:2,
                scrollEasing:"easeOutCirc",
                scrollEasingAmount:600,
                acceleration:4,
                scrollSpeed:800,
                noScrollCenterSpace:50,
                autoScrolling:0,
                autoScrollingSpeed:2000,
                autoScrollingEasing:"easeInOutQuad",
                autoScrollingDelay:500
            });
            $("#%jwplaylistID%ScrollerContainer").width($("#%jwplaylistID%ScrollerContainer").width()+2);
                // Grab all elements with the class "hasTooltip"
            %jwplaylist4%

        }
    })(jQuery);
</script>
<div class='ei8-playlist-container %containerClass%' style='%containerStyle%'>
    %finalPlayer%
</div>
<div style="clear: both;"></div>
<script>
</script>

EOT;

        //determine what is being shown and in what order
        $myPreview = $myDefaults['preview'];
        if(empty($myPreview)) $myPreview = 'bottom';
        $showPreview  = ($myPreview=='none') ? false : true ;
        $showPlayer   = true; //for future manipulation

        $previewFirst = (in_array($myPreview,array('left','top'))) ? true : false;

        $previewOrientation  = (in_array($myPreview,array('left','right'))) ? 'vertical' : 'horizontal' ;

        if ($previewOrientation=='vertical') {
            $playerHeightWidth = $scrollerHeightWidth = 'height:%height%px;';
            //below was an attempt to limit the overall height o
            /*if($myDefaults['width']>300) {
                $w = $myDefaults['width']-118;
                $playerHeightWidth .= 'width:'.$w.'px;';
            }*/
            $scrollerHeightWidth .= 'width:108px;' ;
            $w = $myDefaults['width']+113;
        } else {
            $playerHeightWidth = $scrollerHeightWidth = 'width:%width%px;' ;
            $w = $myDefaults['width'];
        }
        $containerStyle = "width:{$w}px";

        //$previewStyle = ($previewOrientation=='vertical') ? 'width:100px;' : 'height:100px;';
        //$previewMargins = array('left'=>'right', 'right'=>'left', 'top'=>'bottom', 'bottom'=>'top');
        //$previewStyle .= 'margin-'.$previewMargins[$myPreview].':5px;';
        $playerStyle = 'margin-'.$myPreview.':5px;';

        if($showPreview && $showPlayer) {
            $finalPlayer = ($previewFirst) ? $thePreview.$thePlayer : $thePlayer.$thePreview ;
        } elseif($showPlayer) {
            $finalPlayer = $thePlayer;
        } else {
            $finalPlayer = $thePreview;
        }

        $scrollerClassArray = array(
            'jts-'.$previewOrientation,
            'jts-'.$myPreview
        );
        if($playlistAlign!='center') $scrollerClassArray[] = $playlistClass;

        //distill what we actually need now...(and in the right order)
        $myFinalValues = array(
            'finalPlayer'         => $finalPlayer,
            'jwplayer'            => $jwplayer,
            'jwplaylist'          => $jwplaylist,
            'jwplaylist2'         => $jwplaylist2,
            'jwplaylist3'         => $jwplaylist3,
            'jwplaylist4'         => $jwplaylist4,
            'jwplayerID'          => $jwplayerEl,
            'jwplaylistID'        => $jwplayerPlaylistEl,
            //'jwplaylistjs'        => $jwplaylistJS,
            //'class'               => $myDefaults['class'],
            'class'               => $playlistClass,
            'containerClass'      => $playlistClass,
            'playerClass'         => 'ei8-playlist-player-'.$previewOrientation,
            'scrollerClass'       => implode(' ',$scrollerClassArray),
            'playerHeightWidth'   => $playerHeightWidth,
            'scrollerHeightWidth' => $scrollerHeightWidth,
            'previewStyle'        => '',//$previewStyle,
            'previewOrientation'  => $previewOrientation,
            'playerStyle'         => $playerStyle,
            'containerStyle'      => $containerStyle,
            'width'               => $myDefaults['width'],
            'height'              => $myDefaults['height'],
        );

        //swap out the place holders with the actual values
        foreach($myFinalValues as $key=>$val) {
            $replace = "%".$key."%";
            $final = str_replace($replace, $val, $final);
        }

        //$content .= $shortcode_bak.$final.$other;
        $content .= $shortcode_bak.$final;
    }
    return $content;
}


function ei8_xmlrpc_parse_shortcode($content,$type='') {
    $parts = ei8_xmlrpc_explode_string($content);
    $content_bak = $content; //make a copy before we start just in case we need to roll back
    $content = "";
    $playlistBlockSkip = false;
    foreach($parts as $part) {
        //handle the first part that precedes the shortcode
        if(empty($content)) {
            $content = $part;
            continue;
        }

        //now pull out the shortcode from the 'other' part of the content
        list($working, $other) = explode("]", $part, 2);

        $shortcode = "<!--[cxl".$working."]-->";

        $working_bak = $working;

        //remove unneeded whitespace, ensure correct formatting of needed whitespace
        $working = trim($working);
        $working = htmlspecialchars_decode($working);
        $working = htmlspecialchars_decode($working);
        //$working = preg_replace('%\s%',' ',$working);
        //$working = str_replace('&nbsp;',' ',$working);
        //$working = str_replace('  ',' ',$working);
        $working = strip_tags($working);
        //$working = preg_replace('/(\v|\\n|\\r)/','',$working);

        //skip expander stuff
        if(preg_match('/(Expander)/',$working)) {
            $content .= '[cxl'.$part;
            continue;
        }

        //skip embed stuff
        if(preg_match('/(embed)/',$working)) {
            $content .= '[cxl'.$part;
            //echo "<p>FOUND AN [cxl embed] shortcode!!</p>";
            continue;
        }

        //skip playlist blocks and everything in them (because they have already been parsed)
        if(preg_match('/(PlaylistEnd)/',$working)) {
            $playlistBlockSkip = false;
            $content .= '[cxl'.$part;
            continue;
        }
        if($playlistBlockSkip==true || preg_match('/(Playlist)/',$working)) {
            $playlistBlockSkip = true;
            $content .= '[cxl'.$part;
            continue;
        }

        //split up the shortcode into the different values we have to work with
        $values = explode(" ",$working);

        $mediaAlign      = ei8_xmlrpc_get_option('ei8_xmlrpc_media_align');
        $mediaClass      = 'ei8-embedded-content';
        if($mediaAlign!='') $mediaClass .= '-'.$mediaAlign;
        //if($mediaAlign!='') $mediaClass = 'align'.$mediaAlign;


        $myValues = array(
            'class' => $mediaClass,
        );
        $myAlign = '';
        foreach($values as $statement) {
            //handle the first part that is the video url
            if(!strstr($statement,"=")) {
                if(!isset($myValues['url']));
                $statement = 'url='.$statement;
            }

            list($name,$val) = explode("=",$statement,2);
            if($name=='audio') $type='audio';
            if($name=="audio" | $name=="video") $name = 'url';
            //if($name=="align") $myAlign = "style='text-align:".trim($val)."';";
            if($name=='class') continue;
            //elseif($name=="align") $myAlign = trim($val);
            else $myValues[trim($name)] = trim($val);
        }

        $id = "Player".time();
        $final =<<<EOT
<div class='%class%' style="width:%width%px">
%jwplayer%
</div>
EOT;

        $showAffiliate =<<<EOT
    <div class='ei8-affiliate'>
        <a href="http://contentxlerator.com">Powered by Content XLerator</a>
    </div>
EOT;

        //extract height and width from url (and potentially align)
        $urlQueryParts = explode('&', htmlspecialchars_decode($myValues['url']), 2);
        parse_str($urlQueryParts[1], $urlParts);

        //handle audio vs video default dimensions
        $dWidth = ($type=="audio") ? ei8_coalesce(ei8_xmlrpc_get_option('ei8_xmlrpc_default_width_audio'), 500) : ei8_coalesce(ei8_xmlrpc_get_option('ei8_xmlrpc_default_width_video'), 320) ;
        $dHeight = ($type=="audio") ? ei8_coalesce(ei8_xmlrpc_get_option('ei8_xmlrpc_default_height_audio'), 30) : ei8_coalesce(ei8_xmlrpc_get_option('ei8_xmlrpc_default_height_video'), 260);

        //handle necessary defaults
        $myValues['width']  = ei8_coalesce($myValues['width'], $urlParts['width'], $urlParts['w'], ei8_xmlrpc_get_option('ei8_xmlrpc_default_width'), $dWidth);
        $myValues['height'] = ei8_coalesce($myValues['height'], $urlParts['height'], $urlParts['h'], $dHeight);
        //$myValues['affiliate'] = (empty($myValues['affiliate'])) ? "" : $showAffiliate ;
        //$myValues['affiliate'] = (ei8_coalesce($myValues['affiliate'],$urlParts['affiliate'])) ? $showAffiliate : '' ;
        $myValues['hide_affiliate'] = ($myValues['affiliate']) ? "" : 1 ;

        //get the jwplayer embed code
        $guid = ei8_xmlrpc_get_guid_from_url($myValues['url']);
        $urlParts = parse_url($myValues['url']);
        $url = "https://".ei8_xmlrpc_filter_host($urlParts['host'])."/jw6player/".$guid;
        $QS = "";
        foreach($myValues as $key=>$val) {
            if($key=='url') continue;
            $QS .= "&$key=$val";
        }
        $jwplayerEl = "Player".uniqid();
        $QS .= "&playerid=".$jwplayerEl;
        $url .= urlencode($QS);
        //echo "<p>url:$url</p>";

        //handle alignment
        $dAlign = trim(ei8_coalesce($myValues['align'],$urlParts['align'],''));
        if($dAlign!='') $myValues['class'] .= " ei8-align-$dAlign";
        //$myAlign = ($dAlign=='') ? '' : "style='text-align:$dAlign';";

        //distill what we actually need now...
        $myFinalValues = array(
            //'jwplayer'  => file_get_contents($url),
            'jwplayer'  => ei8XmlrpcFloodgateAPI::load_remote($url),
            'class'     => $myValues['class'],
            'width'     => $myValues['width'],
        );

        //swap out the place holders with the actual values
        foreach($myFinalValues as $key=>$val) {
            $replace = "%".$key."%";
            $final = str_replace($replace, $val, $final);
        }

        $content .= $shortcode.$final.$other;
        /*
        $content .= "<small>";
        $content .= "<br>orig:[cxl $working_bak]";
        $content .= "<br>final:[cxl $working]";
        $content .= "<br>url: ".$myValues['url'];
        $content .= "</small>";
        */
    }
    return $content;
}

function ei8_xmlrpc_parse_embed_shortcode($content,$type='') {
    $parts = ei8_xmlrpc_explode_string($content);
    $content_bak = $content; //make a copy before we start just in case we need to roll back
    $content = "";
    $skipFirst = false;
    foreach($parts as $part) {
        //echo "<p>parsing part:: <pre>$part</pre> ::</p>";
        //skip the &nbsp; that was added for xmlrpc acceptance
        if($part=='&nbsp;' || $part=='<p>&nbsp;' || $part=='</p>') {
            $skipFirst = true;
            continue;
        }
        //handle the first part that precedes the shortcode
        if(!$skipFirst && empty($content)) {
            $content = $part;
            continue;
        }


        //now pull out the shortcode from the 'other' part of the content
        list($working, $other) = explode("]", $part, 2);

        $shortcode = "<!--[cxl".$working."]-->";

        $working_bak = $working;

        //remove unneeded whitespace, ensure correct formatting of needed whitespace
        $working = trim($working);
        $working = htmlspecialchars_decode($working);
        $working = htmlspecialchars_decode($working);
        $working = strip_tags($working);

        //skip anything other than embed stuff
        if(!preg_match('/(embed)/',$working)) {
            $content .= '[cxl'.$part;
            //echo "<p>FOUND AN [cxl embed] shortcode!!</p>";
            continue;
        }

        $mediaClass = 'ei8-embedded-content';

        //split up the shortcode into the different values we have to work with
        $values = explode(" ",$working);

        $myValues = array(
            'class' => $mediaClass,
        );
        foreach($values as $statement) {
            if(!strstr($statement,"=")) {
                //if(!isset($myValues['url'])) $name = 'url';
                continue; //malformed expression
            } //
            list($name,$val) = explode("=",$statement,2);
            if($name=='audio') $type='audio';
            if($name=="audio" | $name=="video") $name = 'url';
            //if($name=="align") $myAlign = "style='text-align:".trim($val)."';";
            if($name=='class') continue;
            //elseif($name=="align") $myAlign = trim($val);
            else $myValues[trim($name)] = trim($val);
        }

        //we could parse additional values here and add to the embed code...

        $final = sprintf("<div class='%s'>%s</div>",
                $myValues['class'],
                //file_get_contents($myValues['embed'])
                ei8XmlrpcFloodgateAPI::load_remote($myValues['embed'])
        );

        $content .= $shortcode.$final.$other;
    }
    return $content;
}

function ei8_xmlrpc_explode_string($content,$needle='[cxl') {
    if(strpos($content, $needle)===0) $content = "&nbsp;".$content;
    $parts = explode($needle, $content);
    return $parts;
}


function ei8_xmlrpc_parse_shortcodes($content) {
    $shortcodes = array();
    $parts = ei8_xmlrpc_explode_string($content);
    $content_bak = $content; //make a copy before we start just in case we need to roll back
    $content = "";
    foreach($parts as $part) {
        $part = trim($part);
        //handle the first part that precedes the shortcode
        if(empty($shortcodes) && $part!='') {
            $shortcodes[] = array('pre' => $part);
            //$content = $part;
            continue;
        }

        //now pull out the shortcode from the 'other' part of the content
        list($working, $other) = explode("]", $part, 2);

        $shortcode = "<!--[cxl".$working."]-->";

        $working_bak = $working;

        //remove unneeded whitespace, ensure correct formatting of needed whitespace
        $working = trim($working);
        $working = htmlspecialchars_decode($working);
        $working = htmlspecialchars_decode($working);
        //$working = preg_replace('%\s%',' ',$working);
        //$working = str_replace('&nbsp;',' ',$working);
        //$working = str_replace('  ',' ',$working);
        $working = strip_tags($working);
        //$working = preg_replace('/(\v|\\n|\\r)/','',$working);

        //skip expander stuff
        if(preg_match('/(Expander)/',$working)) {
            $shortcodes[]['pre'] = '[cxl'.$part;
            //$content .= '[cxl'.$part;
            continue;
        }

        //split up the shortcode into the different values we have to work with
        $values = explode(" ",$working);

        $mediaAlign      = ei8_xmlrpc_get_option('ei8_xmlrpc_media_align');
        $mediaClass      = 'ei8-embedded-content';
        if($mediaAlign!='') $mediaClass .= '-'.$mediaAlign;
        //if($mediaAlign!='') $mediaClass = 'align'.$mediaAlign;


        $myValues = array(
            'class' => $mediaClass,
        );
        $myAlign = '';
        foreach($values as $statement) {
            //handle the first part that is the video url
            //if(empty($myValues)) {
            //    $myValues['url'] = $statement;
            //    continue;
            //}
            if(!strstr($statement,"=")) {
                if(!isset($myValues['url']));
                $name = 'url';
            } //continue; //malformed expression
            list($name,$val) = explode("=",$statement,2);
            if($name=='audio') $type='audio';
            if($name=="audio" | $name=="video") $name = 'url';
            //if($name=="align") $myAlign = "style='text-align:".trim($val)."';";
            if($name=='class') continue;
            //elseif($name=="align") $myAlign = trim($val);
            else $myValues[trim($name)] = trim($val);
        }

        //extract height and width from url (and potentially align)
        $urlQueryParts = explode('&', htmlspecialchars_decode($myValues['url']), 2);
        parse_str($urlQueryParts[1], $urlParts);

        //handle audio vs video default dimensions
        $dWidth = ($type=="audio") ? ei8_coalesce(ei8_xmlrpc_get_option('ei8_xmlrpc_default_width_audio'), 500) : ei8_coalesce(ei8_xmlrpc_get_option('ei8_xmlrpc_default_width_video'), 320) ;
        $dHeight = ($type=="audio") ? ei8_coalesce(ei8_xmlrpc_get_option('ei8_xmlrpc_default_height_audio'), 30) : ei8_coalesce(ei8_xmlrpc_get_option('ei8_xmlrpc_default_height_video'), 260);

        //handle necessary defaults
        $myValues['width']  = ei8_coalesce($myValues['width'], $urlParts['width'], $urlParts['w'], ei8_xmlrpc_get_option('ei8_xmlrpc_default_width'), $dWidth);
        $myValues['height'] = ei8_coalesce($myValues['height'], $urlParts['height'], $urlParts['h'], $dHeight);
        $myValues['affiliate'] = (ei8_coalesce($myValues['affiliate'],$urlParts['affiliate']));

        //get the media guid
        $myValues['guid'] = ei8_xmlrpc_get_guid_from_url($myValues['url']);

        //handle alignment
        $dAlign = trim(ei8_coalesce($myValues['align'],$urlParts['align'],''));
        if($dAlign!='') $myValues['class'] .= " ei8-align-$dAlign";

        //put it all into the array
        $shortcodes[] = array(
            'pre' => $shortcode,
            'values' => $myValues,
            'post'   => $other
        );
    }
    return $shortcodes;
}
?>