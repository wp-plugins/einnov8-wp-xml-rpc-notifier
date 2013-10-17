<?php
//Load all classes in the lib directory
$dir = dirname(__FILE__).'/lib';
$libFiles = scandir($dir);
foreach($libFiles as $libFile) if(strstr($libFile,'ei8Xmlrpc')) {
    ei8_xmlrpc_admin_log("<p>Loading $libFile</p>");
    require_once($dir.'/'.$libFile);
}


add_action( 'init',              'ei8_xmlrpc_floodgate_controller' );
add_action( 'template_redirect', 'ei8_xmlrpc_floodgate_controller' );
add_filter( 'request',           'ei8_xmlrpc_floodgate_controller' );

function ei8_xmlrpc_floodgate_controller( $vars = '' )
{
    $name = ei8_xmlrpc_floodgate_get_name();
    $hook = current_filter();

    //echo "<p>GOT HERE! (hook:$hook/name:$name)</p>"; exit;

    // load 'style.php' from the current theme.
    'template_redirect' === $hook
        && get_query_var( $name )
        && load_template( dirname(__FILE__).'/floodgate.php')
        && exit;

    // Add a rewrite rule.
    'init' === $hook && ei8_xmlrpc_floodgate_update_endpoint();

    // Make sure the variable is not empty.
    'request' === $hook
        && isset ( $vars[$name] )
        && empty ( $vars[$name] )
        && $vars[$name] = 'home';

    return $vars;
}

function ei8_xmlrpc_floodgate_get_name() {
    return ei8_xmlrpc_get_floodgate_option('name');
}

function ei8_xmlrpc_floodgate_update_name($name='') {
    if($name!='') ei8_xmlrpc_update_floodgate_option('name',$name);
    ei8_xmlrpc_floodgate_update_endpoint();
    return $name;
}

function ei8_xmlrpc_floodgate_update_endpoint() {
    $result = add_rewrite_endpoint( ei8_xmlrpc_floodgate_get_name(), EP_ROOT );
    flush_rewrite_rules();
    return $result;
}

?>