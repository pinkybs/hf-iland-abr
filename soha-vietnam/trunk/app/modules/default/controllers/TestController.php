<?php

class TestController extends Zend_Controller_Action
{

    protected function vailid()
    {
    	$skey = $_COOKIE['hf_skey'];
    	if (!$skey) {
    		return false;
    	}

    	$tmp = explode('.', $skey);
    	if (empty($tmp)) {
    		return false;
    	}
    	$count = count($tmp);
    	if ($count != 5 && $count != 6) {
    		return false;
    	}

        $uid = $tmp[0];
        $puid = $tmp[1];
        $session_key = base64_decode($tmp[2]);
        $t = $tmp[3];

        $rnd = -1;
        if ($count == 5) {
        	$sig = $tmp[4];
	        $vsig = md5($uid . $puid . $session_key . $t . APP_SECRET);
	        if ($sig != $vsig) {
	        	return false;
	        }
        } else if ($count == 6) {
        	$rnd = $tmp[4];
        	$sig = $tmp[5];
        	$vsig = md5($uid . $puid . $session_key . $t . $rnd . APP_SECRET);
        	if ($sig != $vsig) {
	        	return false;
	        }
        }

        //max long time one day
        if (time() > $t + 86400) {
        	return false;
        }

        return array('uid' => $uid, 'puid' => $puid, 'session_key' => $session_key,  't' => $t, 'rnd' => $rnd);
    }

    public function getauthAction()
    {
        require_once 'Ming/OAuth/Mingoauth.php';
        define(CONSUMER_KEY, 'f59691447230992e39ae3e2ef9ce0c67');
        define(CONSUMER_SECRET, 'f41635956309617845dcb1f3fc59fa51');

        $connection = new MingOAuth(CONSUMER_KEY, CONSUMER_SECRET);
        $request_token = $connection->getRequestToken(HOST.'/test/access');
        if ($request_token && isset($request_token['oauth_token'])) {
            //echo json_encode($request_token);
            $otoken = $request_token['oauth_token'];
            $osecret = $request_token['oauth_token_secret'];
            $t = time();
            $oauthKey = $otoken.'_'.$osecret;
            $sig = md5($oauthKey . $t . APP_SECRET);
            $hfOauth = base64_encode($oauthKey) . '_' . $t . '_' . $sig;
            setcookie('hf_oauth', $hfOauth , 0, '/', str_replace('http://', '.', HOST));

            $url = $connection->getAuthorizeURL($request_token);
            if (200 == $connection->http_code) {
                $this->_redirect($url);
            }
            else {
                info_log('getAuthorizeURL:'.json_encode($connection->http_info), 'oauth_err');
            }
        }
        else {
            info_log('getRequestToken:'.json_encode($request_token), 'oauth_err');
        }
        exit;
    }


    public function accessAction()
    {
        info_log(json_encode($_REQUEST) ,'aa');
        require_once 'Ming/OAuth/Mingoauth.php';
        define(CONSUMER_KEY, 'f59691447230992e39ae3e2ef9ce0c67');
        define(CONSUMER_SECRET, 'f41635956309617845dcb1f3fc59fa51');

        if (!isset($_REQUEST['oauth_token']) || !isset($_REQUEST['oauth_verifier'])) {
            echo 'invalid req';
    		exit;
        }

        $hfOauth = $_COOKIE['hf_oauth'];
    	if (!$hfOauth) {
    		echo 'oauth error';
    		exit;
    	}

    	$tmp = explode('_', $hfOauth);
    	if (empty($tmp)) {
    		echo 'oauth error2';
    		exit;
    	}
    	$count = count($tmp);
    	if ($count != 3) {
    		echo 'oauth error3';
    		exit;
    	}

        $oauthKey = $tmp[0];
        $t = $tmp[1];
        $sig = $tmp[2];

        $vsig = md5(base64_decode($oauthKey) . $t . APP_SECRET);
        if ($sig != $vsig) {
        	echo 'oauth error4';
    		exit;
        }

        //max long time one day
        if (time() > $t + 86400) {
        	echo 'oauth error5';
    		exit;
        }

        //setcookie('hf_oauth', '' , 0, '/', str_replace('http://', '.', HOST));

        $oauthKey = base64_decode($oauthKey);
        $aryOauthKey = explode('_', $oauthKey);
        $otoken = $aryOauthKey[0];
        $osecret = $aryOauthKey[1];

        if ($otoken != $_REQUEST['oauth_token']) {
            echo 'oauth error6';
    		exit;
        }

        $connection = new MingOAuth(CONSUMER_KEY, CONSUMER_SECRET, $otoken, $osecret);
        /* Request access tokens from ming */
        $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
        if (200 != $connection->http_code) {
            info_log('getAccessToken:'.json_encode($access_token), 'oauth_err');
            info_log('getAccessToken:'.json_encode($connection->http_info), 'oauth_err');
        }

        $puid = $access_token['user_id'];
        echo json_encode($access_token);


        $connection2 = new MingOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
        //Thong tin tra ve kieu json object
        $uinfo = $connection2->get('GET/user/show');
        echo '<br/>';
        echo $uinfo['user']['username'];
        echo json_encode($uinfo);



        $finfo = $connection2->get('GET/user/appfriend');
        echo '<br/>';
        echo json_encode($finfo);
        exit;
    }




	public function pwdAction()
	{
		$pwd = $this->_request->getParam('pwd');
		$secret = $this->_request->getParam('secret');
		$md5 = md5($pwd. ':' . $secret);
		echo $md5;
		exit;
	}

    function testfeedAction()
	{
	    $info = $this->vailid();
	    $rest = Ming_Rest::getInstance($info['session_key']);
	    $feed = array('message' => 'it is test feed', 'link' => 'http://aa.bb', 'picture' => 'http://reklama.nk.pl/_/getImageII/?vid=2049&typ=nkbox&element=image&nc=596592596425410112011');
	    $aa = $rest->ming_postFeed($feed);
	    echo $aa;
	    echo '<br/>';
	    echo json_encode($aa);
	    exit;
	}

     function testreqAction()
	{
	    $info = $this->vailid();
	    $rest = Ming_Rest::getInstance($info['session_key']);
	    $aa = $rest->ming_postRequest('Would you play with me,Come on.', 'http://cc.dd');
	    echo $aa;
	    echo '<br/>';
	    echo json_encode($aa);
	    exit;
	}
	function getuserAction()
	{
		$uid = $this->_request->getParam('uid');
		$user = Hapyfish2_Platform_Bll_User::getUser($uid);
		print_r($user);
		exit;
	}

}