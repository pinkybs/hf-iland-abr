<?php

require_once 'Hapyfish2/Application/Abstract.php';

class Hapyfish2_Application_NateCy extends Hapyfish2_Application_Abstract
{
    protected $_rest;

    protected $_puid;

    protected $_hfskey;

    protected $_appParam;

    protected $_newuser;


    /**
     * Singleton instance, if null create an new one instance.
     *
     * @param Zend_Controller_Action $actionController
     * @return Hapyfish2_Application_Facebook
     */
    public static function newInstance(Zend_Controller_Action $actionController)
    {
        if (null === self::$_instance) {
            self::$_instance = new Hapyfish2_Application_NateCy($actionController);
        }

        return self::$_instance;
    }

	/**
     * check signature is valid
     *
     * @param output array $parameters
     * @return bool
     *
     *  opensocial_owner_id         eg: 21263792
     *  opensocial_viewer_id        eg: 21263792
     *  opensocial_app_id           eg: 1645
     *  opensocial_app_url          eg: http://dsfs.nate.com/sandbox/20110309/10026972753/6613705f234206869ce8b3374d7bb740.xml
     *
     *  oauth_token                 ''[empty]
     *  oauth_consumer_key          opennate
     *  xoauth_signature_publickey  http://cyworld.natecontainer.com/public.cer
     *  oauth_signature_method      RSA-SHA1
     *  oauth_nonce                 eg: fd8dbf25145974ec6909f30286507dcd
     *  oauth_timestamp             eg: 1299655770
     *  oauth_signature             eg: MguGB4eSDSkBLIowF9aXWPlmH2tg53j+9sGapYivA0uyhkF/OP1//YElo0aZLGwUbsTZeE5a/bg5SAGakSxFnlZqmnWBxAWHcLj53fySh2EEmKjQ+FsSZE7fo+CCEiY4ViQ0YT/YK2ITmVXMeL6MKzYaqANRspoEerwxPW8nmmE=
     *
     */
    protected function _checkSignature()
    {

    	try {
	        require_once 'osapi/external/OAuth.php';
	        //Build a request object from the current request
	        unset($_GET['index/index']);
	        $request = OAuthRequest::from_request(null, null, array_merge($_GET, $_POST));
	        //$xoauth_signature_publickey = $request->get_parameter('xoauth_signature_publickey');
			require_once 'osapi/external/NateSignatureMethod.php';
	        $signature_method = new NateSignatureMethod();
	        //Check the request signature
	        $signature = rawurldecode($request->get_parameter('oauth_signature'));
	        @$signature_valid = $signature_method->check_signature($request, null, null, $signature);
	        //$signature_valid = true;
	        if ($signature_valid) {
	            $parameters = array(
	                'app_id'    => $request->get_parameter('opensocial_app_id'),
	                'app_url'    => $request->get_parameter('opensocial_app_url'),
	                'owner_id'  => $request->get_parameter('opensocial_owner_id'),
	                'viewer_id' => $request->get_parameter('opensocial_viewer_id')
	            );
	        }
	        else {
	            $parameters = null;
	        }
	        $this->_appParam = $parameters;
    	}
    	catch (Exception $e) {
    		info_log('checkSignature-err', 'Hapyfish2_Application_NateCy');
    		info_log($e->getMessage(), 'Hapyfish2_Application_NateCy');
    		return false;
    	}
        return;
    }

	/**
     * _init()
     *
     * @return void
     */
    protected function _init()
    {
		$this->_checkSignature();
		$this->_appId = $this->_appParam['app_id'];
        $this->_puid = $this->_appParam['viewer_id'];
        $this->_newuser = false;
    }

	/**
     * run() - main mothed
     *
     * @return void
     */
    public function run()
    {
    	if ($this->_puid) {
    		$request = $this->getRequest();
			$userInfo = json_decode($request->getParam('user'), true);
			$userFriend = json_decode($request->getParam('friends'), true);
			$this->_updateInfo($userInfo, $userFriend);

			$uid = $this->_userId;
       		$puid = $this->_puid;
       		$sessionKey = $request->getParam('oauth_nonce');
       		$t = time();
        	$rnd = mt_rand(1, ECODE_NUM);
			$sig = md5($uid . $puid . $sessionKey . $t . $rnd . APP_KEY);
			$skey = $uid . '.' . $puid . '.' . base64_encode($sessionKey) . '.' . $t . '.' . $rnd . '.' . $sig;
        	$this->_hfskey = $skey;//'1052.21263792.NGNkYzdkMzA4MWUyMDc0ZDg5YTMwMDg3YzBhYTNiNGI=.1299831814.3.b22b0ae94321f04dfc99e72536606eff';
    		return true;
    	}
		else {
			return false;
		}
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
    	return $this->_newuser;
    }

    protected function _getUser($data)
    {
        $user = array();
        $user['uid'] = '' . $this->_userId;
        $user['puid'] = $data['uid'];
        $user['name'] = $data['displayName'];
        $sex = isset($data['gender']) ? $data['gender'] : '';
        if ($sex == 'male') {
            $gender = 1;
        } else if ($sex == 'female') {
            $gender = 0;
        } else {
            $gender = -1;
        }
        $user['gender'] = $gender;
        $user['figureurl'] = $data['thumbnailUrl'];
        return $user;
    }

    protected function _updateInfo($userData, $fids)
    {
    	//$userData = $this->_rest->getUser();

    	if (!$userData) {
    		throw new Hapyfish2_Application_Exception('get user info error' . $this->_puid);
    	}

    	$puid = $this->_puid;
    	if ($puid != $userData['uid']) {
    		throw new Hapyfish2_Application_Exception('platform uid error' . $this->_puid);
    	}

    	try {
    		$uidInfo = Hapyfish2_Platform_Cache_UidMap::getUser($puid);
    		//first coming
    		if (!$uidInfo) {
    			$uidInfo = Hapyfish2_Platform_Cache_UidMap::newUser($puid);
    			if (!$uidInfo) {
    				throw new Hapyfish2_Application_Exception('generate user id error' . $this->_puid);
    			}
    			$this->_newuser = true;
    		}
    	} catch (Exception $e) {
    		throw new Hapyfish2_Application_Exception('get user id error' . $this->_puid);
    	}

        $uid = $uidInfo['uid'];
        if (!$uid) {
        	throw new Hapyfish2_Application_Exception('user id error' . $this->_puid);
        }

        $this->_userId = $uid;

        $user = $this->_getUser($userData);
        if ($this->_newuser) {
        	Hapyfish2_Platform_Bll_User::addUser($user);
        	//add log
        	$logger = Hapyfish2_Util_Log::getInstance();
        	$logger->report('100', array($uid, $puid, $user['gender']));
        } else {
        	Hapyfish2_Platform_Bll_User::updateUser($uid, $user, true);
        }

        //$fids = $this->_rest->getFriendIds();
        if ($fids !== null) {
        	//这块可能会出现效率问题，fids很多的时候，memcacehd get次数会很多
        	//优化方案，先根据fid切分到相应的memcached组，用getMulti方法，减少次数
        	$fids = Hapyfish2_Platform_Bll_User::getUids($fids);
			if ($this->_newuser) {
        		Hapyfish2_Platform_Bll_Friend::addFriend($uid, $fids);
        	} else {
        		Hapyfish2_Platform_Bll_Friend::updateFriend($uid, $fids);
        		//Hapyfish2_Platform_Bll_Friend::addFriend($uid, $fids);
        	}
        }
    }

    public function getSKey()
    {
    	return $this->_hfskey;
    }

}