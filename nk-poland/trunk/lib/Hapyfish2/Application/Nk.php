<?php

require_once 'Hapyfish2/Application/Abstract.php';

class Hapyfish2_Application_Nk extends Hapyfish2_Application_Abstract
{

    protected $_puid;

    protected $_session_key;

    protected $_hfskey;

    public $newuser;

    public $params;

    public $hf_params;

    /**
     * Singleton instance, if null create an new one instance.
     *
     * @param Zend_Controller_Action $actionController
     * @return Hapyfish2_Application_Nk
     */
    public static function newInstance(Zend_Controller_Action $actionController)
    {
        if (null === self::$_instance) {
            self::$_instance = new Hapyfish2_Application_Nk($actionController);
        }

        return self::$_instance;
    }

    public function get_params()
    {
        $request = $this->getRequest();
		$this->params = array(
			'sessionkey' => $request->getParam('sessionkey'),
			'uid' => $request->getParam('userid'),
			'sig' => $request->getParam('sessionid'),
			'invitor' => $request->getParam('invitor'),
			'user' => $request->getParam('user'),
			'friends' => $request->getParam('friends')
		);

		return $this->params;
    }

    public function get_hf_params($params, $namespace = 'hf')
    {
        if (empty($params)) {
            return array();
        }

        $prefix = $namespace . '_';
        $prefix_len = strlen($prefix);
        $hf_params = array();
        foreach ($params as $name => $val) {
            if (strpos($name, $prefix) === 0) {
                $hf_params[$name] = $val;
            }
        }

        return $hf_params;
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

	/**
     * _init()
     *
     * @return void
     */
    protected function _init()
    {
    	$data = $this->get_params();
        if (!$data || ($data['sig'] != md5($data['uid'].$data['sessionkey'].APP_SECRET)) || $data['uid'] != $data['user']['uid']) {
            echo 'data invalidate';
            exit;
        }

        $app_id = APP_ID;

		$puid = $data["uid"];
		$sessionKey = $data["sig"];

        //OK
        $this->_appId = APP_ID;
        $this->_appName = APP_NAME;
        $this->_puid = $puid;
        $this->_session_key = $sessionKey;
        $this->hf_params = $this->get_hf_params($_POST);
        $this->newuser = false;
    }

    protected function _getUser($data)
    {
        $user = array();
        $user['uid'] = '' . $this->_userId;
        $user['puid'] = $data['uid'];
        $user['name'] = $data['name'];
        $user['figureurl'] = $data['figureurl'];
        $sex = isset($data['gender']) ? $data['gender'] : '';
        if ($sex == 'male') {
            $gender = 1;
        } else if ($sex == 'female') {
            $gender = 0;
        } else {
            $gender = -1;
        }
        $user['gender'] = $gender;

        return $user;
    }

    protected function _updateInfo()
    {
    	$userData = $this->params['user'];

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
    			$this->newuser = true;
    		}
    	} catch (Exception $e) {
    	    info_log($e->getMessage(), 'pt-err');
    		throw new Hapyfish2_Application_Exception('get user id error' . $this->_puid);
    	}

        $uid = $uidInfo['uid'];
        if (!$uid) {
        	throw new Hapyfish2_Application_Exception('user id error' . $this->_puid);
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

        $fids = $this->params['friends'];
        if ($fids != null) {
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

    public function getSKey()
    {
    	return $this->_hfskey;
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
        $domain = str_replace('http://', '.', HOST);
        setcookie('hf_skey', $skey , 0, '/', $domain);
    }

}