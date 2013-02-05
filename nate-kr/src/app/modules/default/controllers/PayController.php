<?php

class PayController extends Zend_Controller_Action
{
    protected $uid;
	protected $_skey;

    public function init()
    {
        $info = $this->vailid();
        if (!$info) {
            echo '<div>Session timeout,please reload the page.</div>';
            exit;
        }

        $this->info = $info;
        $this->uid = $info['uid'];

        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = STATIC_HOST;
        $this->view->hostUrl = HOST;
        $this->view->appId = APP_ID;
        $this->view->appKey = APP_KEY;
        $this->view->uid = $info['uid'];
        $this->view->platformUid = $info['puid'];
        $this->view->hf_skey = $this->_skey;
    }

	protected function vailid()
    {
    	$skey = $this->_request->getParam('hf_skey');
    	$this->_skey = $skey;

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

    public function topAction()
    {
        echo 'permission deny';
        exit;
    	$uid = $this->uid;
		$user = Hapyfish2_Platform_Bll_User::getUser($uid);
		$user['face'] = $user['figureurl'];
		$user['gold'] = Hapyfish2_Island_HFC_User::getUserGold($uid);

	    $notice = Hapyfish2_Island_Cache_BasicInfo::getNoticeList();
        if (empty($notice)) {
        	$this->view->showNotice = false;
        } else {
        	$this->view->showNotice = true;
			$this->view->mainNotice = $notice['main'];
			$this->view->subNotice = $notice['sub'];
			$this->view->picNotice = $notice['pic'];
        }

		$this->view->user = $user;
		$this->render();
    }

	public function loadpayAction()
	{
		$uid = $this->uid;
		$type = (int)$this->_request->getParam('type');
		if ($type>4 || $type<1) {
			$type = 1;
		}
		$newOrderId = Hapyfish2_Island_Bll_Payment::createOrderId($uid, $type);
		$sig = md5($newOrderId.APP_SECRET);
		$rst = array('status' => 1, 'pkey' => $newOrderId . '-' . $sig);
		echo json_encode($rst);
		exit;
	}
}