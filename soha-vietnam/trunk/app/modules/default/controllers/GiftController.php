<?php

class GiftController extends Zend_Controller_Action
{
    protected $uid;

    public function init()
    {
        $info = $this->vailid();
        if (!$info) {
            echo '<html><body><script type="text/javascript">window.top.location="http://soap.soha.vn/apps/dtd";</script></body></html>';
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

    protected function getClientIP()
    {
        $ip = false;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode (', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = false;
            }
            for ($i = 0, $n = count($ips); $i < $n; $i++) {
                if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    public function indexAction()
    {
        $ip = $this->getClientIP();
        $uid = $this->uid;

        //report log
        $logger = Hapyfish2_Util_Log::getInstance();
        $logger->report('testIp', array('giftindex', $uid, $ip));
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

		if($tid != 3) {
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
		}
		else {
			if( !in_array($gid, array(100, 200, 500, 1000)) ) {
				echo "<div style='color:#ff0000;font-size:14px'>Thao tác sai! Hãy thử lại</a>";
				exit;
			}

			$count = 3;
		    switch ($gid) {
		    	case 100:
		    		$gift['gid'] = 100;
		    		$gift['img'] = 'bs-1.gif';
		    		$gift['name'] = '100宝石';
		    	break;
		    	case 200:
		    		$gift['gid'] = 200;
		    		$gift['img'] = 'bs-2.gif';
		    		$gift['name'] = '200宝石';
		        break;
		    	case 500:
		    		$gift['gid'] = 500;
		    		$gift['img'] = 'bs-3.gif';
		    		$gift['name'] = '500宝石';
		    	break;
		    	case 1000;
		    		$gift['gid'] = 1000;
		    	    $gift['img'] = 'bs-4.gif';
		    	    $gift['name'] = '1000宝石';
		    	break;
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
		if(!in_array($gid, array(100, 200, 500, 1000))) {
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
		        						'summary' => '赠送宝石礼物' . $giftInfo['name'],
		        						'user_level' => $userLevelInfo['level'],
		        						'cid' => $gid,
		        						'num' => $num);
		        	$ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
				}

				Hapyfish2_Island_Bll_GiftPackage::insertGiftLog($uid, $gid, $fids, 2);
			}
		} else {
			$p_nums = count($fids);
			$all_need_money = (int)$p_nums * (int)$gid;

			//如果钱不够，就跳转到充值页面
			if($userGold < $all_need_money) {
				$this->_redirect($this->view->baseUrl . '/gift/err/t/2');
				exit ();
			}

			$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);

			$num = Hapyfish2_Island_Bll_GiftPackage::sendGift($gid, $uid, $fids, $giftSendCountInfo, 3);

			if($num) {
				$goldInfo = array('uid' => $uid,
	        						'cost' => $all_need_money,
	        						'summary' => '赠送宝石' . $all_need_money,
	        						'user_level' => $userLevelInfo['level'],
	        						'cid' => '',
	        						'num' => $all_need_money);
				$ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
			}

			if($ok2) {
				Hapyfish2_Island_Bll_GiftPackage::insertGiftLog($uid, $gid, $fids, 3);
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

		switch ($t){
			case 1:
			    $msg = "<p>Bạn được tặng quà miễn phí 3 lần/ngày<br/></p><p>Hãy tặng quà cho bạn bè!</p>";
			break;
			case 2:
				$msg = "<p>Kim cương không đủ. <a href='http://soap.soha.vn/apps/dtd'><b>Nạp ngay</b></a></p>";
			break;
		}

		$this->view->msg = $msg;
	}

 }
