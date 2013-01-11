<?php
//BEGIN ADMIN SECTION

$optionP = 'ei8-xmlrpc-options';
$optionE = 'ei8-xmlrpc-email-options';

//validate data
add_action('admin_notices', 'ei8_xmlrpc_validate_data' );

function ei8_xmlrpc_validate_data($input) {
    global $optionP, $optionE;
    //validate the email
    $tEmail = ei8_xmlrpc_get_option('ei8_xmlrpc_email_notify');
    if(!empty($tEmail) && !ei8_isValidEmails($tEmail)) {
        echo "<div id='akismet-warning' class='error fade'><b>At least one of the notification email addresses are not valid.  <a href='$optionP'>Please fix or email notifications will not be received. </a>($tEmail)</b></div>";
    }
    //validate the email
    $tEmail = ei8_xmlrpc_get_option('email_from_addr');
    if(!empty($tEmail) && !ei8_isValidEmail($tEmail)) {
        echo "<div id='akismet-warning' class='error fade'><b>The email notification 'From' address is is not valid.  <a href='$optionE'>Please fix or your emails may be marked as spam. </a>($tEmail)</b></div>";
    }

    //validate the ping url
    $tPing = ei8_xmlrpc_get_option('ei8_xmlrpc_ping');
    if(!empty($tPing) && !ei8_isValidUrl($tPing)) {
        echo "<div id='akismet-warning' class='error fade'><b>This is not a valid URL to be pinged.  <a href='$optionP'>Please fix or ping notifications will not be sent. </a>($tPing)</b></div>";
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
    return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email);
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
        array_unshift( $links, sprintf( '<a href="options-general.php?page=%s">%s</a>', 'ei8-xmlrpc-options', __('Settings') ) );
    }
    return $links;
}


//create options page
add_action('admin_menu', 'ei8_xmlrpc_options_menu');

function ei8_xmlrpc_options_menu() {
    global $optionP, $optionE;
    $hideOptions = ei8_xmlrpc_get_option('ei8_xmlrpc_hide_admin_options');
    if(empty($hideOptions) || current_user_can('edit_users')) {
        add_menu_page('eInnov8 Settings', 'eInnov8 Options', 'edit_others_posts', $optionP, 'ei8_xmlrpc_admin_options');
        add_submenu_page( $optionP, 'eInnov8 Settings', 'Preferences', 'edit_others_posts', $optionP, 'ei8_xmlrpc_admin_options');
        add_submenu_page( $optionP, 'ei8 Email Options', 'Email Notifications', 'edit_others_posts', $optionE, 'ei8_xmlrpc_email_options');
        add_submenu_page( $optionP, 'ei8 Shortcodes', '[ei8 shortcodes]', 'activate_plugins', 'ei8-shortcodes', 'ei8_xmlrpc_shortcode_options');
        add_submenu_page( $optionP, 'ei8 CSS', '[ei8 css]', 'activate_plugins', 'ei8-css', 'ei8_xmlrpc_css_options');
    }

    //add_menu_page(THEMENAME . ' Theme Options', THEMENAME . ' Options', 'manage_options', THEMESLUG . 'options', array( &$this, 'engipress_do_overpage' ) );
    //add_submenu_page(THEMESLUG . 'options', THEMENAME . ' Theme Options', '', 'manage_options', THEMESLUG . 'options', array( &$this, 'engipress_do_overpage' ));
}


function ei8_get_post_types() {
    $reg_post_types    = get_post_types();
    $skip_post_types = array('attachment','revision','nav_menu_item');
    $post_types = array();
    foreach($reg_post_types as $post_type) {
        if(!in_array($post_type,$skip_post_types)) $post_types[] = $post_type;
    }
    return $post_types;
}

