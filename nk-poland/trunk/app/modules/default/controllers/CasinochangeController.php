<?php

/**
 * island casino change controller
 *
 * @copyright  Copyright (c) 2008 Happyfish Inc.
 * @create      2011/05/10   Nick
 */
class CasinochangeController extends Zend_Controller_Action
{
    protected $uid;

	public function init()
    {
        $info = $this->vailid();
        if (!$info) {
            echo '<html><body><script type="text/javascript">window.top.location="http://i.taobao.com/apps/show.htm?appkey=12029234";</script></body></html>';
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

        $notice = Hapyfish2_Island_Cache_BasicInfo::getNoticeList();
        if (empty($notice)) {
        	$this->view->showNotice = false;
        } else {
        	$this->view->showNotice = true;
			$this->view->mainNotice = $notice['main'];
			$this->view->subNotice = $notice['sub'];
			$this->view->picNotice = $notice['pic'];
        }
    }

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

    public function indexAction()
    {
        $uid = $this->uid;
        $userInfo = array('uid' => $uid);

        $userInfo['point'] = Hapyfish2_Island_Event_Bll_Casino::getUserPoint($uid);

        $info = Hapyfish2_Platform_Bll_User::getUser($uid);
        $userInfo['name'] = $info['name'];
        $userInfo['face'] = $info['figureurl'];
        $this->view->userInfo = $userInfo;

        //get point change list
        $pointChangeList = Hapyfish2_Island_Event_Bll_Casino::getPointChangeList();
        $this->view->pointChangeList = $pointChangeList;

        $this->render();
    }

    /**
     * change
     *
     */
    public function changeAction()
    {
        $controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);

    	$uid = $this->uid;
    	$point = $this->_request->getParam('point');

    	//change point
        $result = Hapyfish2_Island_Event_Bll_Casino::changeCasino($uid, $point);

        echo Zend_Json::encode($result);
    }

 }
