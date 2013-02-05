<?php

require_once(CONFIG_DIR . '/language.php');

class GiftController extends Zend_Controller_Action
{
    protected $uid;

    public function init()
    {
        $info = $this->vailid();
        if (!$info) {
            echo '<script type="text/javascript">HFApp.reload();</script>';
            exit;
        }

        $this->info = $info;
        $this->uid = $info['uid'];
        $data = array('uid' => $info['uid'], 'puid' => $info['puid'], 'session_key' => $info['session_key']);
        $context = Hapyfish2_Util_Context::getDefaultInstance();
        $context->setData($data);

        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = STATIC_HOST;
        $this->view->hostUrl = HOST;
        $this->view->appId = APP_ID;
        $this->view->appKey = APP_KEY;
        $this->view->uid = $info['uid'];
        $this->view->platformUid = $info['puid'];
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

    public function topAction()
    {
    	$cid = $this->_request->getParam("tid", 1);

		$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($this->uid);
		$giftList = Hapyfish2_Island_Cache_BasicInfo::getGiftList();
		$giftSendCountInfo = Hapyfish2_Island_Cache_Counter::getSendGiftCount($this->uid);

        //get user gold
        $balanceInfo = Hapyfish2_Island_Bll_Gold::get($this->uid, true);
        if (!$balanceInfo) {
	        $result['content'] = 'serverWord_1002';
	        return array('resultVo' => $result);
	    }
		$userGold = $balanceInfo['balance'];

		$giftFree = array ( );
		$giftCharge = array ( );
		foreach ( $giftList as $gift ) {
			switch ( $gift['is_free'] ) {
				case 0 :
					$giftFree[] = $gift;
				break;
				case 1 :
					$giftCharge[] = $gift;
				break;
			}
		}

		$gift_free = array_chunk( $giftFree, 8 );
		$gift_charge = array_chunk( $giftCharge, 8 );
		$this->view->giftFrees = $gift_free;
		$this->view->freeCount = count( $gift_free );
		$giftCharges = array_chunk( $giftCharge, 8 );
		$this->view->giftCharges = $giftCharges;
		$this->view->chargeCount = count ( $gift_charge );
		$this->view->userLevel = $userLevelInfo['level'];
		$this->view->userGold = $userGold;
		$this->view->cid = $cid;
		$this->view->count = $giftSendCountInfo['count'];

        $this->render();
    }

    public function friendsAction()
    {
    	$gid = $this->_request->getParam('gid');
    	$tid = $this->_request->getParam('tid');

        //get user gold
        $balanceInfo = Hapyfish2_Island_Bll_Gold::get($this->uid, true);
        if (!$balanceInfo) {
	        $result['content'] = 'serverWord_1002';
	        return array('resultVo' => $result);
	    }
		$userGold = $balanceInfo['balance'];

		$uid = $this->uid;

		if (empty($gid)) {
			echo '-100';
			exit;
		}

		$gift = Hapyfish2_Island_Cache_BasicInfo::getGiftInfo($gid);
		if (!$gift) {
			echo '-100';
			exit;
		}

		$giftSendCountInfo = Hapyfish2_Island_Cache_Counter::getSendGiftCount($uid);
		$count = $giftSendCountInfo['count'];

		if ( $tid == 1 ) {
			if($count <= 0) {
				$this->_redirect($this->view->baseUrl . '/gift/err/t/1');
				exit();
			}
		}

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
		if($tid == 1) {
			$this->view->giftSendNum = $giftSendCountInfo['count'];
		} else if($tid == 2) {
			$goldSendCount = floor($userGold / 5);
			$this->view->giftSendNum = $goldSendCount;
		} else {
			$goldSendCount = floor($userGold / $gid);
			$this->view->giftSendNum = $goldSendCount;
		}

		$this->view->gift = $gift;
		$this->view->friendList = json_encode($friendList);
		$this->view->friendNum = $friendNum;
		$this->view->pageSize = $pageSize;
		$this->view->pageNum = ceil($friendNum / $pageSize);
		$this->view->tid = $tid;

    }

    public function sendAction()
    {
    	$gid = $this->_request->getParam('gid');
        $fids = $this->_request->getParam('fids');

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

		$giftInfo = array();
		if($gid) {
	    	$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getGiftInfo($gid);
			if (!$giftInfo) {
	    		$result['errno'] = 1001;
				echo json_encode($result);
				exit;
			}
		}

		$fids = split(',', $fids);
        if (empty($fids)) {
    		$result['errno'] = 1001;
			echo json_encode($result);
			exit;
		}

		$uid = $this->uid;
		$gift_need_money = 0;

        $friendIds = Hapyfish2_Platform_Bll_Friend::getFriendIds($uid);
        $tmp = array_flip($friendIds);

        foreach ($fids as $fid) {
        	if (!isset($tmp[$fid])) {
    			$result['errno'] = 1003;
				echo json_encode($result);
				exit;
        	}
        }

		$count = count($fids);
		$giftSendCountInfo = Hapyfish2_Island_Cache_Counter::getSendGiftCount($uid);

		//get user gold
        $balanceInfo = Hapyfish2_Island_Bll_Gold::get($uid, true);
        if (!$balanceInfo) {
	        $result['content'] = 'serverWord_1002';
	        return array('resultVo' => $result);
	    }
		$userGold = $balanceInfo['balance'];

		if(!empty($giftInfo)) {
			if($giftInfo['is_free'] == 0) {
				if ($giftSendCountInfo['count'] > 0 || $count < $giftSendCountInfo['count']) {
					$num = Hapyfish2_Island_Bll_GiftPackage::sendGift($gid, $uid, $fids, $giftSendCountInfo, 1);

					if($num) {
						//add gift log
						Hapyfish2_Island_Bll_GiftPackage::insertGiftLog($uid, $gid, $fids, 1);
					}
				} else {
					if ($giftSendCountInfo['count'] <= 0 || $count > $giftSendCountInfo['count']) {
						$result['errno'] = 1002;
						echo json_encode($result);
						exit;
					}
				}
			} else {
				$p_nums = count($fids);
				$all_need_money = (int)$p_nums * (int)$giftInfo['price'];

				//如果钱不够，就跳转到充值页面
				if($userGold < $all_need_money){
					$this->_redirect($this->view->baseUrl . '/gift/err/t/2');
					exit ();
				}

				$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);

				$num = Hapyfish2_Island_Bll_GiftPackage::sendGift($gid, $uid, $fids, $giftSendCountInfo, 2);

				if($num) {
					$goldInfo = array('uid' => $uid,
		        						'cost' => $all_need_money,
		        						'summary' => LANG_PLATFORM_INDEX_TXT_03 . $giftInfo['name'],
		        						'user_level' => $userLevelInfo['level'],
		        						'cid' => $gid,
		        						'num' => $num);
		        	$ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
				}

				Hapyfish2_Island_Bll_GiftPackage::insertGiftLog($uid, $gid, $fids, 2);
			}
		}

		$result = array('errno' => 0, 'count' => $count, 'num' => $num);
        echo json_encode($result);
    	exit;
    }

