<?php

class InviteController extends Zend_Controller_Action
{
    protected $uid;
    protected $_skey;

    public function init()
    {
        $info = $this->vailid();
        if (! $info) {
            echo '<div>Session timeout,please reload the page.</div>';
            exit();
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
        if (! $skey) {
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
        $rnd = - 1;
        if ($count == 5) {
            $sig = $tmp[4];
            $vsig = md5($uid . $puid . $session_key . $t . APP_KEY);
            if ($sig != $vsig) {
                return false;
            }
        }
        else if ($count == 6) {
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
        return array('uid' => $uid, 'puid' => $puid, 'session_key' => $session_key, 't' => $t,
        'rnd' => $rnd);
    }

    public function topAction()
    {
        $now = time();
        //截至时间 2011-01-24 00:00:00
        if ($now < 1295798400) {
            $this->view->epath = 'e20110123/';
        }
        else {
            $this->view->epath = '';
        }
        $notice = Hapyfish2_Island_Cache_BasicInfo::getNoticeList();
        if (empty($notice)) {
            $this->view->showNotice = false;
        }
        else {
            $this->view->showNotice = true;
            $this->view->mainNotice = $notice['main'];
            $this->view->subNotice = $notice['sub'];
            $this->view->picNotice = $notice['pic'];
        }
        $this->render();
    }

    public function friendsAction()
    {
		$uid = $this->uid;
		//get all friends
		$fids = $this->_request->getParam('fids');
		$aryFid = json_decode($fids, true);

		$friendList = false;
        foreach ($aryFid as $fdata) {
            $rowData = explode(',', $fdata);
            $pfid = $rowData[0];
            $inGame = Hapyfish2_Platform_Cache_UidMap::getUser($pfid);
            //if is in game friends
            if (empty($inGame)) {
                $friendList[] = array('uid' => $pfid, 'name' => $rowData[2], 'face' => $rowData[1]);
            }
        }
        if ($friendList) {
            //$friendList = array_merge($friendList,$friendList,$friendList);
            $friendNum = count($friendList);
        }
        else {
            $friendList = '[]';
		    $friendNum = 0;
        }

        $pageSize = 15;
		$this->view->friendList = json_encode($friendList);
		$this->view->friendNum = $friendNum;
		$this->view->pageSize = $pageSize;
		$this->view->pageNum = ceil($friendNum/$pageSize);

		$user = Hapyfish2_Platform_Bll_User::getUser($uid);
		$user['face'] = $user['figureurl'];
		$this->view->user = $user;

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
        $uid = $this->uid;
        $puids = $this->_request->getParam('sendIds');
        $aryPuid = explode(',', $puids);
        if (empty($aryPuid)) {
            echo 'Failed';
            exit();
        }

        try {
            //invite send logs
            $dalInvite = Hapyfish2_Island_Event_Dal_InviteSend::getDefaultInstance();
            $now = time();
            foreach ($aryPuid as $puid) {
                $rowInvite = $dalInvite->getInviteSend($puid, $uid);
                if (empty($rowInvite)) {
                    $dalInvite->insert($puid, array('invite_puid' => $puid, 'uid' => $uid, 'create_time' => $now));
                }
            }
        }
        catch (Exception $e) {
            info_log($e->getMessage(), 'send-invite-err');
        }

        echo '<a href="javascript:void(0);" onclick="HFApp.invite();" target="_top">Back&gt;&gt;</a>';
        exit();
    }
}
