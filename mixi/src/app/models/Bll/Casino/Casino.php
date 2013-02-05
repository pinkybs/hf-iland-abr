<?php

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/08/23    Liz
 */
class Bll_Casino_Casino 
{
	/**
	 * get Lv init info
	 * 
	 * @param $uid
	 * @return array
	 */
	public static function getLvInit($uid)
	{
		$myLvCount = Bll_Cache_Casino_Casino::getUserLvPoint($uid);
		//$firstLvCount = Bll_Cache_Casino_Casino::getFirstLvPoint();
		$myRank = Bll_Cache_Casino_Casino::getUserLvPointRank($uid, $myLvCount);
		$avgCount = Bll_Cache_Casino_Casino::getAvgLvPoint();
		
		return array('myLvCount' => $myLvCount, 'avgCount' => $avgCount, 'myRank' => $myRank);
	}
	
	public static function getCasinoInit($uid)
	{
		$awardList = Bll_Cache_Casino_Casino::getAwardList();
		$myLvCount = Bll_Cache_Casino_Casino::getUserLvPoint($uid);
		
		return array('awards' => $awardList, 'lvCount' => $myLvCount);
	}
	
	public static function raffle($uid)
	{
		/*Bll_Cache_Casino_Casino::clearCache('newgetAwardList');
		Bll_Cache_Casino_Casino::clearCache('newgetAwardRandArray');
		Bll_Cache_Casino_Casino::clearCache('newgetAwardIdList');
		for ($i=1;$i<=20; $i++) {
			Bll_Cache_Casino_Casino::clearCache('newgetAwardInfo', $i);
		}*/
		
    	$result = array('status' => -1);
    	
    	//get user today lv count
    	/*$todayLvCount = Bll_Cache_Casino_Casino::getUserTodayLvCount($uid);
    	if ( $todayLvCount >= 6 ) {
            $result['content'] = '您今天已經參與過6次抽獎，請明天再來。';
            return array('result' => $result);
    	}*/
    	
        $dalCasino = Dal_Casino_Casino::getDefaultInstance();
        //check user casino count
        $userCasinoFreeCount = $dalCasino->getUserFreeCount($uid);
        if ( $userCasinoFreeCount < 1 ) {
        	$userCasinoBuyCount = $dalCasino->getUserBuyCount($uid);
        	if ( $userCasinoBuyCount < 1 ) {
	            $result['content'] = 'serverWord_166';
	            return array('result' => $result);
        	}
        }
        
        //get rand award
        $awardIdList = Bll_Cache_Casino_Casino::getAwardRandArray();
        $rand = array_rand($awardIdList);
        $award = $awardIdList[$rand];
        
		//get award info
		//$award = 5;
		$awardInfo = Bll_Cache_Casino_Casino::getAwardInfo($award);
		if ( $awardInfo['type'] <=5 ) {
			try {
				//insert into pay log
	            $dalPayLog = Dal_PayLog::getDefaultInstance();
            	$dalPayLog->addLog($uid, $awardInfo['gold'], 0, -103);
            	
	            //update user gold
	            $dalIslandUser = Dal_Island_User::getDefaultInstance();
	            $dalIslandUser->updateUserByField($uid, 'gold', $awardInfo['gold']);
	            
	            $result['goldChange'] = $awardInfo['gold'];
	        }
	        catch (Exception $e) {
	            err_log('[Bll_Casino_Award::order]: ' . $e->getMessage());
	        }
	        
        	$feed = 'ダイヤ'.$awardInfo['gold'].'個を獲得！';
		}
		else if ( $awardInfo['type'] <= 11 ) {
			Hapyfish_Island_Cache_User::incCoin($uid, $awardInfo['coin']);
			$result['coinChange'] = $awardInfo['coin'];
			
        	$feed = 'コイン'.$awardInfo['coin'].'を獲得！';
		}
		else if ( $awardInfo['type'] == 100 ) {
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $b1 = array('uid' => $uid, 'bid' => $awardInfo['item_cid'], 'item_type' => 21, 'status' => 0, 'buy_time' => time());
            $dalBuilding->addUserBuilding($b1);
		
            $buildingInfo = Hapyfish_Island_Cache_Shop::getBuildingById($awardInfo['item_cid']);          
            $feed = $buildingInfo['name'].'を獲得！';
		}
		else if ( $awardInfo['type'] == 200 ) {
			$dalCard = Dal_Island_Card::getDefaultInstance();
			$newCard = array(
        		'uid' => $uid,
				'cid' => $awardInfo['item_cid'],
        		'count' => 1,
				'buy_time' => time(),
				'item_type' => 41);
			//add user card
            $dalCard->addUserCard($newCard);
            
            //card info
        	$cardInfo = Hapyfish_Island_Cache_Shop::getCardById($awardInfo['item_cid']);
        	$feed = $cardInfo['name'].'を獲得！';
		}
		else if ( $awardInfo['type'] == 300 ) {
			$dalPlant = Dal_Island_Plant::getDefaultInstance();
            $newPlant = array('uid' => $uid,
                                      'bid' => $awardInfo['item_cid'],
                                      'status'=> 0,
                                      'item_id' => substr($awardInfo['item_cid'], 0, -2),
                                      'buy_time'=> time(),
                                      'item_type' => substr($awardInfo['item_cid'], -2));
            $dalPlant->insertUserPlant($newPlant);      
            $plantInfo = Hapyfish_Island_Cache_Shop::getPlantById($awardInfo['item_cid']);           
            $feed = $plantInfo['name'].'を獲得！';
		}
		
        $array = array('uid' => $uid,
        			   'award' => $award,
        			   'create_time' => time());
        $dalCasino->insertUserAward($array);

		$dalCard = Dal_Island_Card::getDefaultInstance();
        if ( $userCasinoFreeCount > 0 ) {
        	//update Card count
			$dalCard->updateCardById($uid, 55041, -1);
        }
        else {
        	//update Card count
			$dalCard->updateCardById($uid, 55141, -1);
        }
        Bll_Cache_Casino_Casino::updateUserTodayLvCount($uid, 1);
        
        $minifeed = array('uid' => $uid,
                          'template_id' => 100,
                          'actor' => $uid,
                          'target' => $uid,
                          'title' => array('feed' => $feed),
                          'type' => 6,
                          'create_time' => time());
        Hapyfish_Island_Bll_Feed::insertMiniFeed($minifeed);
	                
        $result['status'] = 1;
        return array('result' => $result, 'awardsId' => $awardInfo['id']);
	}
	
