<?php  
/** Include the bootstrap for setting up WordPress environment */
require('../../../wp-load.php');
//require('/Users/yipeecaiey/www/wordpress/wp-load.php');

//include plugin for function usage

session_start();
$errorMessage = false;

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

// send 'em back to the submit page
$submitPage = ei8_xmlrpc_get_option('ei8_xmlrpc_submit_form');
if(empty($submitPage)) {
    $submitPage = $_SERVER['HTTP_REFERER'];
} elseif(!preg_match('#^http#',$submitPage)) {
    $doSlash = (!preg_match('#^\/#',$submitPage)) ? "/" : "" ;
    $submitPage = get_bloginfo('wpurl').$doSlash.$submitPage;
}

$submitPage .= (strstr($submitPage,"\?")) ? "" : "?" ;


if ( !isset($_SERVER['HTTP_REFERER']) || 
    (isset($_SERVER["HTTP_HOST"]) && !stristr($_SERVER['HTTP_REFERER'], $_SERVER["HTTP_HOST"]))) {
    $submitPage .= "&errorMessage=".urlencode("Submissions from this source are not allowed");
    
    $then = gmstrftime("%a, %d %b %Y %H:%M:%S GMT");                            
    header("Expires: $then");                                                   
    header("Location: ".$submitPage);
    exit;
}

//process twitter posts
if(isset($_REQUEST['ei8_xmlrpc_twitter_post'])) {
    //session_start();
    
    require 'lib/EpiCurl.php';
    require 'lib/EpiOAuth.php';
    require 'lib/EpiTwitter.php';
    require 'lib/secret.php';
    
    $twitterToken  = ei8_xmlrpc_get_option('ei8_xmlrpc_twitter_token');
    $twitterSecret = ei8_xmlrpc_get_option('ei8_xmlrpc_twitter_secret');
    $status = $_REQUEST['ei8_xmlrpc_tweet'];
    
    if(empty($twitterToken) || empty($twitterSecret)) {
        $errorMessage = "Twitter not configured correctly.";
    } elseif(empty($status)) {
        $errorMessage = "Nothing to post!!";
    } elseif(strlen($status)>140) {
        $errorMessage = "140 Character Maximum Exceeded. Please try again";
    } else {

        $twitterObj = new EpiTwitter($consumer_key, $consumer_secret);
        //$twitterObj->setCallBack( ei8_xmlrpc_get_home_url() . ei8_xmlrpc_get_option('ei8_xmlrpc_submit_form')."?&errorMessage=Twitter_unknown#ei8xmlrpctwitterform"); 
        $twitterObj->setToken($twitterToken, $twitterSecret);
    	$twitterInfo= $twitterObj->get_accountVerify_credentials();
    	$twitterInfo->response;
        		
    	$username = $twitterInfo->screen_name;
    	$profilepic = $twitterInfo->profile_image_url;
        
        $update_status = $twitterObj->post_statusesUpdate(array('status' => $status));
        $temp = $update_status->response;
        
        if(!$success) $errorMessage = $tweet->error;
        //print("<p>Tweet: <pre>");
        //print_r($twitterObj);
        //print("</pre></p>");
    }
    //print("<p>Finished processing Tweet");
    //exit();

//process all form submissions
} else {
    //set up some variables
    $uploadPath    = ei8_xmlrpc_get_upload_dir() ;
    $uploadURL     = ei8_xmlrpc_get_upload_dir(1) ;
    $wpurl         = get_bloginfo('wpurl');
    $mediaAlign    = 'align'.ei8_xmlrpc_get_option('ei8_xmlrpc_media_align');
    if($mediaAlign=='align') $mediaAlign = 'alignleft';
    $mediaClass = $mediaAlign." ei8-embedded-content";
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
//    $fileExt  = findexts($_FILES['uploadedfile']['name']);
//    $fileName = rand () ;
//    $theFile = $fileName.".".$fileExt;
//    $fileName  = $_FILES['uploadedfile']['name'];
//    $theFile   = rand() . "." . findexts($fileName);

    //grab the uploaded file name
    $fileName   = $_FILES['uploadedfile']['name'];
    //change to lowercase
    $fileName   = strtolower($fileName);
    //get the file extension
    $fileParts  = explode('.',$fileName);
    $fileExt    = array_pop($fileParts);
    $fileName   = implode('.',$fileParts);
    //remove any spaces in the file name
    $fileName   = str_replace(" ", "_",$fileName);

    //ensure a unique filename
    $i          = 0;
    $fileUnique = "";
    while(file_exists($uploadPath.$fileName.$fileUnique.".".$fileExt)) {
        $i++;
        $fileUnique = "_".str_pad($i,2,"0",STR_PAD_LEFT);
    }
    $theFile = $fileName.$fileUnique.".".$fileExt;

/*    echo "<p>";
    echo "<p>fileName: $fileName</p>";
    echo "<p>fileUnique: $fileUnique</p>";
    echo "<p>fileExt: $fileExt</p>";
    echo "<pre>"; print_r($_FILES['uploadedfile']); echo "</pre>";
    echo "</p>";
    exit();
*/
    // save the file
    move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $uploadPath.$theFile);
    
    // if a file was uploaded, we need to caption it so we update the content variable
    if(!empty($fileExt)) {
        if($_REQUEST['fileaction']=="attached_doc") {
            $mailcontent = $mailcontent.'<br><a href="'.$uploadURL.$theFile.'" target="_blank">'.$theFile.'</a>';
        } else {
            $mailcontent = "<img src=\"".$uploadURL.$theFile."\" height=\"135\" class=\"".$mediaClass."\"><br>".$mailcontent;
        }
    } 
    
    
    // make the call to the xmlrpc server to save the post
    $content['title'] = $title;
    //$content['description'] = $mailcontent;
    $content['description'] = "&nbsp;".$mailcontent;
    if (!$client->query('metaWeblog.newPost','', $userName, $passWord, $content, $poststatus)) {
       //there is an error being returned by the IXR_Client when a post status is set to publish
       //only because there is no response being returned from the server, even though the post is accepted
       //so for now it seems safe to ignore this particular error
       //if($client->getErrorCode() != -32700)
       if($client->getErrorCode() != -32300 && $client->getErrorCode() != -32700)
           die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
    }
}
    
// send 'em back to the submit page
//this has been moved up the page to handle unwanted submissions
//$submitPage = ei8_xmlrpc_get_option('ei8_xmlrpc_submit_form');
//if(!ereg("^http",$submitPage)) {
//    $doSlash = (ereg("^/",$submitPage)) ? "" : "/" ;
//    $submitPage = $wpurl.$doSlash.$submitPage;
//}
//$submitPage .= (ereg("\?",$submitPage)) ? "" : "?" ;
//handle the notifications
if($errorMessage) $submitPage .= "&errorMessage=".urlencode($errorMessage);
$submitPage .= "&success=";
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