<?php

class GiftController extends Zend_Controller_Action
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
    	//$skey = '1052.21263792.NGNkYzdkMzA4MWUyMDc0ZDg5YTMwMDg3YzBhYTNiNGI=.1299831814.3.b22b0ae94321f04dfc99e72536606eff';

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
        $userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($this->uid);
        $giftList = Hapyfish2_Island_Cache_BasicInfo::getGiftList();
        $this->view->userLevel = $userLevelInfo['level'];
        $this->view->giftList = json_encode($giftList);
        $giftNum = count($giftList);
        $pageSize = 12;
        $this->view->giftNum = $giftNum;
        $this->view->pageSize = $pageSize;
        $this->view->pageNum = ceil($giftNum/$pageSize);

        $notice = Hapyfish2_Island_Cache_BasicInfo::getNoticeList();
        if (empty($notice)) {
        	$this->view->showNotice = false;
        } else {
        	$this->view->showNotice = true;
			$this->view->mainNotice = $notice['main'];
			$this->view->subNotice = $notice['sub'];
			$this->view->picNotice = $notice['pic'];
        }

        $this->render();
    }

    public function friendsAction()
    {
		$gid = $this->_request->getPost('gid');
		if (empty($gid)) {
			echo '-100';
			exit;
		}

		$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getGiftInfo($gid);
		if (!$giftInfo) {
			echo '-100';
			exit;
		}

		$uid = $this->uid;

		$pageSize = 15;
		$fids = Hapyfish2_Platform_Bll_Friend::getFriendIds($uid);
		if ($fids) {
			$friendList = Hapyfish2_Platform_Bll_User::getMultiUser($fids);
			$friendNum = count($friendList);
		} else {
			$friendList = '[]';
			$friendNum = 0;
		}

		$giftSendCountInfo = Hapyfish2_Island_Cache_Counter::getSendGiftCount($uid);
		$this->view->giftSendNum = $giftSendCountInfo['count'];
		$this->view->gift = $giftInfo;
		$this->view->friendList = json_encode($friendList);
		$this->view->friendNum = $friendNum;
		$this->view->pageSize = $pageSize;
		$this->view->pageNum = ceil($friendNum/$pageSize);

        $notice = Hapyfish2_Island_Cache_BasicInfo::getNoticeList();
        if (empty($notice)) {
        	$this->view->showNotice = false;
        } else {
        	$this->view->showNotice = true;
			$this->view->mainNotice = $notice['main'];
			$this->view->subNotice = $notice['sub'];
			$this->view->picNotice = $notice['pic'];
        }

    	$this->render();
    }

    public function sendAction()
    {
        $gid = $this->_request->getPost('gid');
        $fids = $this->_request->getPost('fids');
        $result = array();
    	if (empty($gid)) {
    		$result['errno'] = 1001;
			echo json_encode($result);
			exit;
		}
    	if (empty($fids)) {
    		$result['errno'] = 1001;
			echo json_encode($result);
			exit;
		}
    	$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getGiftInfo($gid);
		if (!$giftInfo) {
    		$result['errno'] = 1001;
			echo json_encode($result);
			exit;
		}
		$fids = split(',', $fids);
        if (empty($fids)) {
    		$result['errno'] = 1001;
			echo json_encode($result);
			exit;
		}
		$uid = $this->uid;

		$giftSendCountInfo = Hapyfish2_Island_Cache_Counter::getSendGiftCount($uid);
		$count = count($fids);
		if ($giftSendCountInfo['count'] <= 0 || $count > $giftSendCountInfo['count']) {
    		$result['errno'] = 1002;
			echo json_encode($result);
			exit;
		}

		$uid = $this->uid;

        $friendIds = Hapyfish2_Platform_Bll_Friend::getFriendIds($uid);
        $tmp = array_flip($friendIds);

        foreach ($fids as $fid) {
        	if (!isset($tmp[$fid])) {
    			$result['errno'] = 1003;
				echo json_encode($result);
				exit;
        	}
        }

        $num = Hapyfish2_Island_Bll_Gift::sendGift($gid, $uid, $fids, $giftSendCountInfo);

        $result = array('errno' => 0, 'count' => $count, 'num' => $num);
        echo json_encode($result);
    	exit;
    }

 }
