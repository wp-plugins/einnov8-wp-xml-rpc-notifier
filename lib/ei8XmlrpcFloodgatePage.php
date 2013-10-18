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
    public $floodgateTypes;
    public $floodgateTargets;

    public $session;
    public $session_is_valid;

    public $showNav;
    public $showContent;
    public $breadCrumb;
    public $currentType;
    public $currentTarget;
    public $queryString;

    public function __construct() {
        global $floodgate;

        $this->css          = ei8_plugins_url('/floodgate.css');
        $this->logo         = ei8_xmlrpc_get_floodgate_option('logo');
        $this->clientName   = ei8_xmlrpc_get_floodgate_option('client_name');
        $this->resellerName = ei8_xmlrpc_get_floodgate_option('reseller_name');

        $this->floodgateUrl = get_bloginfo('wpurl').'/'.ei8_xmlrpc_floodgate_get_name().'/';
        $this->floodgateTypes = ei8_xmlrpc_floodgate_get_types();

        $ft = new ei8XmlrpcFloodgateTargets();
        $this->floodgateTargets = $ft->targets;

        //get the current type and target from request var
        list($this->currentType,$this->currentTarget,$this->queryString) = explode('/',$floodgate,3);
        if(empty($this->currentTarget)) $this->currentTarget = $this->get_default_target();

        $this->session = new ei8XmlrpcFloodgateSession();
        $this->session_is_valid = $this->session->validate();
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
        if($this->currentType && $this->floodgateTypes[$this->currentType]) {
            $breadCrumbs[] = $this->floodgateTypes[$this->currentType];
            $breadCrumbs[] = ($this->currentTarget) ? $this->floodgateTargets[$this->currentTarget]->title : "ERROR" ;
        }
        $this->breadCrumb = implode(' :: ', $breadCrumbs);
    }

    private function build_content() {
        $col    = array();
        $col[1] = $col[2] = $col[3] = array();
        $col[1][] = $this->build_content_welcome();

        switch($this->currentType) {
            default:
            case 'home':
                break;
            case 'video':
                $col[2][] = $this->build_content_web_recorder();
                $col[3][] = $this->build_content_media_uploader();
                break;
            case 'audio':
                $col[2][] = $this->build_content_web_recorder();
                $col[3][] = $this->build_content_media_uploader();
                $col[3][] = $this->build_content_phone();
                break;
            case 'text':
                $col[2][] = $this->build_content_text_and_image();
                $col[3][] = $this->build_content_text_and_file();
                break;
            case 'image':
                break;
            case 'support':
                $col[2][] = $this->build_content_support();
                break;
            case 'login':
                $col[1] = array();
                $col[1][] = $this->build_content_login();
                break;
        }

        $this->showContent = "";
        //foreach($col as $colNum=>$contents) foreach($contents as $content) {
        for($colNum=1;$colNum<=3;$colNum++) {
            if($this->currentType=='login') $extra = 'content-login';
            else $extra = ($colNum==3) ? ' content-col-small content-col-end' : '' ;
            $this->showContent .= '<div class="content-col '.$extra.'">';
            foreach($col[$colNum] as $content) {
                list($title,$html) = $content;
                //$extra = ($colNum==3) ? ' content-box-small content-box-end' : '' ;
                $extra = '';
                $this->showContent .=<<<EOT
        		<div class="content-box $extra">
        		<div class="content-box-inner">
        			<h2>$title</h2>
        			<p>$html</p>
        		</div>
        		</div>
EOT;
            }
            $this->showContent .= '</div>';
        }
    }

    private function build_content_media_uploader() {
        $target = new ei8XmlrpcFloodgateTarget($this->currentTarget);
        $title  = "Media Upload";
        //$html   = "<p>Media Uploader Goes Here";
        $html   = ei8_xmlrpc_recorder_wrap('media', $target->target);
        return array($title, $html);
    }

    private function build_content_phone() {
        $title  = "Submit Audio By Telephone";
        $html   = "<p>Phone Info Goes Here</p>";
        return array($title, $html);
    }

    private function build_content_login() {
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
    }

    private function build_content_support() {
        $title  = "Get Support!";
        $html   = "<p>Support Form Goes Here</p>";
        return array($title, $html);
    }

    private function build_content_text_and_file() {
        $title  = "Submit a Written Post and File";
        $html   = ei8_xmlrpc_filter_tags("[ei8 Attachment Submit Form]");
        return array($title, $html);
    }

    private function build_content_text_and_image() {
        $title  = "Submit a Written Post and Image";
        $html   = ei8_xmlrpc_filter_tags("[ei8 Simple Submit Form]");
        return array($title, $html);
    }

    private function build_content_tweet() {
        $title  = "Submit a tweet";
        $html   = ei8_xmlrpc_filter_tags("[ei8 Twitter Form]");
        return array($title, $html);
    }

    private function build_content_web_recorder() {
        $target = new ei8XmlrpcFloodgateTarget($this->currentTarget);
        $title  = "Submit Video or Audio";
        //$html   = "<p>Web Recorder Goes Here";
        $html   = ei8_xmlrpc_recorder_wrap('tall', $target->target);
        return array($title, $html);
    }

    private function build_content_welcome() {
        $title  = "How To Use FLOODtech";
        $html   = ei8_xmlrpc_get_option('ei8_xmlrpc_floodgate_text_'.$this->currentType);
        return array($title, $html);
    }

    private function build_nav() {
        $this->showNav = $this->build_nav_type('Home');
        foreach($this->floodgateTypes as $type=>$typeName) $this->showNav .= $this->build_nav_type($typeName,$type);
        $this->showNav .= $this->build_nav_type('Support','support');
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

    private function build_nav_type($title,$type='') {
        //first set up the url
        $url = $this->build_floodgate_url($type);

        //figure out if there are any subs to handle for this type
        $subsCT = 0;
        $requireSubsMissing = false;
        //echo "<p>Processing type: $type</p>";
        if($type!='' && $type!='support') {
            $ft = new ei8XmlrpcFloodgateTargets($type);
            $subsCT = count($ft->targets);
            if($subsCT<1) $requireSubsMissing = true;
            //echo "<p>Checking for subs...found($subsCT)<pre>"; print_r($ft); echo "</pre></p>";
        }


        //could change the class here if no subs
        if ($requireSubsMissing) {
            $html = sprintf('<li class="menu-item deactivated" title="There are no targets set up for this type"><span>%s</span>', $title);
        } else {
            $showActive = ($showDeactivate=='' && $this->currentType==$type) ? 'active' : '' ;
            $html = sprintf('<li class="menu-item %s"><a href="%s">%s</a>',$showActive, $url, $title);
        }
        if($subsCT>=1) {
            $html .= '<ul class="sub-menu">';
            foreach($ft->targets as $target) {
                $showActive = ($this->currentTarget==$target->id) ? 'active' : '' ;
                $html .= sprintf('<li class="menu-item %s"><a href="%s">%s</a></li>', $showActive, $url.$target->id.'/', $target->title);
            }
            $html .= '</ul>';
        }
        $html .= '</li>';
        return $html;
    }

    private function build_page() {
        $adminBuffer = (is_multisite() && is_admin_bar_showing()) ? 'adminbuffer' : '' ;
        $html =<<<EOT
<!DOCTYPE html>

<html>
<head>
    <link rel="stylesheet" href="$this->css" type="text/css" media="screen,projection" />
    <style media="screen,projection">
        #header2 {
            background: url('$this->logo') no-repeat;
            height: 80px;
        }
    </style>
</head>

<body>
    <header class="floatarea $adminBuffer">
        <section id="innerheader" class="wrap">
            <div id="titles">
                <h2>$this->resellerName</h2>
                <h1>$this->clientName</h1>
            </div>
            <nav id="mainnav">
                <ul class="menu">
                    $this->showNav
                </ul>
            </nav>
        </section>
    </header>

    <section id="header2" class="wrap floatarea">
        <div id="breadcrumb">
            <h1>$this->breadCrumb</h1>
        </div>
    </section>

    <section id="content" class="wrap floatarea">
        $this->showContent
    </section>

    <footer></footer>
</body>
</html>
EOT;
        return $html;
    }

    public function handle() {
        if(strstr($this->currentType,'logout') || strstr($this->currentTarget,'logout') || strstr($this->queryString,'logout')) {
            $this->session->do_logout();
            $this->redirect($this->floodgateUrl);
        } else {
            $this->display();
        }
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

    public function redirect($url) {
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
