<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yipeecaiey
 * Date: 10/18/13
 * Time: 4:19 AM
 * To change this template use File | Settings | File Templates.
 */
//this is WAY over-simplified (password only) NEEDS to be update do be more secure
class ei8XmlrpcFloodgateSession
{
    public  $logged;
    private $table;
    private $sess_pre;

    public function __construct() {
        $this->table = new ei8XmlrpcFloodgateDbTableOptions();
        $this->sess_pre = 'ei8_fgsess_';
        $this->get();
        //$this->validate();
        return $this;
    }

    public function validate() {
        return ($this->logged);
    }

    public function is_valid() {
        return ($this->logged);
    }

    public function try_login($password) {
        $option = new ei8XmlrpcFloodgateOptionFG();
        $dbPass = $option->get('pass');
        if($password==$dbPass && $password!='') return $this->do_login();
        else {
            //could do some fancy messaging here
            $this->do_logout();
        }
    }

    public function do_login() {
        $this->logged = true;
        $this->set();
        return true;
    }

    public function do_logout() {
        $this->destroy();
        return false;
    }

    private function get() {
        //first make sure there is a valid session to be checked
        if (!isset($_SESSION[$this->sess_pre.'logged']) ) $this->set_defaults();
        else {
            //retrieve from db
            $this->logged   = $_SESSION[$this->sess_pre.'logged'];
        }
    }

    private function set() {
        $_SESSION[$this->sess_pre.'logged']     = $this->logged;
    }

    private function set_defaults() {
        $this->logged   = false;
        $this->set();
    }

    private function destroy() {
        //update the session
        $this->set_defaults();
    }

    private function db_update() {
        $sql = sprintf("Update AdminSessions set lastDateTime=NOW() WHERE sess_hash='%s'",
            $_SESSION['sess_hasher']
        );
        $result=mysql_query($sql);
    }

}

