<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yipeecaiey
 * Date: 10/14/13
 * Time: 8:24 AM
 * To change this template use File | Settings | File Templates.
 */
class ei8XmlrpcFloodgatePage
{
    public $css;
    public $logo;
    public $clientName;
    public $resellerName;
    public $floodgateUrl;
    public $floodgateMediaTypes;
    public $floodgateTargets;
    public $pluginUrl;

    public $session;
    public $session_is_valid;

    public $showNav;
    public $showContent;
    public $breadCrumb;
    public $accountInfo;
    public $currentType;
    public $currentTarget;
    public $queryString;

    public function __construct() {
        $floodgate = ei8_xmlrpc_floodgate_get_request_var();
        //global $floodgate;

        $this->pluginUrl    = ei8_plugins_url('',' ');
        $this->css          = ei8_plugins_url('/css/floodgate.css');
        $this->logo         = ei8_xmlrpc_get_floodgate_option('logo');
        $this->clientName   = ei8_xmlrpc_get_floodgate_option('client_name');
        $this->resellerName = ei8_xmlrpc_get_floodgate_option('reseller_name');

        $this->floodgateUrl = get_bloginfo('wpurl').'/'.ei8_xmlrpc_floodgate_get_name().'/';
        $this->floodgateMediaTypes = ei8_xmlrpc_floodgate_get_media_types();

        $ft = new ei8XmlrpcFloodgateTargets();
        $this->floodgateTargets = $ft->targets;
        $this->accountInfo = $ft->accountInfo;

        //get the current type and target from request var
        list($this->currentType,$this->currentTarget,$this->queryString) = explode('/',$floodgate,3);
        if(empty($this->currentTarget)) $this->currentTarget = $this->get_default_target();

        $this->session = new ei8XmlrpcFloodgateSession();
        $this->session_is_valid = $this->session->validate();

        //echo "<p>THIS:<pre>"; print_r($this); echo "</pre></p>";
        $this->purge_wp_scripts();
    }

    private function get_default_target($type='') {
        if($type=='') $type = $this->currentType;
        if($type=='') return '';
        $ft = new ei8XmlrpcFloodgateTargets($type);
        //echo '<p>type: '.$type.'<br>targets:<pre>'; print_r($ft->targets); echo '</pre></p>'; exit;
        if (count($ft->targets)>=1) {
            $myTarget = array_shift($ft->targets);
            return $myTarget->id;
        } else return '';
    }

    private function build_breadcrumb() {
        //$this->breadCrumb = "YOU ARE HERE";
        $breadCrumbs = array();
        //$breadCrumbs[] = $this->resellerName);
        if($this->currentType && $this->floodgateMediaTypes[$this->currentType]) {
            $breadCrumbs[] = $this->floodgateMediaTypes[$this->currentType];
            $breadCrumbs[] = ($this->currentTarget) ? $this->floodgateTargets[$this->currentTarget]->title : "ERROR" ;
        }
        $this->breadCrumb = implode(' : ', $breadCrumbs);
    }

    private function build_content() {
        $col    = array();
        $col[1] = $col[2] = $col[3] = array();
        $col[1]['welcome'] = $this->build_content_welcome();

        switch($this->currentType) {
            default:
            case 'home':
                break;
            case 'video':
                $col[2]['recorder'] = $this->build_content_web_recorder();
                $col[3]['uploader'] = $this->build_content_media_uploader();
                break;
            case 'audio':
                $col[2]['recorder'] = $this->build_content_web_recorder();
                $col[3]['uploader'] = $this->build_content_media_uploader();
                $col[3]['phone']    = $this->build_content_phone();
                break;
            case 'text':
                $col[2]['uploader'] = $this->build_content_submit_text();
                break;
            case 'image':
                $col[2]['uploader'] = $this->build_content_submit_image();
                break;
            case 'support':
                $col[2]['support'] = $this->build_content_support();
                break;
            case 'login':
                $col[1] = array();
                $col[2]['login'] = $this->build_content_login();
                break;
        }

        $this->showContent = "";
        //foreach($col as $colNum=>$contents) foreach($contents as $content) {
        for($colNum=1;$colNum<=3;$colNum++) {
            //handle grid for columns
            /*if($colNum==2 && empty($col[3])) $cols = "eleven columns" ;
            else */$cols = ($colNum==2) ? "six columns" : "five columns" ;

            $this->showContent .= '<div class="'.$cols.'">';
            foreach($col[$colNum] as $key=>$content) {
                list($title,$html) = $content;
                $extra = 'content-box-'.$key;
                $showTitle = ($title=='') ? '' : "<h2>$title</h2>" ;
                $this->showContent .=<<<EOT
        		<div class="content-box $extra">
                    <div class="content-box-inner">
                        $showTitle
                        <p>$html</p>
                    </div>
        		</div>
EOT;
            }
            $this->showContent .= '</div>';
        }
    }

