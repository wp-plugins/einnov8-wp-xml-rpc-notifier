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

    public $showNav;
    public $showContent;
    public $breadCrumb;
    public $currentType;
    public $currentTarget;
    public $queryString;

    public function __construct() {
        global $floodgate,$floodgateTypes;

        $this->css          = ei8_plugins_url('/floodgate.css');
        $this->logo         = ei8_xmlrpc_get_floodgate_option('logo');
        $this->clientName   = ei8_xmlrpc_get_floodgate_option('client_name');
        $this->resellerName = ei8_xmlrpc_get_floodgate_option('reseller_name');

        $this->floodgateUrl = get_bloginfo('wpurl').'/'.ei8_xmlrpc_floodgate_get_name().'/';
        $this->floodgateTypes = $floodgateTypes;

        $ft = new ei8XmlrpcFloodgateTargets();
        $this->floodgateTargets = $ft->targets;

        //get the current type and target from request var
        list($this->currentType,$this->currentTarget,$this->queryString) = explode('/',$floodgate,3);
        if(empty($this->currentTarget)) $this->currentTarget = $this->get_default_target();

    }

    private function get_default_target($type='') {
        if($type=='') $type = $this->currentType;
        if($type=='') return '';
        $ft = new ei8XmlrpcFloodgateTargets($type);
        return (count($ft->targets)>=1) ? $ft->targets[0]->id : '' ;
    }

    private function build_breadcrumb() {
        //$this->breadCrumb = "YOU ARE HERE";
        $breadCrumbs = array();
        //$breadCrumbs[] = $this->resellerName);
        if($this->currentType) $breadCrumbs[] = $this->floodgateTypes[$this->currentType];
        if($this->currentTarget) $breadCrumbs[] = $this->floodgateTargets[$this->currentTarget]->title;
        $this->breadCrumb = implode(' :: ', $breadCrumbs);
    }

    private function build_content() {

        $this->showContent =<<<EOT
    		<div class="content-box">
    		<div class="content-box-inner">
    			<h2>How To Use FLOODtech</h2>
    			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur vitae hendrerit sapien, ut placerat magna. Aenean molestie ac nibh pharetra ornare. Nam mollis gravida magna, auctor placerat magna luctus at. Aenean iaculis nulla justo, vel vestibulum augue pretium quis. Nunc ac orci molestie, adipiscing sapien sit amet, ullamcorper sapien. Ut accumsan sit amet leo non placerat. Donec pulvinar pulvinar tristique. Etiam laoreet diam quis erat ultricies, a tincidunt leo ullamcorper. </p>
    		</div>
    		</div>
    		<div class="content-box">
    		<div class="content-box-inner">
    			<h2>How To Use FLOODtech</h2>
    			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur vitae hendrerit sapien, ut placerat magna. Aenean molestie ac nibh pharetra ornare. Nam mollis gravida magna, auctor placerat magna luctus at. Aenean iaculis nulla justo, vel vestibulum augue pretium quis. Nunc ac orci molestie, adipiscing sapien sit amet, ullamcorper sapien. Ut accumsan sit amet leo non placerat. Donec pulvinar pulvinar tristique. Etiam laoreet diam quis erat ultricies, a tincidunt leo ullamcorper. </p>
    		</div>
    		</div>
    		<div class="content-box content-box-small content-box-end">
    		<div class="content-box-inner">
    			<h2>How To Use FLOODtech</h2>
    			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur vitae hendrerit sapien, ut placerat magna. Aenean molestie ac nibh pharetra ornare. Nam mollis gravida magna, auctor placerat magna luctus at. Aenean iaculis nulla justo, vel vestibulum augue pretium quis. Nunc ac orci molestie, adipiscing sapien sit amet, ullamcorper sapien. Ut accumsan sit amet leo non placerat. Donec pulvinar pulvinar tristique. Etiam laoreet diam quis erat ultricies, a tincidunt leo ullamcorper. </p>
    		</div>
    		</div>
EOT;
    }

    private function build_nav() {
        $this->showNav = $this->build_nav_type('Home');
        foreach($this->floodgateTypes as $type=>$typeName) $this->showNav .= $this->build_nav_type($typeName,$type);
        $this->showNav .= $this->build_nav_type('Support','support');
    }

    private function build_nav_type($title,$type='') {
        //first set up the url
        $url = $this->floodgateUrl;
        if($type!='') $url .= "$type/";

        //figure out if there are any subs to handle for this type
        $subsCT = 0;
        //echo "<p>Processing type: $type</p>";
        if($type!='' && $type!='support') {
            $ft = new ei8XmlrpcFloodgateTargets($type);
            $subsCT = count($ft->targets);
            //echo "<p>Checking for subs...found($subsCT)<pre>"; print_r($ft); echo "</pre></p>";
        }


        //could change the class here if no subs
        $showActive = ($this->currentType==$type) ? 'active' : '' ;
        $html = sprintf('<li class="menu-item %s"><a href="%s">%s</a>',$showActive, $url,$title);
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
        $adminBuffer = (is_admin_bar_showing()) ? 'adminbuffer' : '' ;
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

    public function display() {
        $this->build_breadcrumb();
        $this->build_nav();
        $this->build_content();
        echo $this->build_page();
    }

}
