<?php

require_once(CONFIG_DIR . '/language.php');

class Hapyfish2_Island_Event_Bll_StarfishSale
{
    public static function getSaleList()
    {
        $key = 'starfishOnSale:';
        $cache = self::getBasicMC();
        $data = $cache->get($key);
		if ($data === false) {
			try {
    			$dal = Hapyfish2_Island_Event_Dal_StarfishSale::getDefaultInstance();
    			$data = $dal->getSaleList();
    			$cache->set($key, $data);
				return $data;
			} catch (Exception $e) {
				return -1;
			}
		} else {
			return $data;
		}
    }

    public static function getInviteCount($uid, $start)
    {
        $invitecount = 0;
        $dal = Hapyfish2_Island_Event_Dal_StarfishSale::getDefaultInstance();
        $count = $dal->getInviteCount($uid, $start);
        if ($count) {
            $invitecount = $count;
        }
        return $invitecount;
    }

    public static function Exchange($uid, $cid)
    {
        $result['result'] = array('status' => 1, 'content' => '');
        $userStarFish = Hapyfish2_Island_HFC_User::getUserStarFish($uid);
        $detail = self::getDetail($cid);
        if (empty($detail)) {
            $result['result'] = array('status' => -1, 'content' => LANG_PLATFORM_EVENT_TXT_15);
            return $result;
        }
        if($userStarFish < $detail['price']){
        	$result['result'] = array('status' => -1, 'content' => LANG_PLATFORM_EVENT_TXT_25);
            return $result;
     	}
		$now = time();
		$compensation = new Hapyfish2_Island_Bll_Compensation();
		$compensation->setItem($cid, $detail['number']);
		$ok = $compensation->sendOne($uid, LANG_PLATFORM_EVENT_TXT_26 . LANG_PLATFORM_EVENT_TXT_27);
		if($ok){
			$cinfo = Hapyfish2_Island_Cache_BasicInfo::getDetailInfo($cid);
		    $summary = LANG_PLATFORM_EVENT_TXT_27 . $cinfo['name'] . LANG_PLATFORM_EVENT_TXT_28 .$detail['price'] . LANG_PLATFORM_BASE_TXT_16;
			$ok2 = Hapyfish2_Island_Bll_StarFish::consume($uid, $detail['price'], $summary, $now);
			$result['result']['feed'] = Hapyfish2_Island_Bll_Activity::send('Starfish_word', $uid);
     	}
        return $result;

    }

    public static function getDetail($cid)
    {
    	$key = 'starfishOnSale:'.$cid;
        $cache = self::getBasicMC();
        $data = $cache->get($key);
		if ($data === false) {
			try {
    			$dal = Hapyfish2_Island_Event_Dal_StarfishSale::getDefaultInstance();
    			$data = $dal->getdetail($cid);
    			$cache->set($key, $data);
				return $data;
			} catch (Exception $e) {
				return -1;
			}
		} else {
			return $data;
		}
    }

    public static function getBasicMC()
    {
         $key = 'mc_0';
		 return Hapyfish2_Cache_Factory::getBasicMC($key);
    }

    public static function roseToStarfish($dbIndex)
    {
    	try {
    		$dal = Hapyfish2_Island_Event_Dal_StarfishSale::getDefaultInstance();
    		$ulist = $dal->getRose($dbIndex);
    	} catch (Exception $e) {
    	}

		if($ulist) {
			$time = time();
			$logfile = LOG_DIR . '/starfish-' . date('Ymd', $time) . '.log';

			foreach($ulist as $key => $value) {
				if ($value['rose'] != 0 ) {
					$starfish = round(($value['rose']/10));
				} else {
					$starfish = 0;
				}
				if ($starfish > 0) {
					Hapyfish2_Island_Bll_StarFish::add($value['uid'], $starfish, $time);
					$msg = $value['uid'] . "\t" . $starfish . "\n";
					file_put_contents($logfile, $msg, FILE_APPEND);
				}
			}
		}
    }
}