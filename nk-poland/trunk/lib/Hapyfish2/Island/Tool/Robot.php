<?php

class Hapyfish2_Island_Tool_Robot
{
	public static function dumpInitIsland($uid, $gmuid = 134)
	{
		$userIsland = self::initCacheIsland($uid, $gmuid);
		$file =  TEMP_DIR . '/robots/'.$gmuid . '.cache';
		$data = json_encode($userIsland);
		file_put_contents($file, $data);
		return $data;
	}
	
	public static function getAllOnIsland($uid)
    {
		$data = Hapyfish2_Island_HFC_Plant::getOnIsland($uid, 1, false);
		$plants = array();
		$visitorNum  = 0;
		
		if ($data) {
			$now = time();
			foreach ($data['plants'] as $item) {
				$plant = self::handlerPlant($item, $now);
				$plants[] = $plant;
				$visitorNum += 2;
			}
		}
		
		return array('plants' => $plants, 'visitorNum' => $visitorNum);
    }
    
    public static function handlerPlant(&$item, $now)
    {
    	$plantinfo = Hapyfish2_Island_Cache_BasicInfo::getDetailInfo($item['cid']);
    	$plant = array(
    		'id' => $item['id'] . $item['item_type'],
    		'cid' => $item['cid'],
			'x' => $item['x'],
			'y' => $item['y'],
			'z' => $item['z'],
			'mirro' => $item['mirro'],
    		'canFind' => $item['can_find'],
    		'event' => 0,
    		'hasSteal' => 0,
    		'waitVisitorNum' => 2,
    		'startDeposit' => $plantinfo['ticket'],
    		'deposit' => $plantinfo['ticket'],
    		'payRemainder' => 0
    	);
    	
    	return $plant;
    }
    
    public static function initDock($uid, $gmuid, $praise, $positionCount = null)
    {
		$resultVo = array();
		$boatVo = array();
		
		//get user position list
//		$dockPositionArray = Hapyfish2_Island_HFC_Dock::getUserDock($uid, $positionCount);
		
		$now = time();
		
//		foreach ($dockPositionArray as $dockPosition) {
//			$boatVo[] = self::getDockShipInfo($uid, $dockPosition, $now);
//		}
		
		for($i=1; $i<=8; $i++){
			$dockPosition['position_id'] = $i;
			$boatVo[] = self::getDockShipInfo($uid, $dockPosition, $now, $praise);
		}
        $resultVo['boatPositions'] = $boatVo;
        $resultVo['uid'] = $gmuid;
        $resultVo['positionNum'] = 8;
		
		return $resultVo;
    }
    
    /**
     * get dock ship info
     *
     * @param integer $uid
     * @param array $boatInfo
     * @return array $boatItem
     */
    public static function getDockShipInfo($uid, $dockPosition, $now, $praise)
    {
    	$shiplist = array(1, 2, 3, 4, 5, 6, 7, 8);
    	$shipIds = array_rand($shiplist, 1);
    	$shipId = $shiplist[$shipIds];
    	$addVisitor = Hapyfish2_Island_Bll_Dock::getShipAddVisitorByPraise($shipId, $praise);
    	$shipInfo = Hapyfish2_Island_Cache_BasicInfo::getShipInfo($shipId);
        $startVisitorNum = $addVisitor + $shipInfo['start_visitor_num'];
        $boatItem = array();
        $boatItem['state'] = 'arrive_1';
		$boatItem['visitorNum'] = $startVisitorNum;
		$boatItem['maxVisitorNum'] = $startVisitorNum;
		$boatItem['id'] = $dockPosition['position_id'];
		$boatItem['boatId'] = $shipId;
		$boatItem['canSteal'] = true;
        $boatItem['time'] = 1;

        return $boatItem;
    }
	
    public static function initCacheIsland($uid, $gmuid)
    {
        $isFriend = false;
        
        //platform info
        $user = Hapyfish2_Platform_Bll_User::getUser($uid);

        //
        $userVO = Hapyfish2_Island_HFC_User::getUserVO($uid);

        //get buildings info
        $buildings = Hapyfish2_Island_Bll_Building::getAllOnIsland($uid, 1);

		//
		$plantsVO = self::getAllOnIsland($uid);
		$plants = $plantsVO['plants'];
        if (!empty($plants)) {
        	$buildings = array_merge($buildings, $plants);
        }
        
        $cardStates = array();
        $nowTime = time();
        
        //防御卡
        $defenseTime = 24*3600;
        //保安卡
        $insuranceTime = 24*3600;
        //双倍经验卡
        $doubleExpTime = 24*3600;
        //一件收取卡
        $onekeyTime = 24*3600;
        
		$cardStates[] = array('cid' => 26841, 'time' => $defenseTime);
		$cardStates[] = array('cid' => 27141, 'time' => $insuranceTime);
		$cardStates[] = array('cid' => 74841, 'time' => $doubleExpTime);
		$cardStates[] = array('cid' => 67441, 'time' => $onekeyTime);
        
        $islandVo = array(
        	'uid' => $gmuid,
			'uname' => $user['name'],
			'isFriend' => $isFriend,
			'face' => STATIC_HOST . '/apps/island/images/robot/'.$gmuid.'.jpg',
        	'sitLink' => '',
			'exp' => $userVO['exp'],
			'maxExp' => $userVO['next_level_exp'],
			'level' => $userVO['level'],
			'islandLevel' => $userVO['island_level'],
			'island' => $userVO['bg_island'],
			'sky' => $userVO['bg_sky'],
			'sea' => $userVO['bg_sea'],
			'dock' => $userVO['bg_dock'],
			'islandId' => $userVO['bg_island_id'],
			'skyId' => $userVO['bg_sky_id'],
			'seaId' => $userVO['bg_sea_id'],
			'dockId' => $userVO['bg_dock_id'],
			'praise' => $userVO['praise'],
			'visitorNum' => $plantsVO['visitorNum'],
			'currentTitle' => $userVO['title'],
			'buildings' => $buildings,
			'cardStates' => $cardStates
        );

        $result = array();
        
        $dockVo = self::initDock($uid, $gmuid, $userVO['praise'], $userVO['position_count']);
        
        //get user new remind count
        $islandVo['newRemindCount'] = 0;
        
        //get remind status
        $islandVo['remindAble1'] = 0;
        $islandVo['remindAble2'] = 0;
        $islandVo['remindAble3'] = 0;
        $islandVo['remindAble4'] = 0;
        
        $result['islandVo'] = $islandVo;
        $result['dockVo'] = $dockVo;
        return $result;
    }
}