    private function build_content_box_title($title,$type='') {
        $html = ($type=='') ? '' : "<a class='helpinfo' href='#help_{$type}'><img src='{$this->pluginUrl}/images/help_mini.png'></a>";
        $html .= $title;
        return $html;
    }

    private function build_content_media_uploader() {
        $target = new ei8XmlrpcFloodgateTarget($this->currentTarget);
        switch ($this->currentType) {
            case 'audio':
                $title = "Upload An Audio File";
                $help = 'upload_audio';
                break;
            case 'video':
                $title = "Upload A Video File";
                $help = 'upload_video';
                break;
        }
        $title = $this->build_content_box_title($title,$help);
        //$title  = "Media Upload";
        //$html   = "<p>Media Uploader Goes Here";
        //$html   = ei8_xmlrpc_recorder_wrap('media', $target->target);
        $form = new ei8XmlrpcFloodgateFormUploader($this->currentType,$target->target,$this->pluginUrl);
        $html   = $form->render();
        return array($title, $html);
    }

    private function build_content_phone() {
        $target = new ei8XmlrpcFloodgateTarget($this->currentTarget);
        $title  = $this->build_content_box_title("Submit Audio By Telephone","phone");
        $phone_number = $this->accountInfo->phone_number;
        $pin_code = ($target->pin_code) ? $target->pin_code : $this->accountInfo->pin_code ;
        $html   = "<div class='phoneinfo'>Phone: $phone_number<br>Ext: $pin_code</div>";
        return array($title, $html);
    }

    private function build_content_login() {
        $form = new ei8XmlrpcFloodgateFormLogin($this->build_floodgate_current_url());
        $form->src = $this->pluginUrl;
        if($form->status=='success') $this->redirect($this->floodgateUrl);
        //$form->body = $form->build_table($form_fields);
        $title  = ""; //"Please login";
        return array($title, $form->render());
    }

    /*private function build_content_login() {
        $form = new ei8XmlrpcFloodgateFormFG($this->build_floodgate_current_url());
        $form->submitButton = 'Login';
        $form_fields = array(
            'pass'      => array('password','Please Enter Your Password',40),
            'action'    => array('hidden','login'),
        );
        $title  = "";
        if($_POST[$form->prep_var_name('action')]=='login') {
            $this->session->try_login($_POST[$form->prep_var_name('pass')]);
            if($this->session->is_valid()) {
                //redirect
                //echo '<p>YOU ARE LOGGED IN!<pre>'; print_r($this->session); echo '</pre></p>';
                $this->redirect($this->floodgateUrl);
            } else {
                //show fancy error message?
                $title = "<span class='errormessage'>Please try again</span>";
            }
        }
        $form->body = $form->build_table($form_fields);
        //$title  = "Please login";
        return array($title, $form->render());
    }*/

    private function build_content_support() {
        $title  = "Get Support!";
        $html   = '<script type="text/javascript" src="https://m112.infusionsoft.com/app/form/iframe/24bad3e1859bec1ff536b642d25b7659"></script>';
        return array($title, $html);
    }

    private function build_content_submit_text() {
        $title  = $this->build_content_box_title("Submit a Written Post",'submit_text');
        $target = new ei8XmlrpcFloodgateTarget($this->currentTarget);
        $form = new ei8XmlrpcFloodgateFormContentSubmit($this->currentType,$target->target,$this->build_floodgate_current_url());
        $html   = $form->render();
        return array($title, $html);
    }

    private function build_content_submit_image() {
        $title  = $this->build_content_box_title("Submit Image",'submit_image');
        $target = new ei8XmlrpcFloodgateTarget($this->currentTarget);
        $form = new ei8XmlrpcFloodgateFormContentSubmit($this->currentType,$target->target,$this->build_floodgate_current_url());
        $html   = $form->render();
        return array($title, $html);
    }

    private function build_content_tweet() {
        $title  = "Submit a tweet";
        $html   = ei8_xmlrpc_filter_tags("[cxl Twitter Form]");
        return array($title, $html);
    }