function ei8_xmlrpc_shortcode_options() {
?>
<div class="wrap">
    <?php ei8_screen_icon(); ?>

    <h2>Shortcodes Options:</h2>
    <table class="form-table">
        <tr><td colspan=2><strong>Shortcodes are tags that can be pasted into any page or post to automatically include formatted content or functionality from ei8t.com.<br>
            These shortcodes bypass many wordpress mechanisms that can filter or alter pasted html code.</strong></td></tr>
        <tr valign="top">
            <th scope="row">Static tags:</th>
            <td>
                [ei8 MiniRecorder]<br>
                [ei8 WideRecorder]<br>
                [ei8 TallRecorder]<br>
                [ei8 Simple Submit Form]<br>
                [ei8 Attachment Submit Form]<br>
                [ei8 Twitter Button]<br>
                [ei8 Twitter Form]<br>
                [ei8 MediaUploader]<br><br>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Expander tags:</th>
            <td>
                <strong>NOTE: for the expander tags to work properly, there must be an ExpanderTitle properly placed within an ExpanderBody.<br>
                    Spacing and line breaks do not matter<br>
                    You may be able to copy and paste from the example below, or you may need to contact eInnov8 for technical assistance</strong><br>
                [ei8 ExpanderBody]<br>
                [ei8 ExpanderTitle]Some title[ei8 ExpanderTitleEnd]<br>
                Some content here...as much as you want!!<br>
                could even be another or multiple shortcode(s)<br>
                [ei8 ExpanderBodyEnd]<br><br>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Recorder/Uploader destination folder override examples: </th>
            <td>
                <strong>Note: this can be used with the following shortcodes [ei8 MiniRecorder], [ei8 WideRecorder], [ei8 TallRecorder], and [ei8 MediaUploader]</strong><br>
                [ei8 MiniRecorder v=8hvJLMMDDr9&a=d3JSVK4zFLd] <i>(simple copy and paste from ei8t)</i><br>
                [ei8 MiniRecorder v=8hvJLMMDDr9 a=d3JSVK4zFLd] <i>(separated video and audio statements)</i><br>
                [ei8 TallRecorder v=8hvJLMMDDr9] <i>(only override the video...audio follows default settings)</i><br>
            </td>
        </tr>
        <tr><td colspan=2><strong>The following are samples of video and audio shortcodes that can be copied from ei8t.com:</strong></td></tr>
        <tr valign="top">
            <th scope="row">Video example: </th>
            <td>
                [ei8 url=http://www.ei8t.com/swf/9xFKFDWxn2y&w=420&h=335&bm=td&cp=FF6600-800080-FFFF00-000000]<br>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Audio example: (with affiliate link) </th>
            <td>
                [ei8 url=http://www.dev.ei8t.com/swf/wq3HXt4Jz&w=500&h=20&bm=td&cp=000000-FFFFFF-000000-000000 affiliate=1]
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Alignment example: (left/center/right)</th>
            <td>
                [ei8 url=http://www.dev.ei8t.com/swf/wq3HXt4Jz&w=500&h=20&bm=td&cp=000000-FFFFFF-000000-000000 align=left]
            </td>
        </tr>
    </table>
</div>
<?php
}

function ei8_screen_icon($icon='') {
    if($icon=='') $icon = 'icon-options-general';
    echo '<div id="'.$icon.'" class="icon32"><br></div>';
}

function ei8_xmlrpc_css_options() {
?>
<div class="wrap">

    <?php ei8_screen_icon(); ?>

    <h2>CSS Options:</h2>
    <table class="form-table">
        <tr><td colspan=2><strong>Shortcodes now use css stylings to allow for greater compatibility within each website.<br>
            You can look at the included css and overwrite any of the styles as you see fit by updating the css files within your chosen theme.<br><br>
            DO NOT MAKE ANY CHANGES TO THE INCLUDED PLUGIN CSS FILES OR YOUR CHANGES WILL BE OVERWRITTEN WITH THE NEXT PLUGIN UPDATE<br>
            <a href="<?php echo ei8_plugins_url('/ei8-xmlrpc-notifier.css'); ?>" target="_blank">Click here to access the included css.</a></strong></td>
        </tr>
        <tr><td colspan=2>Additionally, the media uploader ([ei8 MediaUploader]) is loaded in an iFrame directly from ei8t.com<br>
            This can be styled as using an external css file, that is pasted into the preferences page.<br>
            There is an included default css file that you can look at and use as a guide, <br>
            <strong>but please note that your new external file will replace this default file entirely</strong><br>
            <a href="<?php echo ei8_plugins_url('/ei8-file-uploader.css'); ?>" target="_blank">Click here to access the default media uploader css.</a></strong></td>
        </tr>
    </table>
</div>
<?php
}

function ei8_xmlrpc_admin_options() {
    global $optionP;
    $postStatus      = ei8_xmlrpc_get_option('ei8_xmlrpc_post_status');
    $postType        = ei8_xmlrpc_get_option('ei8_xmlrpc_post_type');
    $mediaAlign      = ei8_xmlrpc_get_option('ei8_xmlrpc_media_align');
    $ei8AdminUrl     = "admin.php?page=".$optionP;

    if($_POST['action']=="update") {
        //print_r($_POST);
        $var = 'ei8_xmlrpc_post_status';
        ei8_xmlrpc_update_option($var, $_POST[$var]);

        $var = 'ei8_xmlrpc_post_type';
        ei8_xmlrpc_update_option($var, $_POST[$var]);

        $var = 'ei8_xmlrpc_email_notify';
        ei8_xmlrpc_update_option($var, $_POST[$var]);

        $var = 'ei8_xmlrpc_ping';
        ei8_xmlrpc_update_option($var, $_POST[$var]);

        if (current_user_can('edit_others_posts')) {
            $var = 'ei8_xmlrpc_site_type';
            ei8_xmlrpc_update_option($var, $_POST[$var]);

            $var = 'ei8_xmlrpc_recorder_vars';
            ei8_xmlrpc_update_option($var, ei8_xmlrpc_parse_recorder_vars($_POST[$var]));

            $var = 'ei8_xmlrpc_submit_form';
            ei8_xmlrpc_update_option($var, $_POST[$var]);

            //$var = 'ei8_xmlrpc_use_captcha';
            //ei8_xmlrpc_update_option($var, $_POST[$var]);

            $var = 'ei8_xmlrpc_file_uploader_css';
            ei8_xmlrpc_update_option($var, $_POST[$var]);

            $var = 'ei8_xmlrpc_media_align';
            ei8_xmlrpc_update_option($var, $_POST[$var]);

            $var = 'ei8_xmlrpc_hide_admin_options';
            ei8_xmlrpc_update_option($var, $_POST[$var]);

/*
             * $var = 'ei8_xmlrpc_default_width_audio';
            $val = $_POST[$var];
            if(intval($val)<1) $val = '';
            ei8_xmlrpc_update_option($var, $val);

            $var = 'ei8_xmlrpc_default_width_video';
            $val = $_POST[$var];
            if(intval($val)<1) $val = '';
            ei8_xmlrpc_update_option($var, $val);
*/
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

    $hideAdmin       = ei8_xmlrpc_get_option('ei8_xmlrpc_hide_admin_options');
    $postStatus      = ei8_xmlrpc_get_option('ei8_xmlrpc_post_status');
    $postType        = ei8_xmlrpc_get_option('ei8_xmlrpc_post_type');
    $post_types      = ei8_get_post_types();
    $mediaAlign      = ei8_xmlrpc_get_option('ei8_xmlrpc_media_align');
    $align_options   = array('left','center','right');

?>
<div class="wrap">
    <?php ei8_screen_icon(); ?>

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
                <th scope="row">Post type to use: </th>
                <td><select name='ei8_xmlrpc_post_type'>
                    <?php
                    foreach ($post_types as $post_type ) {
                        $selected = ($post_type==$postType || (empty($postType) && $post_type=="post")) ? "SELECTED" : "" ;
                        echo "<option value=\"$post_type\" $selected>$post_type</option>";
                    }
                    ?>
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
            if (current_user_can('edit_others_posts')) {
                $siteType           = ei8_xmlrpc_get_site_type();
                //$useCaptcha         = ei8_xmlrpc_get_option('ei8_xmlrpc_use_captcha');
                $f_submitForm       = 'ei8_xmlrpc_submit_form';
                $v_submitForm       = ei8_xmlrpc_get_option($f_submitForm);
                $f_recorderVars     = 'ei8_xmlrpc_recorder_vars';
                $v_recorderVars     = ei8_xmlrpc_get_option($f_recorderVars);

                $f_twitterUser      = 'ei8_xmlrpc_twitter_username';
                $v_twitterUser      = ei8_xmlrpc_get_option($f_twitterUser);
                $f_twitterPass      = 'ei8_xmlrpc_twitter_password';
                $v_twitterPass      = ei8_xmlrpc_get_option($f_twitterPass);
                $f_uploaderCSS      = 'ei8_xmlrpc_file_uploader_css';
                $v_uploaderCSS      = ei8_xmlrpc_get_option($f_uploaderCSS);
                if(empty($v_submitForm)) $v_submitForm = '/submit/' ;

                //default heights and widths for video and audio playback using ei8 shortcodes
                $f_defaultWidthAudio    = 'ei8_xmlrpc_default_width_audio';
                $v_defaultWidthAudio    = ei8_coalesce(ei8_xmlrpc_get_option($f_defaultWidthAudio), 500);
                //$f_defaultHeightAudio   = 'ei8_xmlrpc_default_height_audio';
                //$v_defaultHeightAudio   = ei8_coalesce(ei8_xmlrpc_get_option($f_defaultHeightAudio), 20);
                $f_defaultWidthVideo    = 'ei8_xmlrpc_default_width_video';
                $v_defaultWidthVideo    = ei8_coalesce(ei8_xmlrpc_get_option($f_defaultWidthVideo), 320);
                //$f_defaultHeightVideo   = 'ei8_xmlrpc_default_height_video';
                //$v_defaultHeightVideo   = ei8_coalesce(ei8_xmlrpc_get_option($f_defaultHeightVideo), 260);

?>
            <tr><td><h3>Admin Specific Settings</h3></td></tr>
            <tr valign="top">
                <th scope="row">Show eInnov8 Options:</th>
                <td><select name='ei8_xmlrpc_hide_admin_options'>
                    <option value="" <?php if(empty($hideAdmin)) echo "SELECTED"; ?>>visible to ALL(Authors, Editors, & Administrators)</option>
                    <option value="admin" <?php if(!empty($hideAdmin)) echo "SELECTED"; ?>>visible only to Administrators</option>
                    </select></td>
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
                        require 'lib/EpiSequence.php';
                        require 'lib/EpiTwitter.php';
                        require 'lib/secret.php';

                        //protected $callback = 'http://einnov8.com';


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

                            /*
                            print("<p>TwitterObj: <pre>");
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
                <!--<tr valign="top">
                    <th scope="row">Require CAPTCHA on submit forms: </th>
                    <td><?php echo ei8_xmlrpc_form_boolean('ei8_xmlrpc_use_captcha',$useCaptcha); ?></td>
                </tr>-->
                <tr valign="top">
                    <th scope="row">Media uploader custom css:</th>
                    <td><?php echo ei8_xmlrpc_form_text($f_uploaderCSS,$v_uploaderCSS); ?><br>
                        <small>ex. http://www.einnov8.com/css/media_uploader.css</small>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Default media alignment:</th>
                    <td><select name='ei8_xmlrpc_media_align'>
<?php
                        foreach ($align_options as $align ) {
                            $selected = ($align==$mediaAlign || (empty($mediaAlign) && $align=="left")) ? "SELECTED" : "" ;
                            echo "<option value=\"$align\" $selected>$align</option>";
                        }
?>
                        </select></td>
                </tr>
                <!--<tr valign="top">
                    <th scope="row">Default shortcode video width:</th>
                    <td><?php echo ei8_xmlrpc_form_text($f_defaultWidthVideo,$v_defaultWidthVideo); ?></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Default shortcode audio width:</th>
                    <td><?php echo ei8_xmlrpc_form_text($f_defaultWidthAudio,$v_defaultWidthAudio); ?></td>
                </tr>-->
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
function ei8_xmlrpc_email_options() {
    global $optionE;
    $ei8AdminUrl     = "admin.php?page=".$optionE;
    $defaultSettings = ei8_xmlrpc_get_message_defaults($siteType);

    if($_POST['action']=="update") {
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

        ei8_xmlrpc_admin_log("<p>Your email notification preferences have been updated.</p>",1);

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
?>
<div class="wrap">
    <?php ei8_screen_icon(); ?>

    <h2>EmailPreferences:</h2>
    <form method="post" action="<?php echo $ei8AdminUrl; ?>">
        <?php wp_nonce_field('update-options'); ?>
        <table class="form-table">
            <tr><td><h3>Notification Email Settings</h3></td></tr>
            <!--<tr valign="top">
                <th scope="row">Website type: </th>
                <td><select name='ei8_xmlrpc_site_type'>
                    <option value="boon" <?php if('boon'==$siteType) echo "SELECTED"; ?>><?php echo ei8_xmlrpc_get_site_type_name('boon'); ?></option>
                    <option value="flood" <?php if('flood'==$siteType) echo "SELECTED"; ?>><?php echo ei8_xmlrpc_get_site_type_name('flood'); ?></option>
                    <option value="videoaudio" <?php if('videoaudio'==$siteType) echo "SELECTED"; ?>><?php echo ei8_xmlrpc_get_site_type_name('videoaudio'); ?></option>
                    <option value="soupedup" <?php if('soupedup'==$siteType) echo "SELECTED"; ?>><?php echo ei8_xmlrpc_get_site_type_name('soupedup'); ?></option>
                </select></td>
            </tr>-->
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
        </table>
        <input type="hidden" name="action" value="update">
        <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
    </form>
</div>
<?php
}

function ei8_xmlrpc_form_text($var,$val) {
    $html = '<input type="text" name="'.$var.'" size=65 value="'.$val.'" />';
    return $html;
}

function ei8_xmlrpc_form_textarea($var,$val,$rows='') {
    $showRows = ($rows=='') ? '' : 'rows="'.$rows.'"';
    $html = '<textarea class="ei8-textarea" cols=65 name="'.$var.'" '.$showRows.'>'.$val.'</textarea>';
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
    return 'flood';
    /*
    $siteType = ei8_xmlrpc_get_option('ei8_xmlrpc_site_type');
    if(empty($siteType)) {
        $domain = $_SERVER['HTTP_HOST'];
        if(strstr($domain,'1tme1'))     $siteType = 'boon';
        elseif(strstr($domain,'1wcf1')) $siteType = 'flood';
        elseif(strstr($domain,'1vaf1')) $siteType = 'videoaudio';
        elseif(strstr($domain,'1blg1')) $siteType = 'soupedup';
        elseif(strstr($domain,'local')) $siteType = 'flood';
        else                          $siteType = 'boon';
    }
    return $siteType;
    */
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
    foreach(explode(".",$_SERVER['HTTP_HOST']) AS $part) $passWord = substr($part,0,4).$passWord;
    $passWord = "xmlrpc".$passWord;

    //ensure user exists and has the correct password and permissions
    $userID = username_exists( $userName );
    $userInfo = array(
        'ID'         => $userID,
        'user_login' => $userName,
        'user_pass'  => $passWord,
        'user_email' => 'xmlrpc@ei8t.com',
        'role'       => 'author'
    );
    //check user permissions if user exists
    if($userID) $userData = get_userdata($userID);

    //only do the update if necessary
    if( !$userID || $userData->user_pass!=wp_hash_password($passWord) || !user_can($userID,'publish_posts') ) {
        wp_update_user( $userInfo );
    };

    return array($userName, $passWord);
}

function ei8_xmlrpc_get_site_type_name($siteType='') {
    //overrride siteType
    $siteType = 'floodtech';

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
            $siteName = "testiBOONials";
        default:
            $siteName = "Floodgate";
    }

    return $siteName;
}

function ei8_xmlrpc_get_message_defaults($siteType='') {
    $siteType = 'floodgate';
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
    } else {

        $defaults['email_from_name']             = "Website Name";
        $defaults['email_from_addr']             = "submit@sitename.com";
        $defaults['email_subject']               = "New Floodtech Submission";
        $defaults['message_intro']               = "A new Floodtech submission has arrived at your website with this title:
[[post_title]]";

        $defaults['message_post_status_intro']   = "This submission";
        $defaults['message_post_status_draft']   = " is waiting for review within the your website administration area.";
        $defaults['message_post_status_publish'] = " has been published as a post on your website.";
        $defaults['message_post_status_unknown'] = " is available for review within your website administration area.";

        $defaults['message_thank_you']           = "Thank you for being a customer of eInnov8 Marketing.";
        $defaults['message_quick_links_show']    = 1;
        $defaults['message_quick_links_intro']   = "Click on this link (and log in, if necessary) to review, edit and publish this submission:";
        $defaults['message_referral_show']       = 1;
        $defaults['message_referral_text']       = "Learn more about us at http://einnov8.com.";
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
    $updatedSQL = true;
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
        $updatedSQL = false;
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
        ei8_xmlrpc_admin_log("<p class='abq-error'>Error: uploads directory cannot be created.  <br><span style='color: black;'>Use this command to create the directory: mkdir $uploadPath</span></p>",1);
    }
    if(!is_writable($uploadPath)) @chmod($uploadPath, 0777);
    if(!is_writable($uploadPath)) {
        ei8_xmlrpc_admin_log("<p class='abq-error'>Error: uploads directory is not writable.  <br><span style='color: black;'>Use this command to make it writable: chmod -R 777 $uploadPath</span></p>",1);
    }

    //try to make sure xml-rpc is enabled
    update_site_option('enable_xmlrpc',1);
}

function ei8_get_web_root() {
    list($webRoot,$discard) = explode('wp-admin',$_SERVER['SCRIPT_FILENAME']);
    return $webRoot;
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