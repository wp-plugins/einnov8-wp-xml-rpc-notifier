<?php  
/** Include the bootstrap for setting up WordPress environment */
require('../../../wp-load.php');
//require('/Users/yipeecaiey/www/wordpress/wp-load.php');

//include plugin for function usage

session_start();
$errorMessage = false;

// check the captcha code if required
//if(ei8_xmlrpc_get_option('ei8_xmlrpc_use_captcha')==1 && isset($_REQUEST['Submit'])){
if(isset($_REQUEST['Submit'])){
    $key=substr($_SESSION['captcha_key'],0,5);
    $number = $_REQUEST['number'];
    if($number!=$key){
        ei8_xmlrpc_error_message('Validation string not valid!');
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
    $tmpFile    = $_FILES['uploadedfile']['tmp_name'];
    $fileName   = $_FILES['uploadedfile']['name'];
    //change to lowercase
    $fileName   = strtolower($fileName);
    //get the file extension
    $fileParts  = explode('.',$fileName);
    $fileExt    = array_pop($fileParts);
    $fileName   = implode('.',$fileParts);
    //remove any spaces in the file name
    $fileName   = str_replace(" ", "_",$fileName);

    /* validate file upload */
    //check file extension

    $validFileExts = array(
        'image' => array('jpg', 'jpeg', 'png', 'gif'),
        'doc'   => array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'pps', 'ppsx', 'odt', 'ods', 'xls', 'xlsx')
    );

    //array library of mime types and extensions
    $mimeTypes = array(

        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai'  => 'application/postscript',
        'eps' => 'application/postscript',
        'ps'  => 'application/postscript',

        // ms office
        'doc'  => 'application/msword',
        'docx' => 'application/msword',
        'rtf'  => 'application/rtf',
        'xls'  => array('application/excel','application/vnd.ms-excel','application/x-excel','application/x-msexcel'),
        'xlsx' => array('application/excel','application/vnd.ms-excel','application/x-excel','application/x-msexcel'),
        'ppt'  => array('application/vnd.ms-powerpoint','application/mspowerpoint','application/powerpoint','application/x-mspowerpoint'),
        'pptx' => array('application/vnd.ms-powerpoint','application/mspowerpoint','application/powerpoint','application/x-mspowerpoint'),
        'pps'  => array('application/vnd.ms-powerpoint','application/mspowerpoint','application/powerpoint','application/x-mspowerpoint'),
        'ppsx' => array('application/vnd.ms-powerpoint','application/mspowerpoint','application/powerpoint','application/x-mspowerpoint'),

        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    //only do this if there is an uploaded file to be tested
    if(!empty($_REQUEST['fileaction'])) {
        $myFileTypes = $validFileExts['image'];
        if($_REQUEST['fileaction']=="attached_doc") $myFileTypes = array_merge($myFileTypes,$validFileExts['doc']);

        //first check the named file extension
        if(!in_array($fileExt,$myFileTypes)) {
            $msg = "<h1>Invalid file extension</h1><br>";
            $msg2 = sprintf("You have uploaded a '.%s' file, which is not allowed for security reasons.<br>",$fileExt);
            $msg2 .= "If you believe you have received this message in error, please contact your webmaster for technical assistance.";
            ei8_xmlrpc_error_message($msg,$msg2);
        }

        //now check to see the file's mime type
        if(function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimeType = finfo_file($finfo, $tmpFile);
            finfo_close($finfo);
        } else {
            $mimeType = $_FILES['uploadedfile' ]['type'];
        }
        if(empty($mimeType)) $mimeType = "unknown";

        //reject (helpfully)
        if($mimeType=="application/octet-stream") {
            $msg = "<h1>Invalid file type</h1><br>";
            $msg2 = sprintf("You have uploaded a '.%s' file (%s), which is not allowed for security reasons.<br>",$fileExt, $mimeType);
            $msg2 .= "** Is your file still open on your computer? If it is, make sure the file is closed and try to upload it again.**";
            $msg2 .= "If you still believe you have received this message in error, please contact your webmaster for technical assistance.";
            ei8_xmlrpc_error_message($msg, $msg2);

        } elseif (!in_array($mimeType,(array)$mimeTypes[$fileExt])) {
            $msg = "<h1>Invalid file type</h1><br>";
            $msg2 = sprintf("You have uploaded a '.%s' file (%s), which is not allowed for security reasons.<br>",$fileExt,$mimeType);
            $msg2 .= "If you believe you have received this message in error, please contact your webmaster for technical assistance.";
            ei8_xmlrpc_error_message($msg, $msg2);
        }
    }

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
    move_uploaded_file($tmpFile, $uploadPath.$theFile);
    
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

function ei8_xmlrpc_error_message($msg,$msg2="") {
    $html =<<<EOT
<div style="text-align: center;display: block; margin: auto;font-face: Verdana; color: #000000; font-weight: bold;">
    <span style="color: #FF0000;">$msg</span>$msg2<br><br>
    Click <a href="javascript:history.go(-1);">HERE</a> to go back and try again!
</div>
EOT;
    echo $html;
    exit;
}

//This function separates the extension from the rest of the file name and returns it
function findexts ($filename) {
    $filename = strtolower($filename) ;
    $exts     = split("[/\\.]", $filename) ;
    $n        = count($exts)-1;
    $exts     = $exts[$n];
    return $exts;
}

?>