//everything below here was the skeleton of the new auth system that was abandoned for speed of launch
/*
class ei8XmlrpcFloodgateSession
{
    public $logged;
    public $userid;
    public $username;

    private $hasher;
    private $sess_pre;
    private $db;
    private $user_table;
    private $sess_table;


    public function __construct() {
        global $wpdb;
        $this->sess_pre = 'ei8_fgsess_';
        $this->db = &$wpdb;
        $this->user_table = $this->db->prefix . "ei8_floodgate_users";
        $this->sess_table = $this->db->prefix . "ei8_floodgate_sessions";
        $this->validate();
    }

    public function validate() {
        $this->get();

        return false;
    }

    public function login($username,$password) {
        return false;
    }

    public function logout() {
        $this->destroy();
        return true;
    }

    private function get() {
        //first make sure there is a valid session to be checked
        if (!isset($_SESSION[$this->sess_pre.'userid']) ) $this->set_defaults();
        else {
            //retrieve from db
            $this->logged   = $_SESSION[$this->sess_pre.'logged'];
            $this->userid   = $_SESSION[$this->sess_pre.'userid'];
            $this->username = $_SESSION[$this->sess_pre.'username'];
            $this->hasher   = $_SESSION[$this->sess_pre.'hasher'];
        }
    }

    private function set() {
        //update session
        $_SESSION[$this->sess_pre.'logged']     = $this->logged;
        $_SESSION[$this->sess_pre.'userid']     = $this->userid;
        $_SESSION[$this->sess_pre.'username']   = $this->username;
        $_SESSION[$this->sess_pre.'hasher']     = $this->hasher;

        //update db
        $this->db_update();
    }

    private function set_defaults() {
        $this->logged   = false;
        $this->userid   = 0;
        $this->username = '';
        $this->hasher   = '';
        $this->set();
    }

    private function destroy() {
        //update the session
        $this->set_defaults();
    }

    private function db_update() {
        $sql = sprintf("Update AdminSessions set lastDateTime=NOW() WHERE sess_hash='%s'",
            $_SESSION['sess_hasher']
        );
        $result=mysql_query($sql);
    }

}

require ($sdir."dbConnect.inc");
//session_start();
$super_user = 'ojaiadmin';
$super_pass = '2c9a4d1c0cb2b3a913a89fde24bfafa6';
$num_hashes = 3; //NOTE! Changing this will invalidate all current passwords, including the one above

function Login($l_message,$l_class) {
    global $login_attempts;
    global $clubname, $menutext, $menutext_h, $bgcolor;
	echo "<html><head><title>$clubname Admin Area Login</title>\n";
	include "fonts.inc";
	echo "\n</head>\n<body>\n";
	echo "<form action='index.php' method=post>";
	echo "<table width=700 height='100%' border=1 cellspacing=0 cellpadding=0 align=center ";
	echo "bordercolor=Silver bgcolor=Black valign=top>";
	echo "<tr><td height=150 valign=middle>";
	include $admindir."adminlogo.inc";
	echo "</td></tr><tr><td align=CENTER valign=MIDDLE>";
	echo "<table border=0 cellspacing=0 cellpadding=4 align=center bordercolor=Black bgcolor=Silver>";
	echo "<tr><td align=center class=".$l_class." colspan=2>$l_message</td>";
	echo "</tr><tr>";
	echo "<td align=right class=formtitle>USERNAME:</td>";
	echo "<td><input type=text name=username size=30></td>";
	echo "</tr><tr>";
	echo "<td align=right class=formtitle>PASSWORD:</td>";
	echo "<td><input type=password name=password size=30></td>";
	echo "</tr><tr>";
	echo "<td align=center colspan=2><input type=submit value='---SUBMIT---'>";
	echo "<input type=hidden name=l_attempts value='$l_attempts'></td>";
	echo "</tr>";
	echo "</table></td></tr></table></form></body></html>";
}

function session_set($uid,$username) {
    $_SESSION['sess_logged'] = true;
    $_SESSION['sess_uid'] = $uid;
    $_SESSION['sess_username'] = $username;
    $_SESSION['sess_hasher'] = sess_hash();
    //$_SESSION['cookie'] = 0;
    //$_SESSION['remember'] = false;

    $sql = sprintf("update Admin set sess_hash='%s' WHERE username='%s' and id='%s'",
        $_SESSION['sess_hasher'],
        $_SESSION['sess_username'],
        $_SESSION['sess_uid']
    );
    $result=mysql_query($sql);

    $sql = sprintf("insert into AdminSessions set user_id='%s', username='%s', lastDateTime=NOW(), IP='%s', user_agent='%s', sess_hash='%s'",
        $_SESSION['sess_uid'],
        $_SESSION['sess_username'],
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'],
        $_SESSION['sess_hasher']
    );
    $result=mysql_query($sql);

    session_update();

    //notify_admin
    $messageParts = array();
    $messageParts[] = "A user logged into ojaicottage.com/admin";
    $messageParts[] = date("Y-m-d H:i:s");
    $messageParts[] = "Username: ".$_SESSION['sess_username'];
    $messageParts[] = "IP: ".$_SERVER['REMOTE_ADDR'];
    $envInfo = array(
        "SESSION"   => $_SESSION,
        "SERVER"    => $_SERVER
    );
    foreach($envInfo as $id=>$arr) {
        $messageParts[] = "";
        $messageParts[] = $id;
        foreach($arr as $key=>$val) $messageParts[] = "$key: $val";
    }
    $message = implode("\n",$messageParts);
    SendMail("tim.gallaugher@gmail.com", 'admin@ojaicottage.com', "Admin login notification", $message, false);

}

function session_update() {
    $sql = sprintf("Update AdminSessions set lastDateTime=NOW() WHERE sess_hash='%s'",
        $_SESSION['sess_hasher']
    );
    $result=mysql_query($sql);

    $sql = sprintf("insert into AdminActions set sess_hash='%s', request='%s', initDateTime=NOW()",
        $_SESSION['sess_hasher'],
        $_SERVER['REQUEST_URI']
    );
    $result=mysql_query($sql);
}

function password_hash($password, $salt = 'oJc_LoCK_DOWn',$algo = 'md5') {
    global $num_hashes;
    $pass = $password.$salt;
    for($i=1;$i<=$num_hashes;$i++) $pass = hash($algo,$pass);
    return $pass;
}

function sess_hash() {
    $sessinfo = $_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].rand();
    return password_hash($sessinfo);
}


//make sure there is a valid session to be checked
if (!isset($_SESSION['sess_uid']) ) {
    //echo "<p>No uid, resetting session</p>";
    session_defaults();
}

if (isset($_GET['logout'])) {a
    //echo "<p>Logout requested, resetting session</p>";
	session_defaults();
	header("Location: ".$baseurl);
}

//validate the session
if($_SESSION['sess_username'] != '' && $_SESSION['sess_username'] != 'superadmin') {
    $sql = sprintf("select sess_hash from Admin where username='%s' and id='%s' LIMIT 1",
        $_SESSION['sess_username'],
        $_SESSION['sess_uid']
    );
    $logins=mysql_query($sql);
    list($sess_hash) = mysql_fetch_row($logins);
    if($sess_hash!=$_SESSION['sess_hasher']) {
        //echo "<p>SESSION<pre>"; print_r($_SESSION); echo "</pre></p>";
        //echo "<p>Invalid session, resetting session</p>";
        //echo "<p>1: $sess_hash<br>2:".$_SESSION['sess_hasher']."</p>";
        //echo "<p>sql: $sql<pre>"; print_r($logins); echo "</pre></p>";

        //notify_admin
        $messageParts = array();
        $messageParts[] = "Invalid session, resetting session";
        $messageParts[] = date("Y-m-d H:i:s");
        $messageParts[] = "sess1: $sess_hash";
        $messageParts[] = "sess2: ".$_SESSION['sess_hasher'];
        $envInfo = array(
            "SESSION"   => $_SESSION,
            "SERVER"    => $_SERVER
        );
        foreach($envInfo as $id=>$arr) {
            $messageParts[] = "";
            $messageParts[] = $id;
            foreach($arr as $key=>$val) $messageParts[] = "$key: $val";
        }
        $message = implode("\n",$messageParts);
        SendMail("tim.gallaugher@gmail.com", 'admin@ojaicottage.com', "ALERT: Invalid session", $message, false);

        session_defaults();
        $l_message	= "Your session is no longer valid<br>Please log in again";
        $l_class	= "errormessage";
          //echo "<p>$l_message<br>entered: $password<br>hash1: '".password_hash($password)."'<br>hash2: '$dbpassword' </p>";
        Login($l_message,$l_class,$l_attempts);
        exit();
    }
}


if ($_SESSION['sess_logged'] && $_SESSION['sess_hasher']!='') {
    //log the access to a file
    //echo "<p>SESSION<pre>"; print_r($_SESSION); echo "</pre></p>";
    session_update();

} elseif ($username) {
    $sql = sprintf("select username, password, id from Admin where username='%s' LIMIT 1",
        mysql_real_escape_string($username));
	$logins=mysql_query($sql);
	//or die ("<p class=errormessage>error retrieving login info<br>Error: ".mysql_error()."</p>");

	list($dbusername, $dbpassword, $userID) = mysql_fetch_row($logins);

	if (password_hash($password)==$dbpassword && $username==$dbusername) {
		$_SESSION['logged_in_userid'] = $userID;
        session_set($userID,$dbusername);

    } elseif (password_hash($password)==$super_pass && $username==$super_user) {
        $_SESSION['master'] = 1;
        session_set('99999','superadmin');

	} else {
		$l_message	= "You have entered an incorrect password<br>Please try again";
		$l_class	= "errormessage";
        //echo "<p>$l_message<br>entered: $password<br>hash1: '".password_hash($password)."'<br>hash2: '$dbpassword' </p>";
		Login($l_message,$l_class,$l_attempts);
		exit();
	}

} else {
    //echo "<p>SESSION<pre>"; print_r($_SESSION); echo "</pre></p>";
    $l_message	= "Please login:";
	$l_class	= "subsectiontitle";
	Login($l_message,$l_class,$l_attempts);
	exit();
} //end if db_session
*/
?>