	function getAction()
	{
		$uid = $this->uid;
		$pageIndex = $this->_request->getParam('page', 1);
        $pageSize = 10;

		$my_gift_list = Hapyfish2_Island_Bll_GiftPackage::getGiftLog($uid);

		$num = count($my_gift_list);
        $pages = ceil($num/$pageSize);
        $new_list = array_chunk($my_gift_list, $pageSize);
        $pageIndexNum = $pageIndex - 1;

        $this->view->gift_list = $new_list[$pageIndexNum];
		$this->view->pages = $pages;
    }

	function postAction()
	{
		$uid = $this->uid;
		$pageIndex = $this->_request->getParam('page', 1);
		$pageSize = 10;

		$my_gift_list = Hapyfish2_Island_Bll_GiftPackage::postGiftLog($uid);

		$num = count($my_gift_list);
		$pages = ceil($num / $pageSize);
		$new_list = array_chunk($my_gift_list, $pageSize);
		$pageIndexNum = $pageIndex - 1;

		$this->view->gift_list = $new_list[$pageIndexNum];
		$this->view->pages = $pages;
    }

	function errAction()
	{
		$uid = $this->uid;
		$t = $this->_request->getParam("t");

		$balanceInfo = Hapyfish2_Island_Bll_Gold::get($uid, true);
		if (!$balanceInfo) {
			$result['content'] = 'serverWord_1002';
			return $result;
		}
		$userGold = $balanceInfo['balance'];

        $url1 = 'http://apps.facebook.com/' . APP_NAME . '/gift/top/t/2';
		$url2 = 'http://apps.facebook.com/' . APP_NAME . '/pay/top';

		switch ($t){
			case 1:
			    $msg = "<p>" . LANG_PLATFORM_INDEX_TXT_04 . "<br/></p><p>" . LANG_PLATFORM_INDEX_TXT_05 . "<a href='" . $url1 . "'><b>" . LANG_PLATFORM_INDEX_TXT_06 ."</b></a>" . LANG_PLATFORM_INDEX_TXT_07 . "</p>";
			break;
			case 2:
				$msg = "<p>" . LANG_PLATFORM_INDEX_TXT_08 . "<a href='"  . $url2 . "'><b>" . LANG_PLATFORM_INDEX_TXT_09 . "</b></a></p>";
			break;
		}

		$this->view->msg = $msg;
	}

 }
