<?php

class Hapyfish2_Island_Bll_DailyAward
{

	public static $_mcKeyPrex = 'i:u:dlyaward:';

    /**
     * check user task info
     *
     * @param integer $uid
     * @return array
     */
    public static function getAwards($uid)
    {
    	$result = array();
    	$result['items'] = array();
    	$result['awardNum'] = 1;
    	$result['seriesDays'] = 1;
    	$loginInfo = Hapyfish2_Island_HFC_User::getUserLoginInfo($uid);
    	if (empty($loginInfo)) {
			return $result;
    	}
    	$result['seriesDays'] = $loginInfo['active_login_count'] == 0 ? 1 : $loginInfo['active_login_count'];
    	$result['awardNum'] = $result['seriesDays'] > 3 ? 3 : $result['seriesDays'];

		//判断是否是粉丝
		$result['isFan'] = false;
		if($isFan) {
			$result['isFan'] = true;
		}
		$friendDetail = Hapyfish2_Island_Bll_Fragments::getDetail($uid);
    	try {
			//is today gained
	    	$nowDate = date('Ymd');
	    	$mckey = self::$_mcKeyPrex . $uid;
	        $cache = Hapyfish2_Cache_Factory::getMC($uid);
	        $dailyReward = $cache->get($mckey);//$dailyReward['date'], $dailyReward['items'], $dailyReward['gained']

			//has gained today's awards
			if ($dailyReward && $dailyReward['items'] && $dailyReward['date'] == $nowDate && $dailyReward['gained']) {
				$result['awardNum'] = $dailyReward['awardNum'];
				return $result;
			}

			if ($dailyReward && $dailyReward['items'] && $dailyReward['date'] == $nowDate && empty($dailyReward['gained'])) {
				if(16 != count($dailyReward['items'])){
					$result['items'] = self::_getTodayAwardsItem($result['awardNum']);
				} else {
					$result['items'] = $dailyReward['items'];
				}

				$result['awardNum'] = $dailyReward['awardNum'];
				return $result;
			}

			$dailyReward = array();
	    	if($friendDetail['Isfriend']){
				$result['awardNum'] += 1;
			}
			if($isFan){
				$result['awardNum'] += 1;
			}
			$dailyReward['awardNum'] = $result['awardNum'];
	        $dailyReward['date'] = $nowDate;
	        $dailyReward['gained'] = 0;
	        $dailyReward['items'] = self::_getTodayAwardsItem($result['awardNum']);
	        $cache->set($mckey, $dailyReward);
    	}
		catch (Exception $e) {
       		info_log('getAwards:'.$uid,'Bll_DailyAward_Error');
       		info_log($e->getMessage(),'Bll_DailyAward_Error');
       		return $result;
        }

		$result['items'] = $dailyReward['items'];
    	return $result;
    }

	/**
     * gain awards
     *
     * @param integer $uid
     * @return array
     */
    public static function gainAwards($uid)
    {
    	$resultVo = array();

		$nowDate = date('Ymd');
    	$mckey = self::$_mcKeyPrex . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $dailyReward = $cache->get($mckey);//$dailyReward['date'], $dailyReward['items'], $dailyReward['gained']
        if (empty($dailyReward)) {
			$resultVo = array('status' => '-1', 'content' => 'serverWord_103');
			return array('result' => $resultVo);
        }

        require_once(CONFIG_DIR . '/language.php');
        //gain award
		if ($dailyReward && $dailyReward['items'] && $dailyReward['date'] == $nowDate && empty($dailyReward['gained'])) {
			$coinChange = 0;
			$goldChange = 0;
			$robotSendNum = 0;
			$starfishChange = 0;
			$fragmentChange = 0;
			$robot = new Hapyfish2_Island_Bll_Compensation();
			$items = array_chunk($dailyReward['items'], $dailyReward['awardNum']);
			foreach ($items[0] as $data) {
				if (1 == $data['type']) { //coin
					$coinChange += $data['num'];
				}
				else if (2 == $data['type']) { //gold
					$goldChange += $data['num'];
				}
				else if (3 == $data['type']) { //海星
					$starfishChange +=$data['num'];
				}
				else if (5 == $data['type']){
					$fragmentChange += $data['num'];
				}
				else { //item
					$robot->setItem($data['cid'], $data['num']);
					$robotSendNum ++;
				}
			}
			//coin
			if ($coinChange) {
				$robot->setCoin($coinChange);
				$robotSendNum ++;
				$resultVo['coinChange'] = $coinChange;
			}
			//gold
			if ($goldChange) {
				$robot->setGold($goldChange, 6);
				$robotSendNum ++;
				$resultVo['goldChange'] = $goldChange;
			}
			if ($starfishChange){
				$robot->setStarFish($starfishChange);
				$robotSendNum ++;
				$resultVo['starFishchange'] = $starfishChange;
			}
			if($fragmentChange){
				$robot->setFragment($fragmentChange);
				$robotSendNum ++;
			}
			//item
			if ($robotSendNum) {
				//$robot->setFeedTitle($strMsg);
				$ok = $robot->sendOne($uid, LANG_PLATFORM_BASE_TXT_11);
				if (!$ok) {
					info_log('gainAwards-sendOneErr:'.$uid,'Bll_DailyAward_Error');
				}
			}
			//gold
			if ($ok && $goldChange) {
				//TODO:: gold change log
			}

			//save to mc
			$dailyReward['gained'] = 1;
			$cache->set($mckey, $dailyReward);
			$resultVo['status'] = 1;
			return array('result' => $resultVo);
		}
		//has gained today's awards
		else {
			$resultVo = array('status' => '-1', 'content' => 'serverWord_303');
			return array('result' => $resultVo);
		}
    }

