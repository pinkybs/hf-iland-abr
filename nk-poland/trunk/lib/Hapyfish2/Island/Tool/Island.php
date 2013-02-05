<?php

class Hapyfish2_Island_Tool_Island
{
	public static function dumpInitIsland($uid, $gmuid = 134)
	{
		$userIsland = self::initCacheIsland($uid, $gmuid);
		$file = TEMP_DIR . '/inituserisland.' . $uid . '.cache';
		$data = json_encode($userIsland);
		file_put_contents($file, $data);
		return $data;
	}
	
	public static function getAllOnIsland($uid)
    {
		$data = Hapyfish2_Island_HFC_Plant::getOnIsland($uid, false);
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
    		'startDeposit' => 0,
    		'deposit' => 0,
    		'payRemainder' => 0
    	);
    	
    	return $plant;
    }
    
    public static function initDock($uid, $gmuid, $positionCount = null)
    {
		$resultVo = array();
		$boatVo = array();
		
		//get user position list
		$dockPositionArray = Hapyfish2_Island_HFC_Dock::getUserDock($uid, $positionCount);
		
		$now = time();
		
		foreach ($dockPositionArray as $dockPosition) {
			$boatVo[] = self::getDockShipInfo($uid, $dockPosition, $now);
		}
		
        $resultVo['boatPositions'] = $boatVo;
        $resultVo['uid'] = $gmuid;
        $resultVo['positionNum'] = $positionCount;
		
		return $resultVo;
    }
    
    /**
     * get dock ship info
     *
     * @param integer $uid
     * @param array $boatInfo
     * @return array $boatItem
     */
    public static function getDockShipInfo($uid, $dockPosition, $now)
    {
        $boatItem = array();
        $boatItem['state'] = 'onTheRoad';
		$boatItem['visitorNum'] = 100;
		$boatItem['maxVisitorNum'] = 100;
		$boatItem['id'] = $dockPosition['position_id'];
		$boatItem['boatId'] = $dockPosition['ship_id'];
		$boatItem['canSteal'] = false;
        $boatItem['time'] = -3600;

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
        $buildings = Hapyfish2_Island_Bll_Building::getAllOnIsland($uid);

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
        
		require_once(CONFIG_DIR . '/language.php');
		
        $islandVo = array(
        	'uid' => $gmuid,
			'uname' => LANG_PLATFORM_BASE_TXT_14,
			'isFriend' => $isFriend,
			'face' => STATIC_HOST . '/apps/island/images/lele.jpg',
        	'sitLink' => '',
			'exp' => 49730,
			'maxExp' => 59911,
			'level' => 15,
			'islandLevel' => 8,
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
        
        $dockVo = self::initDock($uid, $gmuid, $userVO['position_count']);
        
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