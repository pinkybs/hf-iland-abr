<?php

class EventController extends Zend_Controller_Action
{
    protected $uid;

    protected $info;

    /**
     * initialize basic data
     * @return void
     */
    public function init()
    {
    	$info = $this->vailid();
        if (!$info) {
        	$result = array('status' => '-1', 'content' => 'serverWord_101');
			$this->echoResult($result);
        }

        $this->info = $info;
        $this->uid = $info['uid'];
        $data = array('uid' => $info['uid'], 'puid' => $info['puid'], 'session_key' => $info['session_key']);
        $context = Hapyfish2_Util_Context::getDefaultInstance();
        $context->setData($data);

    	$controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
    }

	protected function vailid()
    {
    	//$skey = $_COOKIE['hf_skey'];
    	$skey = $this->_request->getParam('hf_skey');
    	$skey = ('null' == $skey) ? $_COOKIE['hf_skey'] : $skey;
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
	        $vsig = md5($uid . $puid . $session_key . $t . APP_KEY);
	        if ($sig != $vsig) {
	        	return false;
	        }
        } else if ($count == 6) {
        	$rnd = $tmp[4];
        	$sig = $tmp[5];
        	$vsig = md5($uid . $puid . $session_key . $t . $rnd . APP_KEY);
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

    protected function checkEcode($params = array())
    {
    	if ($this->info['rnd'] > 0) {
    		$rnd = $this->info['rnd'];
    		$uid = $this->uid;
    		$ts = $this->_request->getParam('tss');
    		$authid = $this->_request->getParam('authid');
    		$ok = true;
    		if (empty($authid) || empty($ts)) {
    			$ok = false;
    		}
    		if ($ok) {
    			$ok = Hapyfish2_Island_Bll_Ecode::check($rnd, $uid, $ts, $authid, $params);
    		}
    		if (!$ok) {
    			//Hapyfish2_Island_Bll_Block::add($uid, 1, 2);
    			info_log($uid, 'ecode-err');
	        	$result = array('status' => '-1', 'content' => 'serverWord_101');
	        	setcookie('hf_skey', '' , 0, '/', '.island.qzoneapp.com');
	        	$this->echoResult($result);
    		}
    	}
    }

    protected function echoResult($data)
    {
    	header("Cache-Control: no-store, no-cache, must-revalidate");
    	echo json_encode($data);
    	exit;
    }

    public function getactivedayAction()
    {
    	$uid = $this->uid;
    	$result = array('status' => -1, 'content' => 'serverWord_110');
    	$loginInfo = Hapyfish2_Island_HFC_User::getUserLoginInfo($uid);
    	if (!$loginInfo) {
			$this->echoResult($result);
		}

		$this->echoResult(array('day' => $loginInfo['active_login_count'], 'result' => array('status' => 1)));
    }

    public function active5dayAction()
    {
    	$uid = $this->uid;
    	$key = 'evlock:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);

	    //get lock
		$ok = $lock->lock($key);
    	if (!$ok) {
			$result = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult($result);
		}

    	$result = array('status' => -1, 'content' => 'serverWord_110');
    	$loginInfo = Hapyfish2_Island_HFC_User::getUserLoginInfo($uid);
        if (!$loginInfo) {
			$this->echoResult($result);
		}

		if ($loginInfo['active_login_count'] < 5) {
			$this->echoResult($result);
		}

    	$isGained = Hapyfish2_Island_Event_Bll_Active5Day::isGained($uid);
		if (!$isGained) {
			$result = Hapyfish2_Island_Event_Bll_Active5Day::gain($uid);
		} else {
			$result['content'] = 'serverWord_151';
		}

        //release lock
        $lock->unlock($key);

		$this->echoResult($result);
    }

    public function getinviteflowstateAction()
    {
    	$uid = $this->uid;
    	$data = Hapyfish2_Island_Event_Bll_InviteFlow::getState($uid);
    	$this->echoResult($data);
    }

    public function inviteawardAction()
    {
    	$uid = $this->uid;
    	$key = 'evlock:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);

	    //get lock
		$ok = $lock->lock($key);
        if (!$ok) {
			$resultVo = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $resultVo));
		}

		$result = array('result' => array('status' => -1, 'content' => 'serverWord_110'));

		$data = Hapyfish2_Island_Event_Bll_InviteFlow::getState($uid);
		$step = $data['step'];
		$friendCount = count($data['friendsList']);

		if ($step == 1) {
			if ($friendCount < 4) {
				 $this->echoResult($result);
			}
		} else if ($step == 2) {
			if ($friendCount < 4) {
				 $this->echoResult($result);
			}
		} else  if ($step == 3) {
			if ($friendCount < 4) {
				 $this->echoResult($result);
			}
		} else  if ($step == 4) {
			if ($friendCount < 4) {
				 $this->echoResult($result);
			}
		} else {
			$this->echoResult($result);
		}

		$result = Hapyfish2_Island_Event_Bll_InviteFlow::gain($uid, $step);

        //release lock
        $lock->unlock($key);
        $this->echoResult($result);
    }

 }