	/**
	 * get today login awards item array
	 *
	 * @param integer $cntChance
	 * @return array
	 */
	private static function _getTodayAwardsItem($cntChance=1)
    {
    	$retItem = array();
		//get rand item basic
		$aryItem = array();
		//get random item key
		$aryRandOdds = array();
		$lstItem = Hapyfish2_Island_Cache_LotteryItemOdds::getLotteryItemOddsList(1);
    	foreach ($lstItem as $data) {
			$itemKey = $data['order'];
			$aryItem[$itemKey] = $data;
			$aryRandOdds[$itemKey] = $data['item_odds'];
		}

		if (1 == $cntChance) {
			$gainKey = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey];
        	unset($aryRandOdds[$gainKey]);
		}
		else if (2 == $cntChance) {
			$gainKey = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey];
        	unset($aryRandOdds[$gainKey]);
        	$gainKey2 = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey2];
        	unset($aryRandOdds[$gainKey2]);
		}else if(3 == $cntChance) {
			$gainKey = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey];
        	unset($aryRandOdds[$gainKey]);
        	$gainKey2 = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey2];
        	unset($aryRandOdds[$gainKey2]);
        	$gainKey3 = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey3];
        	unset($aryRandOdds[$gainKey3]);
		}else if (4 == $cntChance){
			$gainKey = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey];
        	unset($aryRandOdds[$gainKey]);
        	$gainKey2 = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey2];
        	unset($aryRandOdds[$gainKey2]);
        	$gainKey3 = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey3];
        	unset($aryRandOdds[$gainKey3]);
        	$gainKey4 = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey4];
        	unset($aryRandOdds[$gainKey4]);
		} else {
			$gainKey = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey];
        	unset($aryRandOdds[$gainKey]);
        	$gainKey2 = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey2];
        	unset($aryRandOdds[$gainKey2]);
        	$gainKey3 = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey3];
        	unset($aryRandOdds[$gainKey3]);
        	$gainKey4 = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey4];
        	unset($aryRandOdds[$gainKey4]);
        	$gainKey5 = self::_randomKeyForOdds($aryRandOdds);
        	$retItem[] = $aryItem[$gainKey5];
        	unset($aryRandOdds[$gainKey5]);
		}

		//rebuild array for flash as Vo
		$aryRet = array();
		foreach ($retItem as $key=>$data) {
			$aryObj = array();
			//coin
			if (1 == $data['item_type']) {
				$aryObj['type'] = 1;
				$aryObj['cid'] = 0;
			}
			//gold
			else if (2 == $data['item_type']) {
				$aryObj['type'] = 2;
				$aryObj['cid'] = 0;
			}//star
			else if(3 == $data['item_type']){
				$aryObj['type'] = 3;
				$aryObj['cid'] = 0;
			}
			else if ( 5 == $data['item_type']){
				$aryObj['type'] = 5;
				$aryObj['cid'] = 0;
			}
			//item
			else {
				$aryObj['type'] = 4;
				$aryObj['cid'] = intval($data['item_id']);
			}
			$aryObj['num'] = intval($data['item_num']);
			$aryRet[$key] = $aryObj;
		}
		foreach($aryRandOdds as $key=>$data){
			$aryremain = array();
			if (1 == $aryItem[$key]['item_type']) {
				$aryremain['type'] = 1;
				$aryremain['cid'] = 0;
			}
			//gold
			else if (2 == $aryItem[$key]['item_type']) {
				$aryremain['type'] = 2;
				$aryremain['cid'] = 0;
			}//star
			else if(3 == $aryItem[$key]['item_type']){
				$aryremain['type'] = 3;
				$aryremain['cid'] = 0;
			}
			else if ( 5 == $aryItem[$key]['item_type']){
				$aryremain['type'] = 5;
				$aryremain['cid'] = 0;
			}
			//item
			else {
				$aryremain['type'] = 4;
				$aryremain['cid'] = intval($aryItem[$key]['item_id']);
			}
			$aryremain['num'] = intval($aryItem[$key]['item_num']);
			$aryRet[] = $aryremain;

		}
		return $aryRet;
    }

	/**
	 * generate random by key=>odds
	 *
	 * @param array $aryKeys
	 * @return integer
	 */
	private static function _randomKeyForOdds($aryKeys)
	{
		$tot = 0;
		$aryTmp = array();
		foreach ($aryKeys as $key => $odd) {
			$tot += $odd;
			$aryTmp[$key] = $tot;
		}
		$rnd = mt_rand(1,$tot);

		foreach ($aryTmp as $key=>$value) {
			if ($rnd <= $value) {
				return $key;
			}
		}
	}

}