    private function build_content_web_recorder() {
        $target = new ei8XmlrpcFloodgateTarget($this->currentTarget);
        if($this->currentType=='audio') {
            $title  = "Submit Audio";
            $help   = 'webrec_audio';
            $tt = 'fta';
            $tv = 'default';
            $ta = $target->target;
        } else {
            $title  = "Submit Video";
            $help   = 'webrec_video';
            $tt = 'ft';
            $tv = $target->target;
            $ta = 'default';
        }
        $title = $this->build_content_box_title($title,$help);
        $html   = ei8_xmlrpc_recorder_wrap($tt, "a=$ta&v=$tv");
        return array($title, $html);
    }

    private function build_content_welcome() {
        $title  = "";//"How To Use FLOODtech";
        $html   = ei8_xmlrpc_get_option('ei8_xmlrpc_floodgate_text_'.$this->currentType);
        return array($title, $html);
    }

    private function build_floodgate_current_url() {
        return $this->build_floodgate_url($this->currentType,$this->currentTarget);
    }

    private function build_floodgate_url($type='',$target='') {
        $url = $this->floodgateUrl;
        if(!empty($type)) {
            $url .= $type.'/';
            if(!empty($target))
                $url .= $target.'/';
        }
        return $url;
    }

    private function build_helpinfo() {
        //this could be loaded from the db...but for now is hardcoded
        $html =<<<EOT
        <div id='help_submit_text' class='helpcontent'>
            <h3>How To: Submit Written Post</h3>
            <ol>
                <li>Title your post, then paste the content into the submission box.</li>
                <li>If you would like to attach a document to this post, use the browse button to select a file on your computer</li>
                <li>Submit</li>
            </ol>
            <strong>Note: Attachment is not required for successful post.</strong>
        </div>
        <div id='help_submit_image' class='helpcontent'>
            <h3>How To: Submit Image</h3>
            <ol>
                <li>Use the browse button to select an image on your computer</li>
                <li>Title your post, then paste the content into the submission box.</li>
                <li>Submit</li>
            </ol>
        </div>
        <div id='help_webrec_audio' class='helpcontent'>
            <h3>How To: Submit Audio</h3>
            <ol>
                <li>Click allow to enable the online recorder to access your microphone</li>
                <li>Select <strong>Begin Recording</strong> to access the submission interface and record your audio</li>
                <li>Press play to listen to your recording before submitting it. If you wish to re-record, simply select cancel and start again</li>
                <li>Enter a Title and Description -> then <strong>press Save to submit.</strong></li>
            </ol>
        </div>
        <div id='help_webrec_video' class='helpcontent'>
            <h3>How To: Submit Video</h3>
            <ol>
                <li>Click allow to enable the online recorder to access your microphone and webcam</li>
                <li>Select <strong>Begin Recording</strong> to access the submission interface and record your video</li>
                <li>Press play to watch to your recording before submitting it. If you wish to re-record, simply select cancel and start again</li>
                <li>Enter a Title and Description -> then <strong>press Save to submit.</strong></li>
            </ol>
        </div>
        <div id='help_upload_audio' class='helpcontent'>
            <h3>How To: Upload An Audio File</h3>
            <ol>
                <li>Enter a Title and Description first</li>
                <li>Browse for the audio file on your computer</li>
                <li>Once selected, the audio will begin to upload automatically</li>
                <li>You will be notified when the upload is complete</li>
            </ol>
        </div>
        <div id='help_upload_video' class='helpcontent'>
            <h3>How To: Upload A Video File</h3>
            <ol>
                <li>Enter a Title and Description first</li>
                <li>Browse for the video file on your computer</li>
                <li>Once selected, the video will begin to upload automatically</li>
                <li>You will be notified when the upload is complete</li>
            </ol>
        </div>
        <div id='help_phone' class='helpcontent'>
            <h3>How To: Submit Audio By Telephone</h3>
            <ol>
                <li>Call the number provided and enter your pin number</li>
                <li>Follow the prompts to record your audio</li>
                <li>To save your recording, simply hang up</li>
            </ol>
        </div>

EOT;
        return $html;
    }

    private function build_nav() {
        $this->showNav = $this->build_nav_type('Home');
        foreach($this->floodgateMediaTypes as $type=>$typeName) $this->showNav .= $this->build_nav_type($typeName,$type);
        $this->showNav .= $this->build_nav_type('Support','support');
    }