	public static function getAward($uid)
	{
    	$result = array('status' => -1);
    	$return = array();
    	
        $dalCasino = Dal_Casino_Casino::getDefaultInstance();
		$userAward = $dalCasino->getUserAward($uid);
		
		if ( empty($userAward) ) {
    		$return['result'] = $result;
			return $return;
		}
		
		$awardInfo = Bll_Cache_Casino_Casino::getAwardInfo($userAward['award']);
		
		if ( $awardInfo['type'] == 1 ) {
			Hapyfish_Island_Cache_User::incCoin($uid, $awardInfo['coin']);
			$result['coinChange'] = $awardInfo['coin'];
		}
		else if ( $awardInfo['type'] == 2 ) {
	        try {
				//insert into pay log
	            $dalPayLog = Dal_PayLog::getDefaultInstance();
            	$dalPayLog->addLog($uid, $awardInfo['gold']);
            	
	            //update user gold
	            $dalIslandUser = Dal_Island_User::getDefaultInstance();
	            $dalIslandUser->updateUserByField($uid, 'gold', $awardInfo['gold']);
	            
	            $result['goldChange'] = $awardInfo['gold'];
	        }
	        catch (Exception $e) {
	            err_log('[Bll_Casino_Award::order]: ' . $e->getMessage());
	        }
		}
		else if ( $awardInfo['type'] == 3 ) {
	        try {
	            $dalCasino->updateUserLvPoint($uid, $awardInfo['lv_point']);
	            //clear cache
	            Bll_Cache_Casino_Casino::clearCache('newgetUserLvPoint', $uid);
	            Bll_Cache_Casino_Casino::clearCache('newgetUserLvPointRank', $uid);
	            
	            //my lv count 
	            $myLvCount = Bll_Cache_Casino_Casino::getUserLvPoint($uid);
	            //the first lv count
	            $firstLvCount = Bll_Cache_Casino_Casino::getFirstLvPoint();
	            if ( $myLvCount > $firstLvCount ) {
	            	Bll_Cache_Casino_Casino::clearCache('newgetFirstLvPoint', $uid);
	            }
	            
	            //$result['lvChange'] = $awardInfo['lv_point'];
	        }
	        catch (Exception $e) {
	            err_log('[Bll_Casino_Award::order-Lv_point]: ' . $e->getMessage());
	        }
		}
		else if ( $awardInfo['type'] == 4 ) {
			$dalCard = Dal_Island_Card::getDefaultInstance();
			$newCard = array(
        		'uid' => $uid,
				'cid' => $awardInfo['item_cid'],
        		'count' => 1,
				'buy_time' => time(),
				'item_type' => 41);
			//add user card
            $dalCard->addUserCard($newCard);
		}
		
		$dalCasino->updateUserGetAward($uid);
		
		$result['status'] = 1;
		$return['result'] = $result;
		return $return;
	}
	
	/**
	 * change casino
	 * 
	 * @param $uid
	 * @return array
	 */
	public static function changeCasino($uid)
	{
		$result = array();
		$myLvCount = Bll_Cache_Casino_Casino::getUserLvPoint($uid);
		$newCardCount = round($myLvCount/3);
		
		$dalCard = Dal_Island_Card::getDefaultInstance();
		$newCard = array(
        	'uid' => $uid,
			'cid' => 55141,
        	'count' => $newCardCount,
			'buy_time' => time(),
			'item_type' => 41);
		//add user card
        $dalCard->addUserCard($newCard);
		
        $dalCasino = Dal_Casino_Casino::getDefaultInstance();
        $dalCasino->updateUserLvPoint($uid, -$myLvCount);
        //clear cache
        Bll_Cache_Casino_Casino::clearCache('newgetUserLvPoint', $uid);
        Bll_Cache_Casino_Casino::clearCache('newgetUserLvPointRank', $uid);
        Bll_Cache_Casino_Casino::clearCache('newhasChange', $uid);
        
        $result['status'] = 1;
        return $result;
	}
}