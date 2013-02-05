<?php

require_once(CONFIG_DIR . '/language.php');

class Hapyfish2_Island_Event_Bll_InviteFlow
{
    public static function getState($uid)
    {
    	$result = array('result' => array('status' => 1));
    	$step = self::getInviteStep($uid);
		$result['step'] = $step + 1;
		$result['friendsList'] = self::getInviteFriendList($uid, $result['step']);

		return $result;
    }

    public static function getInviteStep($uid)
    {

		$key = 'i:u:e:invf_gold2:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);

        $data = $cache->get($key);

		if ($data === false) {
			try {
    			$dalInviteFlow = Hapyfish2_Island_Event_Dal_InviteFlow::getDefaultInstance();
    			$data = $dalInviteFlow->getStep($uid);
    			$cache->set($key, $data);
				return $data;
			} catch (Exception $e) {
				return -1;
			}
		} else {
			return $data;
		}
    }

    public static function getInviteFriendList($uid, $step)
    {
    	$friendList = array();
    	if ($step < 0 || $step > 4) {
    		return $friendList;
    	}

    	$inviteList = Hapyfish2_Island_Bll_InviteLog::getAllOfFlow($uid);
    	if (!$inviteList) {
    		return $friendList;
    	}

    	$count = count($inviteList);
    	if ($step == 1) {
    		$start = 0;
    		$end = 4;
    		if ($count < $end) {
    			$end = $count;
    		}
    	} else if ($step == 2) {
    		$start = 4;
    		$end = 7;
    		if ($count < $end) {
    			$end = $count;
    		}
    	} else if ($step == 3) {
    	    $start = 7;
    		$end = 9;
    		if ($count < $end) {
    			$end = $count;
    		}
    	} else if ($step == 4) {
			$start = 9;
    		$end = 10;
    		if ($count < $end) {
    			$end = $count;
    		}
    	}

		for($i = $start; $i < $end; $i++) {
    		$fid = $inviteList[$i]['fid'];
    		$info = Hapyfish2_Platform_Bll_User::getUser($fid);
    		if ($info['figureurl'] == 'http://avatars.nasza-klasa.pl/img/avatar/brakzdjecia') {
                $info['figureurl'] = STATIC_HOST.'/apps/island/images/common/nopic.gif';
            }

    		$friendList[] = array(
    			'name' => $info['name'],
    			'face' => $info['figureurl']
    		);
		}

		return $friendList;
    }

    public static function isGaind($uid, $step)
    {
    	$nowStep = self::getInviteStep($uid);
    	if ($step < $nowStep) {
    		return true;
    	} else {
    		return false;
    	}
    }

    public static function gain($uid, $step, $time = null)
    {
    	$result = array('result' => array('status' => '-1', 'content' => 'serverWord_110'));

    	if ($step < 0 || $step > 4) {
    		return  $result;
    	}

    	$compensation = new Hapyfish2_Island_Bll_Compensation();

    	$goldnum = 0;
    	if ($step == 1) {
			//邀请4名新玩家，奖励眼镜母鸡
			$compensation->setItem(37521, 1);
			//2011-03-09 改成送宝石
			//$goldnum = 50;
			//$compensation->setGold($goldnum, 4);
			$title = LANG_PLATFORM_EVENT_TXT_19;
    	} else if ($step == 2) {
			//邀请3名新玩家，奖励人气王门牌
			$compensation->setItem(75421, 1);
			//2011-03-09 改成送宝石
			//$goldnum = 20;
			//$compensation->setGold($goldnum, 4);
			$title = LANG_PLATFORM_EVENT_TXT_20;
    	} else if ($step == 3) {
    		//邀请2名新玩家，奖励积木房子
    		$compensation->setItem(44631, 1);
    		//2011-03-09 改成送宝石
    		//$goldnum = 20;
			//$compensation->setGold($goldnum, 4);
    		$title = LANG_PLATFORM_EVENT_TXT_21;
    	} else if ($step == 4) {
    		//邀请1名新玩家，奖励爱之海
    		$compensation->setItem(22413, 1);
    		$title = LANG_PLATFORM_EVENT_TXT_22;
    	}

		$compensation->setFeedTitle($title);
		$ok = $compensation->sendOne($uid, '');

		if ($ok) {
			if (!$time) {
				$time = time();
			}
			try {
				$key = 'i:u:e:invf_gold2:' . $uid;
        		$cache = Hapyfish2_Cache_Factory::getMC($uid);
        		$cache->set($key, $step);

				$dal = Hapyfish2_Island_Event_Dal_InviteFlow::getDefaultInstance();
				$info = array('uid' => $uid, 'step' => $step, 'create_time' => $time);
				$dal->insert($uid, $info);
			} catch (Exception $e) {
				info_log($uid, 'Event_InviteFlow');
			}

			$result = array('result' => array('status' => 1,'goldChange' => $goldnum));
		}

		return $result;
    }
}