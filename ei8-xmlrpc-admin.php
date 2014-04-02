<?php
//Load all classes in the lib directory
$dir = dirname(__FILE__).'/lib';
$libFiles = scandir($dir);
foreach($libFiles as $libFile) if(strstr($libFile,'ei8Xmlrpc')) {
    //ei8_xmlrpc_admin_log("<p>Loading $libFile</p>");
    require_once($dir.'/'.$libFile);
}

//BEGIN ADMIN SECTION

//validate data
add_action('admin_notices', 'ei8_xmlrpc_validate_data' );

function ei8_xmlrpc_validate_data($input) {
    global $optionP, $optionE;
    $form = new ei8XmlrpcFloodgateFormHandler();

    //validate the email
    $tEmail = ei8_xmlrpc_get_option('ei8_xmlrpc_email_notify');
    //if(!empty($tEmail) && !ei8_isValidEmails($tEmail)) {
    if(!empty($tEmail)) {
        $f = new ei8XmlrpcFloodgateFormFieldTextEmail('test',$tEmail);
        if(!$f->validate()) echo "<div id='akismet-warning' class='error fade'><b>At least one of the notification email addresses are not valid.  <a href='$optionP'>Please fix or email notifications will not be received. </a>($tEmail)</b></div>";
    }
    //validate the email
    $tEmail = ei8_xmlrpc_get_option('email_from_addr');
    //if(!empty($tEmail) && !ei8_isValidEmail($tEmail)) {
    if(!empty($tEmail)) {
        $f = new ei8XmlrpcFloodgateFormFieldTextEmail('test',$tEmail);
        if(!$f->validate()) echo "<div id='akismet-warning' class='error fade'><b>The email notification 'From' address is is not valid.  <a href='$optionE'>Please fix or your emails may be marked as spam. </a>($tEmail)</b></div>";
    }

    //validate the ping url
    $tPing = ei8_xmlrpc_get_option('ei8_xmlrpc_ping');
    //if(!empty($tPing) && !ei8_isValidUrl($tPing)) {
    if(!empty($tPing)) {
        $f = new ei8XmlrpcFloodgateFormFieldTextUrl('test',$tPing);
        if(!$f->validate()) echo "<div id='akismet-warning' class='error fade'><b>This is not a valid URL to be pinged.  <a href='$optionP'>Please fix or ping notifications will not be sent. </a>($tPing)</b></div>";
    }

}

