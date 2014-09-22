<?php
/**
 * Created as a wrapper to whatever library we are currently using to connect to Twitter
 */

require_once 'tmhOAuth.php';
require_once 'tmhUtilities.php';
@session_start();


class ei8TwitterObj {

    /* Change the following 3 lines as needed */
    private $user_agent         = 'Content XLerator Plugin';
    private $consumer_key       = '1Hfe1DxsXqqINYJH2cDQ';
    private $consumer_secret    = 'ikhnqDVHzQWUXcL4jPfetvoqooNizNoGk7Anl938rq4';
    private $auth_method        = 'authenticate'; // 'authenticate' for web, 'authorize' for desktop apps

    public  $obj                = null;
    public  $callback           = '';

    private $oauth_token         = '';
    private $oauth_secret        = '';
    private $twitter_token      = '';
    private $twitter_secret     = '';
    private $twitter_info       = '';
    private $oauth              = '';

    function __construct($twitter_token=null, $twitter_secret=null) {
        $this->obj = new tmhOAuth(array(
          'consumer_key'    => $this->consumer_key,
          'consumer_secret' => $this->consumer_secret,
          'user_agent'      => $this->user_agent
        ));

        $this->setTokens($twitter_token, $twitter_secret);
    }

    function setCallback($callback='') {
        $this->setValue('callback',$callback);
    }
    
    function setAccessToken($token='') {
        if(empty($token)) {
            if(!empty($this->oauth_token)) $this->setTokens();
        } else $this->setTokens($token['oauth_token'], $token['oauth_token_secret']);
    }

    function setTokens($twitter_token=null, $twitter_secret=null) {
        $this->setValue('twitter_token',$twitter_token);
        $this->setValue('twitter_secret',$twitter_secret);
        $this->obj->config['user_token']  = $this->twitter_token;
        $this->obj->config['user_secret'] = $this->twitter_secret;
    }

    function getAuthTokens() {
        return array($this->oauth_token, $this->oauth_secret);
    }

    function getTokens() {
        return array($this->twitter_token, $this->twitter_secret);
    }

    function setValue($name,$value) {
        $this->$name = $value;
    }

    function setOAuth($oauth='') {
        $_SESSION['oauth'] = $oauth;
        $this->setValue('oauth',$oauth);
        $this->setValue('oauth_token',$oauth['oauth_token']);
        $this->setValue('oauth_secret',$oauth['oauth_secret']);
        $this->setAccessToken($oauth);
    }

    function getValue($name) {
        return $this->$name;
    }

    function getAuthorizationUrl() {
        return $this->authorize_user('',false);
    }

    //Start the oauth dance...request a temporary token
    function authorize_user($method='', $redirect = true) {
        if(empty($method)) $method=$this->auth_method;
        $params = array(
            'oauth_callback'     => $this->callback,
            'x_auth_access_type' => 'write'
        );
        
        $code = $this->obj->request('POST', $this->obj->url('oauth/request_token', ''), $params);
        
        if ($code == 200) {
            $this->setOAuth($this->obj->extract_params($this->obj->response['response']));
            $authurl = $this->obj->url("oauth/{$method}", '') .  "?oauth_token={$this->oauth_token}";
            if($redirect) {
                header("Location: ".$authurl);
                //echo the url here in case the redirect failed
                echo "<p><a href='{$authurl}'>Click here to complete the Twitter authorization</a></p>";
            } else return $authurl;
        } else {
            $this->outputError();
        }
    }

    //finish the oauth dance
    //This runs when Twitter redirects the user to the callback. Exchange the temporary token for a permanent access token
    function authorize_app() {
        $code = $this->obj->request('POST', $this->obj->url('oauth/access_token', ''), array(
            'oauth_verifier' => $_REQUEST['oauth_verifier']
        ));

        if ($code == 200) {
            $this->setAccessToken($this->obj->extract_params($this->obj->response['response']));
            $this->setOAuth();
            return $this->getTokens();
            //header("Location: {$this->callback}");
        } else {
            $this->outputError($this->obj);
        }
    }
    
    // Once the user has authenticated, validate the user as needed
    function validate_user() {
        $code = $this->obj->request('GET', $this->obj->url('1.1/account/verify_credentials'));
        
        if ($code == 200) {
            $this->twitter_info = json_decode($this->obj->response['response']);
        } else {
            $this->twitter_info = false;
            //$this->outputError();
        }
        return $this->twitter_info;
    }

    //tweet
    function tweet($tweet) {
        $code = $this->obj->request('POST', $this->obj->url('1.1/statuses/update'), array(
          'status' => $tweet
        ));

        if ($code == 200) {
          return true;
          //tmhUtilities::pr(json_decode($this->obj->response['response']));
        } else {
          return false;
          //tmhUtilities::pr($this->obj->response['response']);
        }
    }

    function outputError() {
        echo 'Error: ' . $this->obj->response['response'] . PHP_EOL;
        tmhUtilities::pr($this->obj);
    }

}
?>