<?php

require_once 'Hapyfish2/Application/Abstract.php';

class Hapyfish2_Application_Sohavn extends Hapyfish2_Application_Abstract
{
    protected $_rest;

    protected $_request;

    protected $_puid;

    protected $_session_key;

    protected $_hfskey;

    protected $newuser;


    /**
     * Singleton instance, if null create an new one instance.
     *
     * @param Zend_Controller_Action $actionController
     * @return Hapyfish2_Application_Taobao
     */
    public static function newInstance(Zend_Controller_Action $actionController)
    {
        if (null === self::$_instance) {
            self::$_instance = new Hapyfish2_Application_Sohavn($actionController);
        }

        return self::$_instance;
    }

	/**
     * _init()
     *
     * @return void
     */
    protected function _init()
    {
        $this->_request = $this->getRequest();
        $this->_appId = APP_ID;
        $this->_appName = APP_NAME;
    }

    public function getSKey()
    {
    	return $this->_hfskey;
    }

    public function getPlatformUid()
    {
    	return $this->_puid;
    }

    public function getRest()
    {
    	return $this->_rest;
    }

    public function isNewUser()
    {
    	return $this->newuser;
    }

    public function checkValidate()
    {
        $request = $this->_request;
        $reqToken = $request->getParam('oauth_token');
        $reqVerifer = $request->getParam('oauth_verifier');
        if (!isset($reqToken) || !isset($reqVerifer)) {
            return 'invalid req';
        }

        $hfOauth = $_COOKIE['hf_oauth'];
    	if (!$hfOauth) {
    		return 'oauth error(cookie empty)';
    	}

    	$tmp = explode('_', $hfOauth);
    	if (empty($tmp)) {
    		return 'oauth error2';
    	}
    	$count = count($tmp);
    	if ($count != 3) {
    		return 'oauth error3';
    	}

        $oauthKey = $tmp[0];
        $t = $tmp[1];
        $sig = $tmp[2];

        $vsig = md5(base64_decode($oauthKey) . $t . APP_SECRET);
        if ($sig != $vsig) {
        	return 'oauth error4';
        }

        //max long time one day
        if (time() > $t + 86400) {
        	return 'oauth error5';
        }

        //setcookie('hf_oauth', '' , 0, '/', str_replace('http://', '.', HOST));

        $oauthKey = base64_decode($oauthKey);
        $aryOauthKey = explode('_', $oauthKey);
        $otoken = $aryOauthKey[0];
        $osecret = $aryOauthKey[1];

        if ($otoken != $reqToken) {
            return 'oauth error6';
        }

        $oauthRest = new Ming_OAuthRest($otoken, $osecret);
        $access_token = $oauthRest->getAccessToken($reqVerifer);
        if (!$access_token) {
            return 'error in oauth get access token';
        }

        //OK
        $puid = $access_token['user_id'];
        $sessionKey = $access_token['oauth_token'].'_'.$access_token['oauth_token_secret'];
        $this->_puid = $puid;
        $this->_rest = Ming_Rest::getInstance($sessionKey);
        $this->_rest->setUser($puid);
        $this->_session_key = $sessionKey;
        $this->newuser = false;

        return '';
    }

    protected function _getUser($data)
    {
        $user = array();
        $user['uid'] = $this->_userId;
        $user['puid'] = $data['uid'];
        $user['name'] = $data['name'];
        $user['email'] = $data['email'];
        $user['figureurl'] = $data['headurl'];
        $sex = isset($data['sex']) ? $data['sex'] : '';
        if ($sex == '1') {
            $gender = 1;
        } else if ($sex == '0') {
            $gender = 0;
        } else {
            $gender = -1;
        }
        $user['gender'] = $gender;

        return $user;
    }

    protected function _updateInfo()
    {
    	$userData = $this->_rest->ming_getUser();

    	if (!$userData) {
    		throw new Hapyfish2_Application_Exception('get user info error');
    	}

    	$puid = $this->_puid;
    	if ($puid != $userData['uid']) {
    		throw new Hapyfish2_Application_Exception('platform uid error');
    	}

    	try {
    		$uidInfo = Hapyfish2_Platform_Cache_UidMap::getUser($puid);
    		//first coming
    		if (!$uidInfo) {
    			$uidInfo = Hapyfish2_Platform_Cache_UidMap::newUser($puid);
    			if (!$uidInfo) {
    				throw new Hapyfish2_Application_Exception('generate user id error');
    			}
    			$this->newuser = true;
    		}
    	} catch (Exception $e) {
    		throw new Hapyfish2_Application_Exception('get user id error');
    	}

        $uid = $uidInfo['uid'];
        if (!$uid) {
        	throw new Hapyfish2_Application_Exception('user id error');
        }

        $this->_userId = $uid;

        $user = $this->_getUser($userData);
        if ($this->newuser) {
        	Hapyfish2_Platform_Bll_User::addUser($user);
        	//add log
        	$logger = Hapyfish2_Util_Log::getInstance();
        	$logger->report('100', array($uid, $puid, $user['gender']));
        } else {
        	Hapyfish2_Platform_Bll_User::updateUser($uid, $user, true);
        }

        $fids = $this->_rest->ming_getAppFriendIds();

        if ($fids !== null) {
        	//这块可能会出现效率问题，fids很多的时候，memcacehd get次数会很多
        	//优化方案，先根据fid切分到相应的memcached组，用getMulti方法，减少次数
        	$fids = Hapyfish2_Platform_Bll_User::getUids($fids);
			if ($this->newuser) {
        		Hapyfish2_Platform_Bll_Friend::addFriend($uid, $fids);
        	} else {
        		Hapyfish2_Platform_Bll_Friend::updateFriend($uid, $fids);
        		//Hapyfish2_Platform_Bll_Friend::addFriend($uid, $fids);
        	}
        }
    }



    /**
     * run() - main mothed
     *
     * @return void
     */
    public function run()
    {
		$this->_updateInfo();

        //P3P privacy policy to use for the iframe document
        //for IE
        header('P3P: CP=CAO PSA OUR');

        $uid = $this->_userId;
        $puid = $this->_puid;
        $session_key = $this->_session_key;
        $t = time();
        $rnd = mt_rand(1, ECODE_NUM);

        $sig = md5($uid . $puid . $session_key . $t . $rnd . APP_SECRET);

        $skey = $uid . '.' . $puid . '.' . base64_encode($session_key) . '.' . $t . '.' . $rnd . '.' . $sig;
        $this->_hfskey = $skey;
        setcookie('hf_skey', $skey , 0, '/', str_replace('http://', '.', HOST));
    }
}