/* moved to ei8XmlrpcFloodgateForm class...left here in case they are still needed
function ei8_isValidEmails($email) {
    //moved to oop
    $form = new ei8XmlrpcFloodgateForm();
    return $form->validate_emails($email);
}

function ei8_isValidEmail($email){
    //moved to oop
    $form = new ei8XmlrpcFloodgateForm();
    return $form->validate_email($email);
}

function ei8_isValidUrl($url){
    //moved to oop
    $form = new ei8XmlrpcFloodgateForm();
    return $form->validate_url($url);
}
*/

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
    global $optionP, $optionE, $optionF, $optionL;
    $hideOptions = ei8_xmlrpc_get_option('ei8_xmlrpc_hide_admin_options');
    if(empty($hideOptions) || current_user_can('edit_users')) {
        add_menu_page('eInnov8 Settings', 'eInnov8 Options', 'edit_others_posts', $optionP, 'ei8_xmlrpc_admin_options');
        add_submenu_page( $optionP, 'eInnov8 Settings', 'Preferences', 'edit_others_posts', $optionP, 'ei8_xmlrpc_admin_options');
        add_submenu_page( $optionP, 'ei8 Email Options', 'Email Notifications', 'edit_others_posts', $optionE, 'ei8_xmlrpc_email_options');
        add_submenu_page( $optionP, 'ei8 Floodgate Settings', 'Floodgate Settings', 'edit_others_posts', $optionF, 'ei8_xmlrpc_floodgate_settings');
        add_submenu_page( $optionP, 'ei8 Embed Settings', 'Embed Settings', 'edit_others_posts', $optionL, 'ei8_xmlrpc_legacy_settings');
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
        <tr><td colspan=2><h3>Shortcodes for Recording or Uploading</h3></td></tr>
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
                <span style='font-weight: bold'>
                    <ul style='list-style: disc;'>NOTE: for expander tags to work properly:
                        <li style="margin-left: 40px">There must be an ExpanderTitle properly placed within an ExpanderBody</li>
                        <li style="margin-left: 40px">Spacing and line breaks do not matter</li>
                        <li style="margin-left: 40px">You may be able to copy and paste from the example below, or you may need to contact eInnov8 for technical assistance</li>
                    </ul>
                </span>
                [ei8 ExpanderBody]<br>
                [ei8 ExpanderTitle]Some title[ei8 ExpanderTitleEnd]<br>
                Some content here...as much as you want!!<br>
                could even be another or multiple shortcode(s)<br>
                [ei8 ExpanderBodyEnd]<br><br>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Recorder/Uploader custom folder examples:</th>
            <td>
                <strong>Note: this can be used with the following shortcodes [ei8 MiniRecorder], [ei8 WideRecorder], [ei8 TallRecorder], and [ei8 MediaUploader]</strong><br>
    <?php
        $recorderOptions = array('MiniRecorder', 'WideRecorder', 'TallRecorder', 'MediaUploader');
        $ro = 0;
        $roCt = count($recorderOptions);
        $customFolders = ei8_xmlrpc_getCustomFolders();
        foreach($customFolders as $folder => $info) {
            if($ro>=$roCt) $ro=0;
            echo sprintf("[ei8 %s cf=%s]<br>", $recorderOptions[$ro], $folder);
            $ro++;
        }
    ?><br><br>
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

        <tr><td colspan=2><h3>Shortcodes for Media Playback</h3></td></tr>
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
        <tr valign="top">
            <th scope="row">Playlist example:</th>
            <td>
                <span style='font-weight: bold'>
                    <ul style='list-style: disc;'>NOTE: for the playlists to work properly:
                        <li style="margin-left: 40px">There can be as many items in the playlist as you wish, these are added as normal [ei8 url=...] tags</li>
                        <li style="margin-left: 40px">There must be a PlaylistEnd tag properly placed at the end of the playlist.</li>
                        <li style="margin-left: 40px">Spacing and line breaks do not matter</li>
                        <li style="margin-left: 40px">Any text or other content contained within a playlist block will be ignored</li>
                        <li style="margin-left: 40px">You may be able to copy and paste from the example below, or you may need to contact eInnov8 for technical assistance</li>
                    </ul>
                </span>
                [ei8 Playlist]<br>
                [ei8 url=http://www.dev.ei8t.com/swf/d3tRs77ffYq width=352 height=284 skin=black]<br>
                [ei8 url=http://www.dev.ei8t.com/swf/fj4Wskcpq8J width=320 height=260 skin=black]<br>
                [ei8 url=http://www.dev.ei8t.com/swf/bMZNkrjWQ38 width=320 height=260 skin=black]<br>
                [ei8 url=http://www.dev.ei8t.com/swf/3gVsJFwcxHN width=320 height=260 skin=black]<br>
                [ei8 PlaylistEnd]
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Playlist customization:</th>
            <td>
                <span>
                    <ul style='list-style: disc;'>The following are variables you can set within the [ei8 Playlist] tag
                        <li style="margin-left: 40px"><strong>align</strong> [options: left, right, center] <i>(overrides default playlist alignment)</i></li>
                        <li style="margin-left: 40px"><strong>width</strong> [any numeric pixel width]</li>
                        <li style="margin-left: 40px"><strong>autoplay</strong> [options: true, false] <i>(default: true)</i></li>
                    </ul>
                </span>
                [ei8 Playlist <strong>align=right width=600 autoplay=false</strong>]<br>
                ...[ei8 shortcodes go here]...<br>
                [ei8 PlaylistEnd]
            </td>
        </tr>
    </table>
</div>
<?php
}

function ei8_xmlrpc_floodgate_get_tab($currentTab='') {
    list($fgTabs,$fgTabUrl) = ei8_xmlrpc_floodgate_get_tab_settings();
    if($currentTab=='') $currentTab = $_GET['fgTab'];
    if($currentTab=='') $currentTab = 'settings';

    $currentTitle   = $fgTabs[$currentTab];
    $currentUrl     = $fgTabUrl.$currentTab;

    return array($currentTab,$currentTitle,$currentUrl);
}

function ei8_xmlrpc_floodgate_get_tab_settings() {
    global $optionF;
    $fgTabs = array(
        'settings'  => 'Settings',
        'order'     => 'Order Navigation',
        'text'      => 'Main Text',
    );
    $fgTabUrl   = "admin.php?page=$optionF&fgTab=";
    return array($fgTabs,$fgTabUrl);
}

function ei8_xmlrpc_floodgate_show_tabs() {
    list($currentTab) = ei8_xmlrpc_floodgate_get_tab();
    list($fgTabs,$fgTabUrl) = ei8_xmlrpc_floodgate_get_tab_settings();

    $nav = '<h2 class="nav-tab-wrapper">';
    foreach($fgTabs as $tab=>$title) {
        $showActive = ($currentTab==$tab) ? 'nav-tab-active' : '' ;
        $nav .= sprintf('<a href="%s" class="nav-tab %s">%s</a>', $fgTabUrl.$tab, $showActive, esc_html($title));
    }
    $nav .= '</h2>';
    echo $nav;
}

function ei8_xmlrpc_floodgate_settings() {
    $form = new ei8XmlrpcFloodgateFormHandler();
    list($currentTab,$currentTitle,$ei8AdminUrl) = ei8_xmlrpc_floodgate_get_tab();

    $form_process   = 'ei8_xmlrpc_floodgate_process_'.$currentTab;
    $form_render    = 'ei8_xmlrpc_floodgate_render_'.$currentTab;

    //form processing
    if(!empty($_POST['tabaction'])) {

        $form_process();

        //force page reload
        $form->redirect($ei8AdminUrl,1);

    }

    //now render the form
?>
<div class="wrap">
<?php ei8_screen_icon(); ?>
<?php ei8_xmlrpc_floodgate_show_tabs(); ?>
<form method="post" action="<?php echo $ei8AdminUrl; ?>">
    <?php /*wp_nonce_field('update-options');*/ ?>
    <?php $form_render(); ?>
    <input type="hidden" name="tabaction" value="process" />
    <?php if($currentTab!='order') { ?><p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p><?php } ?>
</form>
</div>
<?php
}


function ei8_xmlrpc_floodgate_process_settings() {
    global $floodgateOptions;
    $floodgateTargetVarPre = 'ei8_xmlrpc_floodgate_target_';

    //process floodgate options
    ei8_xmlrpc_admin_log("<p>Processing floodgate options.</p>");
    $op = new ei8XmlrpcFloodgateOptionFG();
    $floodgateName = ei8_xmlrpc_floodgate_get_name();
    foreach($floodgateOptions as $option) {
        $var = $op->build_name($option);
        ei8_xmlrpc_admin_log("<p>Processing floodgate option: $option($var).</p>");
        $op->set($var,stripslashes($_POST[$var]));
        $op->update();
        //$var = ei8_xmlrpc_build_floodgate_option_name($option);
        //ei8_xmlrpc_update_option($var, $_POST[$var]);
    }
    if ($floodgateName != ei8_xmlrpc_floodgate_get_name())  ei8_xmlrpc_floodgate_update_endpoint();

    //process floodgate targets
    $targets = array();
    foreach($_POST as $v=>$val) {
        if(!strstr($v,$floodgateTargetVarPre)) continue;
        list($ignore,$ident) = explode($floodgateTargetVarPre,$v);
        list($id,$var) = explode('_',$ident,2);
        if(!in_array($id,array_keys($targets))) {
            $targets[$id] = new ei8XmlrpcFloodgateTarget();
            $targets[$id]->id = $id;
            $targets[$id]->dbdata = new ei8XmlrpcFloodgateTarget($id);
            $targets[$id]->orderer = $targets[$id]->dbdata->orderer;
        }
        $targets[$id]->$var = $val;
        /*ei8_xmlrpc_admin_log("<p>Processing submitted target:
            <br>floodgateTargetVarPre: $floodgateTargetVarPre
            <br>v: $v
            <br>ignore: $ignore
            <br>ident: $ident
            <br>id: $id
            <br>var: $var
            <br>val: $val</p>");*/
    }
    $showTargets = print_r($targets,true);
    ei8_xmlrpc_admin_log("<p>(".count($targets).") floodgate targets found to process.</p>");
    //ei8_xmlrpc_admin_log("<p>Targets <pre>$showTargets</pre></p>");
    if(count($targets)>=1) foreach($targets as $id=>$target) {
        if($id=='new') {
            $fgT = new ei8XmlrpcFloodgateTargets();
            $rT = $fgT->remoteTargets[$target->target];
            //echo "<p>New Target<pre>"; print_r($rT); echo "</pre></p>"; exit;
            $target->media_type = $rT->media_type;
            if(empty($target->title)) $target->title = $rT->title;
        } //else $target->target = $target->dbdata->target;

        //echo "<p>Updating Target<pre>"; print_r($target); echo "</pre></p>";
        if(empty($target->title)) $target->delete();
        else $target->update();
    }
    //ei8_xmlrpc_admin_log("<p>Your Floodgate Settings have been updated.</p>",1);
}

function ei8_xmlrpc_floodgate_process_order() {
    //NOTE: this is handled by AJAX
    //ei8_xmlrpc_admin_log("<p>Your Navigation Order has been updated.</p>",1);

    parse_str($_POST['order'], $data);

    if (is_array($data)) {
        foreach($data as $key => $values ) {
            echo "<p>processing data($key)<pre>"; print_r($values); echo "</pre></p>";
            $type = str_replace('item_','',$key);
            foreach( $values as $position => $id ) {
                $ft = new ei8XmlrpcFloodgateTarget($id);
                $ft->update_order($type, $position);
            }
        }
    }
    die();
}
add_action('wp_ajax_update-floodgate-type-order', 'ei8_xmlrpc_floodgate_process_order' );


function ei8_xmlrpc_floodgate_process_text() {
    $fgTypes = ei8_xmlrpc_floodgate_get_media_types(1);
    $fgVarPre = 'ei8_xmlrpc_floodgate_text_';

    foreach($fgTypes as $type=>$title) {
        $var = $fgVarPre.$type;
        ei8_xmlrpc_update_option($var, $_POST[$var], FALSE);
    }

    ei8_xmlrpc_admin_log("<p>Your Floodgate Text Settings have been updated.</p>",1);
}

function ei8_xmlrpc_floodgate_render_settings() {
    global $floodgateOptionSettings;
    $floodgateTargetVarPre = 'ei8_xmlrpc_floodgate_target_';

    $fgT = new ei8XmlrpcFloodgateTargets();
    //if there are no valid targets...attempt to import and setup the acct guid
    if(count($fgT->targets)<1) {
        $fgT->importCustomFolders();
        $fgT = new ei8XmlrpcFloodgateTargets();
    }
?>

<table class="form-table">
    <tr><td><h3>Floodgate Options</h3></td></tr>
<?php
    $op = new ei8XmlrpcFloodgateOptionFG();
    foreach($floodgateOptionSettings as $name=>$vals) {
        $op->load($name);
        $var = $op->name;
        $val = $op->value;
        //$var = ei8_xmlrpc_build_floodgate_option_name($name);
        $title = $vals[0];
        $extra = $vals[2];
        //$val = ei8_xmlrpc_get_floodgate_option($var);
        $val = htmlentities($val);

        if($name=='acct_guid' && !empty($val)) {
            //find the account name and show it
            $api = new ei8XmlrpcFloodgateAPI($val);
            $info = $api->getAccountInfo();
            $extra = sprintf(" Selected account: %s - %s,%s - %s",
                $info->account->login,
                $info->account->last_name,
                $info->account->first_name,
                $info->account->company
            );
        }
        $showExtra = (empty($extra)) ? '' : '<br><small>('.$extra.')</small>' ;

?>
    <tr valign="top">
        <th scope="row"><?php echo $title ?></th>
        <td><input type="text" name="<?php echo $var ?>" size=35 value="<?php echo $val ?>" /><?php echo $showExtra ?></td>
    </tr>
<?php
    }
?>
    <tr><td><h3>Floodgate Targets</h3></td></tr>
    <tr valign="top">
        <th scope="col"><h4>Media Type</h4></th>
        <th scope="col"><h4>Local Title</h4></th>
        <th scope="col"><h4>Remote Title</h4></th>
        <th scope="col"><h4>Target guid</h4></th>
    </tr>
<?php
    $floodgateMediaTypes = ei8_xmlrpc_floodgate_get_media_types();
    //echo "<p>Targets:<pre>"; print_r($fgT->targets); echo "</pre></p>";
    //echo "<p>Remote Targets:<pre>"; print_r($fgT->remoteTargets); echo "</pre></p>";
    //exit;
    foreach($floodgateMediaTypes as $type=>$title) {
        //echo "<tr><td><h4>{$title}</h4></td></tr>";
        foreach($fgT->targets as $target) {
            if($target->media_type!=$type) continue;
            $targetVarPre = $floodgateTargetVarPre.$target->id.'_';
            $remoteTitle = $fgT->remoteTargets[$target->target]->title;
            if(empty($remoteTitle)) {
                $remoteTitle = "<span style='color:red;'>MISSING TARGET</span><br><small>This target has been deleted from ei8t.com and will not be displayed for users</small>";
                $missingTitle = true;
            } else $missingTitle = false;
            $targetRowClass = (($missingTitle) || !($target->remoteTargetExists)) ? "class='missingtarget'" : "" ;
?>
    <tr valign="top" <?php echo $targetRowClass; ?>>
        <td><?php echo ucfirst($target->media_type); ?><input type='hidden' name='<?php echo $targetVarPre.'media_type' ?>' value='<?php echo $target->media_type ?>'></td>
        <td><input type="text" name="<?php echo $targetVarPre.'title' ?>" size=35 value="<?php echo $target->title ?>" /></td>
        <td><?php echo $remoteTitle ?></td>
        <td><?php echo $target->target ?><input type='hidden' name='<?php echo $targetVarPre.'target' ?>' value='<?php echo $target->target ?>'></td>
    </tr>
<?php

        }
    }
    $targetVarPre = $floodgateTargetVarPre.'new_';
?>
    <tr valign="top">
        <td><strong>Add A New Target</strong></td>
        <td><input type="text" name="<?php echo $targetVarPre.'title' ?>" size=35 value="" /></td>
        <td><select name='<?php echo $targetVarPre.'target' ?>'>
            <option selected> --- CHOOSE ONE --- </option>
<?php
    $types = array();
    $spacerOption = "<option disabled></option>";
    $nested = array();
    //build an array of just the guids for the current targets
    $myTargets = array();
    foreach($fgT->targets as $target) $myTargets[] = $target->target;
    //build the select array for the remote Targets
    foreach ($fgT->remoteTargets as $target ) {
        //determine if this is a new media type and show the title
        if(!in_array($target->media_type,$types)) {
            echo $spacerOption;
            echo "<option disabled>".strtoupper($target->media_type)."</option>";
            $types[] = $target->media_type;
        }
        //determine if this target is already being used
        $doDisabled = (in_array($target->target,$myTargets)) ? 'disabled' : '' ;
        //handle nesting of subfolders
        if(empty($target->parent_folder_id)) $nested = array();
        else {
            if(!in_array($target->parent_folder_id,$nested)) $nested[] = $target->parent_folder_id;
            else {
                $oldNested = $nested;
                $nested = array();
                foreach($oldNested as $id) {
                    $nested[] = $id;
                    if($id==$target->parent_folder_id) break;
                }
            }
        }
        $spacer = " &nbsp ";
        foreach($nested as $n) $spacer .= "--- ";
        echo "<option value='".$target->target."' $doDisabled>".$spacer.$target->title."</option>";
    }
?>
                        </select></td>
    </tr>
    </table>
<?php
}

function ei8_xmlrpc_floodgate_render_order() {
?>
    <noscript>
        <div class="error message"><p>This page can't work without javascript, because it uses drag and drop and AJAX.</p></div>
    </noscript>
    <table class="form-table">
        <tr valign="top">
<?php
    $fgTypes = ei8_xmlrpc_floodgate_get_media_types();
    $fgTypesCt = count($fgTypes);

    if($fgTypesCt>=1) foreach($fgTypes as $type=>$title) {
        $sortableName = 'sortable-'.$type;
        $ft = new ei8XmlrpcFloodgateTargets($type);
        $targets = $ft->targets;
        echo "<td><h3>$title Targets</h3>";
        echo sprintf('<div id="ajax-response-%s"></div>',$type);
        echo '<div id="order-floodgate-type">';
        echo sprintf('<ul id="%s">',$sortableName);
        if(count($ft->targets)>=1) foreach($ft->targets as $target) {
            echo sprintf('<li id="item_%s_%s"><span>%s</span></li>', $type, $target->id, $target->title);
        } else echo '<p>No '.$title.' targets exist...</p>';
        echo '</ul>';
        echo '<div class="clear"></div>';
        echo '</div>';
        echo '</td>';
?>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery("#<?php echo $sortableName; ?>").sortable({
                'tolerance':'intersect',
                'cursor':'pointer',
                'items':'li',
                'placeholder':'placeholder',
                'nested': 'ul'
            });

            jQuery("#<?php echo $sortableName; ?>").disableSelection();
            jQuery("#<?php echo $sortableName; ?>").on( "sortupdate", function() {
                jQuery.post(
                        ajaxurl,
                        {
                            action:'update-floodgate-type-order',
                            order:jQuery("#<?php echo $sortableName; ?>").sortable("serialize")
                        },
                        function(data, status, something)
                {
                    jQuery("#ajax-response-<?php echo $type; ?>").html('<div class="message updated fade"><p><?php echo $title; ?> Items Order Updated</p></div>');
                    jQuery("#ajax-response-<?php echo $type; ?> div").delay(3000).hide("slow");
                    //alert('got here! action: update-floodgate-type-order, sortableName:<?php echo $sortableName; ?>, ajaxurl:'+ajaxurl);
                    //alert("Data: " + data + "\nStatus: " + status + "\nSomething: " + something);
                });
            });
        });
    </script>
<?php
    }
?>
        </tr>
    </table>
<?php
}

function ei8_xmlrpc_floodgate_render_text() {
    $fgTypes = ei8_xmlrpc_floodgate_get_media_types(1);
    $fgVarPre = 'ei8_xmlrpc_floodgate_text_';

    $settings = array(
        'textarea_rows' => '12',
        'editor_css'    => '<style>.wp-editor-wrap {width:500px;}</style>',
        'media_buttons' => false,
        'wpautop' => false
    );

    echo "<table class='form-table'>";

    foreach($fgTypes as $type=>$title) {
        $var = $fgVarPre.$type;
        $val = stripslashes(ei8_xmlrpc_get_option($var));
?>
    <tr valign="top">
        <th scope="row"><?php echo $title ?>:</th>
        <td><?php wp_editor( $val, $var, $settings ); ?></td>
    </tr>
<?php
    }
    echo "</table>";
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
            <a href="<?php echo ei8_plugins_url('/css/ei8-xmlrpc-notifier.css'); ?>" target="_blank">Click here to access the included css.</a></strong></td>
        </tr>
        <tr><td colspan=2>Additionally, the media uploader ([ei8 MediaUploader]) is loaded in an iFrame directly from ei8t.com<br>
            This can be styled as using an external css file, that is pasted into the preferences page.<br>
            There is an included default css file that you can look at and use as a guide, <br>
            <strong>but please note that your new external file will replace this default file entirely</strong><br>
            <a href="<?php echo ei8_plugins_url('/css/ei8-file-uploader.css'); ?>" target="_blank">Click here to access the default media uploader css.</a></strong></td>
        </tr>
    </table>
</div>
<?php
}

function ei8_xmlrpc_admin_options() {
    global $optionP;
    //$postStatus      = ei8_xmlrpc_get_option('ei8_xmlrpc_post_status');
    //$postType        = ei8_xmlrpc_get_option('ei8_xmlrpc_post_type');
    //$mediaAlign      = ei8_xmlrpc_get_option('ei8_xmlrpc_media_align');
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
            $var = 'ei8_xmlrpc_hide_admin_options';
            ei8_xmlrpc_update_option($var, $_POST[$var]);

            $var = 'ei8_xmlrpc_media_align';
            ei8_xmlrpc_update_option($var, $_POST[$var]);

/*            $var = 'ei8_xmlrpc_site_type';
            ei8_xmlrpc_update_option($var, $_POST[$var]);

            $var = 'ei8_xmlrpc_recorder_vars';
            ei8_xmlrpc_update_option($var, ei8_xmlrpc_admin_parse_recorder_vars($_POST[$var]));

            $var = 'ei8_xmlrpc_submit_form';
            ei8_xmlrpc_update_option($var, $_POST[$var]);

            //$var = 'ei8_xmlrpc_use_captcha';
            //ei8_xmlrpc_update_option($var, $_POST[$var]);

            $var = 'ei8_xmlrpc_file_uploader_css';
            ei8_xmlrpc_update_option($var, $_POST[$var]);

            //update the custom folders values
            $customFolders = ei8_xmlrpc_getCustomFolders();
            foreach($customFolders as $folder=>$info) {
                ei8_xmlrpc_storeCustomFolder($folder, $_POST[$info['var']]);
            }
*/

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
        ei8XmlrpcFloodgatePage::redirect($ei8AdminUrl);

    }

    $hideAdmin       = ei8_xmlrpc_get_option('ei8_xmlrpc_hide_admin_options');
    $postStatus      = ei8_xmlrpc_get_option('ei8_xmlrpc_post_status');
    $postType        = ei8_xmlrpc_get_option('ei8_xmlrpc_post_type');
    $post_types      = ei8_get_post_types();
    $mediaAlign      = ei8_xmlrpc_get_option('ei8_xmlrpc_media_align');
    $playlistAlign   = ei8_xmlrpc_get_option('ei8_xmlrpc_playlist_align');
    $playlistLayout  = ei8_xmlrpc_get_option('ei8_xmlrpc_playlist_layout');
    $align_options   = array('left','center','right');
    $playlist_layout_options    = array('horizontal','vertical','list');
    $playlist_show_title        = ei8_xmlrpc_get_option('ei8_xmlrpc_playlist_show_title');
    $playlist_show_description  = ei8_xmlrpc_get_option('ei8_xmlrpc_playlist_show_description');

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
                //$useCaptcha         = ei8_xmlrpc_get_option('ei8_xmlrpc_use_captcha');
                /*$f_submitForm       = 'ei8_xmlrpc_submit_form';
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
                //$v_defaultHeightVideo   = ei8_coalesce(ei8_xmlrpc_get_option($f_defaultHeightVideo), 260);*/

?>
            <!-- <tr><td><h3>Admin Specific Settings</h3></td></tr>-->
            <tr valign="top">
                <th scope="row">Show eInnov8 Options:</th>
                <td><select name='ei8_xmlrpc_hide_admin_options'>
                    <option value="" <?php if(empty($hideAdmin)) echo "SELECTED"; ?>>visible to ALL(Authors, Editors, & Administrators)</option>
                    <option value="admin" <?php if(!empty($hideAdmin)) echo "SELECTED"; ?>>visible only to Administrators</option>
                </select></td>
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
            <tr valign="top">
                <th scope="row">Default playlist alignment:</th>
                <td><select name='ei8_xmlrpc_playlist_align'>
<?php
                        foreach ($align_options as $align ) {
                            $selected = ($align==$playlistAlign || (empty($playlistAlign) && $align=="left")) ? "SELECTED" : "" ;
                            echo "<option value=\"$align\" $selected>$align</option>";
                        }
?>
                    </select></td>
            </tr>
<!--            <tr valign="top">
                <th scope="row">Default playlist layout:</th>
                <td><select name='ei8_xmlrpc_playlist_layout'>
<?php
                        foreach ($playlist_layout_options as $layout ) {
                            $selected = ($layout==$playlistLayout || (empty($playlistLayout) && $layout=="horizontal")) ? "SELECTED" : "" ;
                            echo "<option value=\"$layout\" $selected>$layout</option>";
                        }
?>
                    </select></td>
            </tr>
                <tr valign="top">
                <th scope="row">Playlist show title:</th>
                <td><?php echo ei8_xmlrpc_form_boolean('ei8_xmlrpc_playlist_show_title',$playlist_show_title); ?></td>
            </tr>
            <tr valign="top">
                <th scope="row">Playlist show description:</th>
                <td><?php echo ei8_xmlrpc_form_boolean('ei8_xmlrpc_playlist_show_description',$playlist_show_description); ?></td>
            </tr>
-->
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

function ei8_xmlrpc_legacy_settings() {
    global $optionL;
    $formVarPre = ei8XmlrpcFloodgateFormField::VAR_PRE; //TODO This is half implemented...where the old form methods are now using the new form class
    $ei8AdminUrl     = "admin.php?page=".$optionL;

    if($_POST['action']=="update") {

        if (current_user_can('edit_others_posts')) {

            $var = 'ei8_xmlrpc_recorder_vars';
            ei8_xmlrpc_update_option($var, ei8_xmlrpc_admin_parse_recorder_vars($_POST[$formVarPre.$var]));

            $var = 'ei8_xmlrpc_submit_form';
            ei8_xmlrpc_update_option($var, $_POST[$formVarPre.$var]);

            //$var = 'ei8_xmlrpc_use_captcha';
            //ei8_xmlrpc_update_option($var, $_POST[$formVarPre.$var]);

            $var = 'ei8_xmlrpc_file_uploader_css';
            ei8_xmlrpc_update_option($var, $_POST[$formVarPre.$var]);

            $var = 'ei8_xmlrpc_media_align';
            ei8_xmlrpc_update_option($var, $_POST[$formVarPre.$var]);

            $var = 'ei8_xmlrpc_playlist_align';
            ei8_xmlrpc_update_option($var, $_POST[$formVarPre.$var]);

            $var = 'ei8_xmlrpc_playlist_layout';
            ei8_xmlrpc_update_option($var, $_POST[$formVarPre.$var]);

            $var = 'ei8_xmlrpc_playlist_show_title';
            ei8_xmlrpc_update_option($var, $_POST[$formVarPre.$var]);

            $var = 'ei8_xmlrpc_playlist_show_description';
            ei8_xmlrpc_update_option($var, $_POST[$formVarPre.$var]);

            //update the custom folders values
            $customFolders = ei8_xmlrpc_getCustomFolders();
            foreach($customFolders as $folder=>$info) {
                ei8_xmlrpc_storeCustomFolder($folder, $_POST[$formVarPre.$info['var']]);
            }


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
        ei8XmlrpcFloodgatePage::redirect($ei8AdminUrl);


    }

?>
<div class="wrap">
    <?php ei8_screen_icon(); ?>

    <h2>Embed (Legacy) Options:</h2>
    <p></p>
    <form method="post" action="<?php echo $ei8AdminUrl; ?>">
        <?php wp_nonce_field('update-options'); ?>
        <table class="form-table">
<?php
            if (current_user_can('edit_others_posts')) {
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

                $align_options   = array('left','center','right');
                $playlist_layout_options    = array('horizontal','vertical','list');
                $playlist_show_title        = ei8_xmlrpc_get_option('ei8_xmlrpc_playlist_show_title');
                $playlist_show_description  = ei8_xmlrpc_get_option('ei8_xmlrpc_playlist_show_description');

?>
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
                        $twitterAuthToken  = ei8_xmlrpc_get_option('ei8_xmlrpc_twitter_auth_token');
                        $twitterAuthSecret = ei8_xmlrpc_get_option('ei8_xmlrpc_twitter_auth_secret');
                        //echo "<p>twitterToken: $twitterToken<br>twitterSecret: $twitterSecret<br>twitterAuthToken: $twitterAuthToken<br>twitterAuthSecret: $twitterAuthSecret</p>";

                        require_once 'lib/ei8-twitter-wrapper.php';

                        $twitterObj = new ei8TwitterObj();
                        $twitterObj->setCallback(ei8_xmlrpc_get_plugin_url() . "twitter_callback.php" );

                        //reset request
                        if($_REQUEST['resetTwitter']) {
                            $twitterToken = $twitterSecret = "";
                            ei8_xmlrpc_update_option('ei8_xmlrpc_twitter_token', "");
                            ei8_xmlrpc_update_option('ei8_xmlrpc_twitter_secret', "");
                            echo ei8_xmlrpc_conf_message(true,$title='Success',$text="Twitter connection reset");

                        //got a response from Twitter, means the user has authenticated, now we authenticate the app
                        } elseif($_GET['oauth_verifier']) {
                            //$twitterObj->setOAuth($_GET['oauth_token']);
                            //$twitterObj->setAccessToken($_GET['oauth_token']);
                            //echo '<p>GOT HERE: pre authorize_app()</p>';
                            $twitterObj->setTokens($twitterAuthToken, $twitterAuthSecret);
                            list($twitterToken, $twitterSecret) = $twitterObj->authorize_app();
                            //echo '<p>GOT HERE: post authorize_app(), pre validate_user</p>';
                            //print("<p>TwitterObj: <pre>");
                            //print_r($twitterObj);
                            //print("</pre></p>");
                            //echo "<p>GOT HERE</p>"; exit;
                            $twitterInfo= $twitterObj->validate_user();
                            //echo '<p>GOT HERE: post validate_user()</p>';
                            //print("<p>TwitterInfo: <pre>");
                            //print_r($twitterInfo);
                            //print("</pre></p>");
                            //exit();
                            ei8_xmlrpc_update_option('ei8_xmlrpc_twitter_token', $twitterToken);
                            ei8_xmlrpc_update_option('ei8_xmlrpc_twitter_secret', $twitterSecret);
                            ei8_xmlrpc_update_option('ei8_xmlrpc_twitter_auth_token', "");
                            ei8_xmlrpc_update_option('ei8_xmlrpc_twitter_auth_secret', "");
                            echo ei8_xmlrpc_conf_message(true,$title='Success',$text="Twitter connection established");
                        }

                        //echo ei8_xmlrpc_conf_message(false,$title='DEBUG Twitter connection settings',$text="token:$twitterToken secret:$twitterSecret");

                        //there are no tokens stored in the db, so give the user the option of starting the auth dance
                        if(empty($twitterToken) || empty($twitterSecret)) {

                            //do the user auth here
                            $url = $twitterObj->getAuthorizationUrl();

                            //store the temp auth tokens
                            list($authToken, $authSecret) = $twitterObj->getAuthTokens();
                            ei8_xmlrpc_update_option('ei8_xmlrpc_twitter_auth_token', $authToken);
                            ei8_xmlrpc_update_option('ei8_xmlrpc_twitter_auth_secret', $authSecret);


                            //print("<p>TwitterObj: <pre>");
                            //print_r($twitterObj);
                            //print("</pre></p>");
                            //$url .= (strstr($url,'?')) ? "&" : "?" ;
                            //$url .= "oauth_callback=".urlencode($ei8AdminUrl);
                            echo "<a href='$url'>Authorize an account with Twitter</a>";

                        //make sure the user is valid
                        } else {

                            //do the user validate here


                            $twitterObj->setTokens($twitterToken, $twitterSecret);
                            $twitterInfo = $twitterObj->validate_user();

                            $resetUrl = $ei8AdminUrl."&resetTwitter=1#ei8xmlrpctwittersettings";
                            if(!$twitterInfo) {
                                echo "ERROR: Invalid Twitter Credentials<br> <a href='$resetUrl'>Reset Twitter Credentials</a>";
                            } else {
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
                                echo "<img src='$profilepic' align='left' style='padding-right:10px;'> Screen name: $username <br><small><a href='$resetUrl'>Reset Twitter Credentials</a></small>";
                            }
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
<!--            <tr valign="top">
                <th scope="row">Default playlist layout:</th>
                <td><select name='ei8_xmlrpc_playlist_layout'>
<?php
                        foreach ($playlist_layout_options as $layout ) {
                            $selected = ($layout==$playlistLayout || (empty($playlistLayout) && $layout=="horizontal")) ? "SELECTED" : "" ;
                            echo "<option value=\"$layout\" $selected>$layout</option>";
                        }
?>
                    </select></td>
            </tr>
-->
                <tr valign="top">
                <th scope="row">Playlist show title:</th>
                <td><?php echo ei8_xmlrpc_form_boolean('ei8_xmlrpc_playlist_show_title',$playlist_show_title); ?></td>
            </tr>
            <tr valign="top">
                <th scope="row">Playlist show description:</th>
                <td><?php echo ei8_xmlrpc_form_boolean('ei8_xmlrpc_playlist_show_description',$playlist_show_description); ?></td>
            </tr>
            <tr>
                <td><h3>Web Recorder Settings</h3></td>
                <td style="vertical-align: middle;"><small>ex. http://www.ei8t.com/swfmini/<span style="color: red;">v=8mGCvmv3X&amp;a=d3hQHKcR8DR</span></small></td>
            </tr>
<?php
    $customFolders = ei8_xmlrpc_getCustomFolders();
    foreach($customFolders as $folder => $info) {
?>
            <tr valign="top">
                <th scope="row"><?php echo $info['title']; ?>:</th>
                <td><?php echo ei8_xmlrpc_form_text($info['var'],$info['value']).' &nbsp; <small>Usage ex: [ei8 MiniRecorder cf='.$folder.']</small>'; ?></td>
            </tr>
<?php
    }
?>
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
    $formVarPre = ei8XmlrpcFloodgateFormField::VAR_PRE;
    $ei8AdminUrl     = "admin.php?page=".$optionE;

    if($_POST['action']=="update") {

        if($_POST['ei8_xmlrpc_reset_to_defaults']==1) {
            $defaults = ei8_xmlrpc_get_message_defaults();
            foreach($defaults as $var=>$val) {
                ei8_xmlrpc_update_option($var, $val);
            }
        } else {
            $vars = ei8_xmlrpc_get_message_variables();
            foreach($vars as $var) {
                ei8_xmlrpc_update_option($var, $_POST[$formVarPre.$var]);
            }
        }

        ei8_xmlrpc_admin_log("<p>Your email notification preferences have been updated.</p>",1);

        //force page reload
        ei8XmlrpcFloodgatePage::redirect($ei8AdminUrl);
    }

?>
<div class="wrap">
    <?php ei8_screen_icon(); ?>

    <h2>EmailPreferences:</h2>
    <form method="post" action="<?php echo $ei8AdminUrl; ?>">
        <?php wp_nonce_field('update-options'); ?>
        <table class="form-table">
            <tr><td><h3>Notification Email Settings</h3></td></tr>
            <?php
            $message_variables = ei8_xmlrpc_get_message_variables(1);
            $message_settings  = ei8_xmlrpc_get_message_settings();
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
    //moved to oop
    $f = new ei8XmlrpcFloodgateFormFieldText($var,$val);
    return $f->render_field();
}

function ei8_xmlrpc_form_textarea($var,$val,$rows='') {
    //moved to oop
    $args = (empty($rows)) ? array() : array('rows'=>$rows) ;
    $f = new ei8XmlrpcFloodgateFormFieldTextarea($var,$val,$args);
    return $f->render_field();
}

function ei8_xmlrpc_form_boolean($var,$val) {
    //moved to oop
    $f = new ei8XmlrpcFloodgateFormFieldSelectBoolean($var,$val);
    return $f->render_field();
}

function ei8_xmlrpc_get_blog_option($val) {
    global $wp_version;
    return ($wp_version >= 3) ? get_site_option($val) : get_blog_option($val) ;
}

function ei8_xmlrpc_get_option($id) {
    $db = new ei8XmlrpcFloodgateDbTableOptions();
    return $db->get_option($id);
}

function ei8_xmlrpc_update_option($id, $value, $addslashes=TRUE) {
    $db = new ei8XmlrpcFloodgateDbTableOptions();
    if($addslashes) $value = addslashes($value);
    $db->update_option($id,$value);
}

function ei8_xmlrpc_admin_parse_recorder_vars($vars) {
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

    //create user if necessary
    if(!$userID) wp_insert_user( $userInfo );

    //check user permissions if user exists
    if($userID) $userData = get_userdata($userID);

    //only do the update if necessary
    if( $userData->user_pass!=wp_hash_password($passWord) || !user_can($userID,'publish_posts') ) {
        wp_update_user( $userInfo );
    };

    return array($userName, $passWord);
}

function ei8_xmlrpc_get_site_type_name() {
    return "Floodgate";
}

function ei8_xmlrpc_get_message_defaults() {

    $defaults = array(
        'email_from_name'               => "Website Name",
        'email_from_addr'               => "submit@sitename.com",
        'email_subject'                 => "New Floodtech Submission",
        'message_intro'                 => "A new Floodtech submission has arrived at your website with this title:
    [[post_title]]",

        'message_post_status_intro'     => "This submission",
        'message_post_status_draft'     => " is waiting for review within the your website administration area.",
        'message_post_status_publish'   => " has been published as a post on your website.",
        'message_post_status_unknown'   => " is available for review within your website administration area.",

        'message_thank_you'           => "Thank you for being a customer of eInnov8 Marketing.",
        'message_quick_links_show'    => 1,
        'message_quick_links_intro'   => "Click on this link (and log in, if necessary) to review, edit and publish this submission:",
        'message_referral_show'       => 1,
        'message_referral_text'       => "Learn more about us at http://einnov8.com."
    );

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

function ei8_xmlrpc_admin_init() {
    wp_enqueue_script('jQuery');
    wp_enqueue_script('jquery-ui-sortable');

    wp_register_style('ei8AdminStyleSheets', ei8_plugins_url('/css/ei8-xmlrpc-notifier-admin.css'));
    wp_enqueue_style( 'ei8AdminStyleSheets');

    ei8_xmlrpc_admin_install();
}

//handle db table installs and updates
function ei8_xmlrpc_admin_install() {
    global $wpdb, $wp_version;
    global $customFolderDefault, $customFolderPre;

    $table1 = $wpdb->prefix . "ei8_xmlrpc_options";

    $table1_sql = "CREATE TABLE `{$table1}` (
        `ID` BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
        `option_name` VARCHAR( 100 ) NOT NULL ,
        `option_value` TEXT NOT NULL,
        PRIMARY KEY ( `ID` ),
        UNIQUE ( `option_name` )
        );";

    $table2 = $wpdb->prefix . "ei8_floodgate_targets";

    $table2_sql = "CREATE TABLE `{$table2}` (
        `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
        `title` VARCHAR( 100 ) NOT NULL ,
        `target` TEXT NOT NULL,
        `media_type` TEXT NOT NULL,
        `orderer` INT( 3 ) NOT NULL DEFAULT 5,
        PRIMARY KEY ( `id` )
        );";

    if($wp_version < 3) require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $ei8_xmlrpc_db_sql   = $table1_sql.$table2_sql ;

    $wpdb->flush();
    $errs = 0;

    //first check for old testiboonials settings and update if necessary
    $updatedSQL = true;
    $tableT = $wpdb->prefix . "testiboonials_xmlrpc_options";
    if($wpdb->get_var("SHOW TABLES LIKE '$tableT'")==$tableT && $wpdb->get_var("SHOW TABLES LIKE '$table1'") != $table1) {
        ei8_xmlrpc_admin_log("<p>Converting database from older version.</p>",1);

        if ($wpdb->get_var("SHOW TABLES LIKE '$table1'") != $table1) {
            $sql = "CREATE TABLE $table1 LIKE $tableT";
            $errs = ei8_xmlrpc_admin_query($sql);

            if($errs<1) {
                $sql = "SELECT * FROM $tableT";
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
                ei8_xmlrpc_admin_query("DROP table $tableT");

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
        $errs = ei8_xmlrpc_admin_query($table1_sql,$errs);
        $errs = ei8_xmlrpc_admin_query($table2_sql,$errs);

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
        ei8_xmlrpc_admin_log("<p>CURRENT ei8_xmlrpc_db_sql :: <pre>".ei8_xmlrpc_get_option( "ei8_xmlrpc_db_sql" )."</pre></p>");
        ei8_xmlrpc_admin_log("<p>NEW ei8_xmlrpc_db_sql :: <pre>{$ei8_xmlrpc_db_sql}</pre></p>");

        $table1Bak = $table1.'_bak';
        $table2Bak = $table2.'_bak';

        $upgradeTable1 = ($wpdb->get_var("SHOW TABLES LIKE '$table1'") == $table1);
        $upgradeTable2 = ($wpdb->get_var("SHOW TABLES LIKE '$table2'") == $table2);
        $upgradeTable1Failed = ($wpdb->get_var("SHOW TABLES LIKE '$table1Bak'") == $table1Bak);
        $upgradeTable2Failed = ($wpdb->get_var("SHOW TABLES LIKE '$table2Bak'") == $table2Bak);

        //create table backups
        ei8_xmlrpc_admin_log("<p>Backing up current tables</p>");
        if($upgradeTable1) {
            ei8_xmlrpc_admin_log("<p>FOUND '$table1'...set to upgrade</p>");
            if ($upgradeTable1Failed) {
                ei8_xmlrpc_admin_log("<p>OOPS! Looks like an upgrade of the database wasn't completed! ...attempting to clean up $table1 now</p>");
                $errs = ei8_xmlrpc_admin_query( "DROP TABLE IF EXISTS $table1;", $errs );
            } else $errs = ei8_xmlrpc_admin_query( "RENAME TABLE $table1 TO $table1Bak;", $errs );
        }
        if($upgradeTable2) {
            ei8_xmlrpc_admin_log("<p>FOUND '$table2'...set to upgrade</p>");
            if ($upgradeTable2Failed) {
                ei8_xmlrpc_admin_log("<p>OOPS! Looks like an upgrade of the database wasn't completed! ...attempting to clean up $table2 now</p>");
                $errs = ei8_xmlrpc_admin_query( "DROP TABLE IF EXISTS $table2;", $errs );
            } else $errs = ei8_xmlrpc_admin_query( "RENAME TABLE $table2 TO $table2Bak;", $errs );
        }

        //create new tables
        ei8_xmlrpc_admin_log("<p>Creating new tables</p>");
        $errs = ei8_xmlrpc_admin_query($table1_sql, $errs);
        $errs = ei8_xmlrpc_admin_query($table2_sql, $errs);

        //copy data from backups
        ei8_xmlrpc_admin_log("<p>Copying old data into new tables</p>");
        if($upgradeTable1) $errs = ei8_xmlrpc_admin_query( "INSERT INTO $table1 SELECT * FROM $table1Bak;", $errs );
        if($upgradeTable2) $errs = ei8_xmlrpc_admin_query( "INSERT INTO $table2 SELECT * FROM $table2Bak;", $errs );

        //drop backup tables
        ei8_xmlrpc_admin_log("<p>Dropping backup tables</p>");
        if($upgradeTable1) $errs = ei8_xmlrpc_admin_query( "DROP TABLE $table1Bak;", $errs );
        if($upgradeTable2) $errs = ei8_xmlrpc_admin_query( "DROP TABLE $table2Bak;", $errs );

        //see if floodgate needs to be populated
        $ft = new ei8XmlrpcFloodgateTargets();
        if(count($ft->getTargets())<1) {
            ei8_xmlrpc_admin_log("<p>Importing custom folder settings into upgraded floodgate targets</p>",1);
            $ft->importCustomFolders();
        }

        //update options db_version
        if($errs<1) {
            ei8_xmlrpc_admin_log("<p>Storing new table structure</p>");
            ei8_xmlrpc_update_option('ei8_xmlrpc_db_sql',$ei8_xmlrpc_db_sql);
            ei8_xmlrpc_admin_log("<p>Database tables updated to current version</p>",1);
        } else {
            ei8_xmlrpc_admin_log("<p class='abq-error'>Errors updating database</p>",1);
            ei8_xmlrpc_admin_log("<p><b>SQL ERROR:</b><pre style='color:red'>".$wpdb->last_error."</pre></p>");
        }

    } else {
        //ei8_xmlrpc_admin_log("<p>Database is up to date. No updates performed.</p>",1);
        $updatedSQL = false;
    }

    //check for deprecated named options and update as necessary
    if(!(ei8_xmlrpc_get_option('ei8_xmlrpc_recorder_vars')) && (ei8_xmlrpc_get_option('ei8_xmlrpc_pubClip_minirecorder'))) {
        ei8_xmlrpc_update_option('ei8_xmlrpc_recorder_vars', ei8_xmlrpc_get_option('ei8_xmlrpc_pubClip_minirecorder'));
        ei8_xmlrpc_update_option('ei8_xmlrpc_pubClip_minirecorder', '');
        ei8_xmlrpc_admin_log("<p>Updated recorder settings to previous version</p>",1);
    }
    $cf = $customFolderPre.$customFolderDefault;
    if(!(ei8_xmlrpc_get_option($cf)) && (ei8_xmlrpc_get_option('ei8_xmlrpc_recorder_vars'))) {
        ei8_xmlrpc_update_option($cf, ei8_xmlrpc_get_option('ei8_xmlrpc_recorder_vars'));
        //ei8_xmlrpc_update_option('ei8_xmlrpc_recorder_vars', '');
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

    //add the endpoint for floodgate url rewriting
    ei8_xmlrpc_floodgate_update_name();
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
function ei8_xmlrpc_admin_query($sql, $errCt=0) {
    global $wpdb, $ei8_xmlrpc_debug;

    //conditionally turn on error reporting
    //NOTE: there has got to be a better way to catch and display sql errors...but I ran out of time...
    //if (isset($ei8_xmlrpc_debug)) $wpdb->show_errors();
    ei8_xmlrpc_admin_log("<p><b>Running Query:</b> <pre>".$sql."</pre></p>");
    //if ($wpdb->query($sql) === FALSE && strlen(trim($wpdb->last_error))>=1) {
    if ($wpdb->query($sql) === FALSE) {
        ei8_xmlrpc_admin_log("<p style='color:red'><b>SQL ERROR: </b>\"".$wpdb->last_error."\"</p>");
        $errCt++;
    }
    return $errCt ;
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

add_action('admin_init', 'ei8_xmlrpc_admin_init');
add_action('admin_notices', 'ei8_xmlrpc_admin_notices');
?>