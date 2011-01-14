<?php  
/** Include the bootstrap for setting up WordPress environment */
require('../../../wp-load.php');

session_start();

// check the captcha code if required
if(ei8_xmlrpc_get_option('ei8_xmlrpc_use_captcha')==1 && isset($_REQUEST['Submit'])){
    $key=substr($_SESSION['captcha_key'],0,5);
    $number = $_REQUEST['number'];
    if($number!=$key){
        echo '<center><p style="font-face: Verdana; color: #FF0000; font-weight: bold;">
            Validation string not valid! Click <a href="javascript:history.go(-1);">HERE</a> to go back and try again!</p></center>';
//        echo "<p>User submitted string : ".$number."</p>";
//        echo "<p>\$_SESSION['captcha_key'] : ".$key."</p>";
        exit;
    }
}

//set up some variables
$uploadPath    = ei8_xmlrpc_get_upload_dir() ;
$uploadURL     = ei8_xmlrpc_get_upload_dir(1) ;
$wpurl         = get_bloginfo('wpurl');
list($userName, $passWord) = ei8_xmlrpc_get_login();

//set the default status to "published" so that it triggers the xmlrpc-notifier
$poststatus = "1";

// set up xmlrpc library calls
require_once(ABSPATH . 'wp-admin/includes/admin.php');
require_once(ABSPATH . WPINC . '/class-IXR.php');
$client = new IXR_Client($wpurl."/xmlrpc.php");  

// grab the data from the form post
$mailcontentraw = $_REQUEST['comment'];
$title = $_REQUEST['title'];
$mailcontentraw2 = htmlspecialchars($mailcontentraw);
$mailcontent = stripslashes($mailcontentraw2);
$spacer = "<br><hr width=100><br>";

// grab the uploaded file and grab the extension 
// we'll tack it back on after generating a unique but clean file name
$fileExt  = findexts($_FILES['uploadedfile']['name']); 
$fileName = rand () ;
$theFile = $fileName.".".$fileExt;

// save the file
move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $uploadPath.$theFile);

// if a file was uploaded, we need to caption it so we update the content variable
if(!empty($fileExt)) {
    if($_REQUEST['fileaction']=="attached_doc") {
        $mailcontent = $mailcontent.'<br><a href="'.$uploadURL.$theFile.'" target="_blank">'.$theFile.'</a>';
    } else { 
        $mailcontent = "<img src=\"".$uploadURL.$theFile."\" height=\"135\" hspace=\"10\" vspace=\"5\" align=\"left\" valign=\"middle\"><br>".$mailcontent;
    }
} 


// make the call to the xmlrpc server to save the post
$content['title'] = $title;   
$content['description'] = $mailcontent;  
if (!$client->query('metaWeblog.newPost','', $userName, $passWord, $content, $poststatus)) {
   //there is an error being returned by the IXR_Client when a post status is set to publish
   //only because there is no response being returned from the server, even though the post is accepted
   //so for now it seems safe to ignore this particular error
   if($client->getErrorCode() != -32700)  
       die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());  
}

// send 'em back to the submit page
$submitPage = ei8_xmlrpc_get_option('ei8_xmlrpc_submit_form');
if(!ereg("^http",$submitPage)) {
    $doSlash = (ereg("^/",$submitPage)) ? "" : "/" ;
    $submitPage = $wpurl.$doSlash.$submitPage;
}
//handle the notifications
$submitPage .= (ereg("\?",$submitPage)) ? "&" : "?" ;
$submitPage .= "success=";
$submitPage .= (!empty($_REQUEST['ei8_xmlrpc_a'])) ? $_REQUEST['ei8_xmlrpc_a']."#".$_REQUEST['ei8_xmlrpc_a'] : "1" ;

$then = gmstrftime("%a, %d %b %Y %H:%M:%S GMT");                            
header("Expires: $then");                                                   
header("Location: ".$submitPage);
exit;

//This function separates the extension from the rest of the file name and returns it
function findexts ($filename) {
    $filename = strtolower($filename) ;
    $exts     = split("[/\\.]", $filename) ;
    $n        = count($exts)-1;
    $exts     = $exts[$n];
    return $exts;
}

?>