    private function build_nav_type($title,$type='') {
        //first set up the url
        $url = $this->build_floodgate_url($type);

        //figure out if there are any subs to handle for this type
        $subsCT = 0;
        $requireSubsMissing = false;
        //echo "<p>Processing type: $type</p>";
        if($type!='' && $type!='support') {
            $ft = new ei8XmlrpcFloodgateTargets($type,true);
            $subsCT = count($ft->targets);
            if($subsCT<1) $requireSubsMissing = true;
            //echo "<p>Checking for subs...found($subsCT)<pre>"; print_r($ft); echo "</pre></p>";
        }


        //could change the class here if no subs
        if ($requireSubsMissing) {
            $html = sprintf('<li title="There are no targets set up for this type"><a>%s</a>', $title);
        } else {
            //$showActive = ($showDeactivate=='' && $this->currentType==$type) ? 'active' : '' ;
            $showActive = ($this->currentType==$type) ? 'active' : '' ;
            $showHasSubs = ($subsCT>=1) ? "has-submenu" : "" ;
            $html = sprintf('<li class="%s %s"><a href="%s">%s</a>',$showActive, $showHasSubs, $url, $title);
        }
        if($subsCT>=1) {
            $html .= '<ul class="sub-menu">';
            foreach($ft->targets as $target) {
                $showActive = ($this->currentTarget==$target->id) ? 'active' : '' ;
                $html .= sprintf('<li class="%s"><a href="%s"><span></span>%s</a></li>', $showActive, $url.$target->id.'/', $target->title);
            }
            $html .= '</ul>';
        }
        $html .= '</li>';
        return $html;
    }

    private function build_page() {
        //$adminBuffer = (is_multisite() && is_admin_bar_showing()) ? 'adminbuffer' : '' ;
        show_admin_bar(false);
        $adminBuffer = '' ;
        //list($width, $height, $type, $attr) = getimagesize("img/flag.jpg");
        list($logoWidth,$logoHeight) = getimagesize($this->logo);

        $headerPadding = 4;
        $headerHeight = $logoHeight+($headerPadding*2);
        $breadcrumbH = 20; //arbitrary number from standard css
        $breadcrumbY = ($logoHeight<=$breadcrumbH) ? 0 : round(($logoHeight-$breadcrumbH)/2)+1 ;
        $helpInfo = $this->build_helpinfo();

        $pageTitle = $this->resellerName." ".$this->clientName;

        $showSuccess    = ei8_xmlrpc_conf_message(true,'%title%','%msg%',false);
        $showError      = ei8_xmlrpc_conf_message(false,'%title%','%msg%',false);
        $successDefaults= ei8_xmlrpc_conf_message_defaults(true);
        $errorDefaults  = ei8_xmlrpc_conf_message_defaults(false);

        $html =<<<EOT
<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>

	<!-- Basic Page Needs
  ================================================== -->
	<meta charset="utf-8">
	<title>$pageTitle</title>
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Mobile Specific Metas
  ================================================== -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- CSS
  ================================================== -->
	<link rel="stylesheet" href="{$this->pluginUrl}/css/skeleton/base.css">
	<link rel="stylesheet" href="{$this->pluginUrl}/css/skeleton/skeleton.css">

	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!-- Favicons
	================================================== -->
	<link rel="shortcut icon" href="{$this->pluginUrl}/images/favicon.ico">
	<link rel="apple-touch-icon" href="{$this->pluginUrl}/images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="{$this->pluginUrl}/images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="{$this->pluginUrl}/images/apple-touch-icon-114x114.png">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{$this->pluginUrl}/colorbox/colorbox.css" type="text/css" media="screen,projection" />
    <style media="screen,projection">
        .logo {
            background: url('$this->logo') no-repeat;
            margin: 4px 0px 0px 6px;
            height: 63px;
        }
        #breadcrumb h1 {
            margin-top: 21px;
        }
    </style>
    <link rel="stylesheet" href="{$this->pluginUrl}/css/skeleton/layout.css">

    <!-- Javascript -->
    <script src="{$this->pluginUrl}/lib/js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="{$this->pluginUrl}/lib/js/swfobject.js"></script>

</head>

<body>

    <header class="$adminBuffer">
        <div class="container">
            <div id="titles" class="five columns">
                <h2>$this->resellerName</h2>
                <h1>$this->clientName</h1>
            </div>
            <nav class="eleven columns primary">
                <ul>
                    $this->showNav
                </ul>
            </nav>
            <nav class="sixteen columns mobile">
                <ul>
                    $this->showNav
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="five columns logo"></div>
        <div id="breadcrumb" class="eleven columns">
            <h1>$this->breadCrumb</h1>
        </div>
    </div>

    <div id="content" class="container">
        $this->showContent
    </div>

    <footer>
    </footer>

    <section id='colorboxes'>$helpInfo</section>

    <!-- Javascript -->
    <script src="{$this->pluginUrl}/lib/js/jquery.uploadfile.min.js"></script>
    <script type="text/javascript" src="{$this->pluginUrl}/colorbox/jquery.colorbox.js"></script>
    <script type="text/javascript" src="{$this->pluginUrl}/lib/js/responsive-menu.js"></script>
    <script type="text/javascript">
        $(document).ready(function($){
            //setup helpinfo
            $(".helpinfo").colorbox({inline:true, width:"40%"});

            //nav fix
			var mainnavheight = $('#mainnav').outerHeight(true);
			$('#mainnav .sub-menu').css('top', mainnavheight);
        });

        //responsive nav
        $(document).ready(function() {
            //build dropdown
            $("<select />").appendTo("nav.primary");

            //Create default option "Go to..."
            $("<option />", {
                "selected"  : "selected",
                "value"     : "",
                "text"      : "Go to..."
            }).appendTo("nav.primary select");

            //Populate dropdowns with the first menu items
            $("nav.primary li a").each(function() {
                var el = $(this);
                $("<option />", {
                    "value" : el.attr("href"),
                    "text"  : el.text()
                }).appendTo("nav.primary select");
            });

            //make responsive dropdown menu actually work
            $("nav.primary select").change(function() {
                window.location = $(this).find("option:selected").val();
            });


            //alert($("nav.primary select option").length);
            if( $("nav.primary select option").length <= 1 ) {
                $("nav.primary select").css("display","none");
                $("header .container").css("padding","7px 0");
            }
        });

        //set some things up for the floodgate.js
        var showSuccess         = '$showSuccess';
        var showError           = '$showError';
        var successDefaultTitle = '$successDefaults[0]';
        var successDefaultMsg   = '$successDefaults[1]';
        var errorDefaultTitle   = '$errorDefaults[0]';
        var errorDefaultMsg     = '$errorDefaults[1]';
    </script>
    <script type="text/javascript" src="{$this->pluginUrl}/lib/js/floodgate.js"></script>
</body>
</html>
EOT;
        return $html;
    }

    public function display() {
        if($this->session_is_valid) {
            $this->build_breadcrumb();
            $this->build_nav();
        } else {
            $this->currentType = 'login';
        }
        $this->build_content();
        echo $this->build_page();
    }

    public static function get_rlimitmem_url() {
        $self = new ei8XmlrpcFloodgatePage();
        $url = $self->pluginUrl."/rlimitmem.php";
        return $url;
    }

    public static function get_rlimitmem_status() {
        $rlimitmemString = 'RLimitMem max';
        $file = ABSPATH."wp-admin/.htaccess";
        if(file_exists($file)) {
            $contents = file_get_contents($file);
            if(strstr($contents,$rlimitmemString)) return true;
        }
        return false;
    }

    public static function get_rlimitmem_show_status() {
        $status = self::get_rlimitmem_status();
        $showStatus = ($status) ? 'Enabled' : 'Disabled' ;
        return $showStatus;
    }

    public function handle() {
        if(strstr($this->currentType,'logout') || strstr($this->currentTarget,'logout') || strstr($this->queryString,'logout')) {
            $this->session->do_logout();
            $this->redirect($this->floodgateUrl);
        } else {
            $this->display();
        }
    }

    public function purge_wp_scripts() {
        global $wp_scripts;
        if(!isset($wp_scripts) || !is_object($wp_scripts)) $wp_scripts = new WP_Scripts();
        $wp_scripts->registered = array();
        $wp_scripts->queue = array();
    }

    public static function redirect($url) {
        //echo "<p>You should be redirected to: $url</p>"; exit;

        if ( !headers_sent() ) {
            wp_redirect($url);
        } else {
            //$url = site_url($url);
?>

<meta http-equiv="Refresh" content="0; URL=<?php echo $url; ?>">
<script type="text/javascript">
    <!--
    document.location.href = "<?php echo $url; ?>"
    //-->
</script>
</head>
<body>
Sorry. Please use this <a href="<?php echo $url; ?>" title="New Post">link</a>.
</body>
</html>

<?php
        }
        exit();
    }

}
