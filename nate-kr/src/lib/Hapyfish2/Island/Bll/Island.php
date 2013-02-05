<?php

class Hapyfish2_Island_Bll_Island
{
    /**
     * load island info
     *
     * @param integer $ownerUid
     * @param integer $uid
     * @return array
     */
    public static function initIsland($ownerUid, $uid, $checkPraise = false)
    {
        //check is friend
        if ($ownerUid != $uid) {
            $isFriend = Hapyfish2_Platform_Bll_Friend::isFriend($uid, $ownerUid);
        }
        else {
            $isFriend = false;
        }
        
        //owner platform info
        $user = Hapyfish2_Platform_Bll_User::getUser($ownerUid);

        //
        $userVO = Hapyfish2_Island_HFC_User::getUserVO($ownerUid);

        if ($ownerUid != $uid) {
            //visit island
            Hapyfish2_Island_Cache_Visit::dailyVisit($uid, $ownerUid);
        }

        //get owner buildings info
        $buildings = Hapyfish2_Island_Bll_Building::getAllOnIsland($ownerUid);

		//
		$plantsVO = Hapyfish2_Island_Bll_Plant::getAllOnIsland($ownerUid, $uid);
		$plants = $plantsVO['plants'];
		
		if ($checkPraise) {
			$truePraise = 0;
			if (!empty($plants)) {
				$plantInfoList = Hapyfish2_Island_Cache_BasicInfo::getPlantList();
				foreach ($plants as $plant) {
					$truePraise += $plantInfoList[$plant['cid']]['add_praise'];
				}
			}
			if (!empty($buildings)) {
				$buildingInfoList = Hapyfish2_Island_Cache_BasicInfo::getBuildingList();
				foreach ($buildings as $building) {
					$truePraise += $buildingInfoList[$building['cid']]['add_praise'];
				}
			}
			if ($truePraise != $userVO['praise']) {
				
		        $userIsland = array(
		        	'praise' => $truePraise,
		        	'position_count' => $userVO['position_count'],
		        	'bg_island' => $userVO['bg_island'],
		        	'bg_island_id' => $userVO['bg_island_id'],
		        	'bg_sky' => $userVO['bg_sky'],
		        	'bg_sky_id' => $userVO['bg_sky_id'],
		        	'bg_sea' => $userVO['bg_sea'],
		        	'bg_sea_id' => $userVO['bg_sea_id'],
		        	'bg_dock' => $userVO['bg_dock'],
		        	'bg_dock_id' => $userVO['bg_dock_id']
		        );
		        
		        Hapyfish2_Island_HFC_User::updateFieldUserIsland($ownerUid, $userIsland);
		        
		        //update user achievement info about praise
		        if ($truePraise > $userVO['praise']) {
					$achievement = Hapyfish2_Island_HFC_Achievement::getUserAchievement($ownerUid);
					if ($achievement) {
						if ($achievement['num_13'] < $truePraise) {
							$achievement['num_13'] = $truePraise;
							Hapyfish2_Island_HFC_Achievement::updateUserAchievement($ownerUid, $achievement);
						}
					}
		        }
		        
		        $userVO['praise'] = $truePraise;
			}
		}
		
        if (!empty($plants)) {
        	$buildings = array_merge($buildings, $plants);
        }
        
        $cardStates = array();
        $nowTime = time();
        
        //防御卡
        $defenseTime = $userVO['defense'] - $nowTime;
        //保安卡
        $insuranceTime = $userVO['insurance'] - $nowTime;
        //双倍经验卡
        $doubleExpTime = $userVO['doubelexp'] - $nowTime;
        //一件收取卡
        $onekeyTime = $userVO['onekey'] - $nowTime;
        
        if ($defenseTime > 0) {
            $cardStates[] = array('cid' => 26841, 'time' => $defenseTime);
        }
        if ($insuranceTime > 0) {
            $cardStates[] = array('cid' => 27141, 'time' => $insuranceTime);
        }
        if ($doubleExpTime > 0) {
            $cardStates[] = array('cid' => 74841, 'time' => $doubleExpTime);
        }
        if ($onekeyTime > 0) {
        	$cardStates[] = array('cid' => 67441, 'time' => $onekeyTime);
        }
        
        $islandVo = array(
        	'uid' => $ownerUid,
			'uname' => $user['name'],
			'isFriend' => $isFriend,
			'face' => $user['figureurl'],
        	'sitLink' => 'http://www.kaixin001.com/home/?uid=' . $user['puid'],
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
        if ($ownerUid == $uid) {
            //get user new minifeed count
            $islandVo['newFeedCount'] = Hapyfish2_Island_Cache_Feed::getNewMiniFeedCount($uid);
            $userTitles = array();
        	if (!empty($userVO['title_list'])) {
        		$tmp = split(',', $userVO['title_list']);
        		foreach ($tmp as $id) {
        			$userTitles[] = array('title' => $id);
        		}
        	}
            
            $result['userTitles'] = $userTitles;
        }
        
        $dockVo = Hapyfish2_Island_Bll_Dock::initDock($ownerUid, $uid, $userVO['position_count']);
        
        //get user new remind count
        $islandVo['newRemindCount'] = Hapyfish2_Island_Cache_Remind::getNewRemindCount($uid);
        
        //get remind status
        $remindStatus = Hapyfish2_Island_Bll_Remind::getRemindStatus($uid, $ownerUid);
        $islandVo['remindAble1'] = $remindStatus['1'];
        $islandVo['remindAble2'] = $remindStatus['2'];
        $islandVo['remindAble3'] = $remindStatus['3'];
        $islandVo['remindAble4'] = $remindStatus['4'];
        
        $result['islandVo'] = $islandVo;
        $result['dockVo'] = $dockVo;
        return $result;
    }
    
    public static function reload($uid, $checkPraise = false)
    {
		$resultVo['status'] = 1;
		$resultVo['itemBoxChange'] = true;
		$resultVo['islandChange'] = true;
		$result['resultVo'] = $resultVo;

		//get island info
		$islandVo = self::initIsland($uid, $uid, $checkPraise);
		$result['islandVo'] = $islandVo['islandVo'];

		//get user info
		$result['userVo'] = Hapyfish2_Island_Bll_User::getUserInit($uid);

		//get user item box info
		$result['items'] = Hapyfish2_Island_Bll_Warehouse::loadItems($uid);
            
		$result['dockVo'] = $islandVo['dockVo'];
        
		return $result;
    }
    
    /**
     * diy island info
     *
     * @param integer $uid
     * @param array $changesAry
     * @param array $removesAry
     * @return array
     */
    public static function diyIsland($uid, $changesAry, $removesAry)
    {
        //
        if (empty($changesAry) && empty($removesAry)) {
			return self::reload($uid);
        }
        
        $changeBuildingList = array();
        $changePlantList = array();
        $changeBackgroundList = array();
        
        $removeBuildingList = array();
        $removePlantList = array();
        
        //data filter for change
        //split to changeBuildingList, changePlantList, changeBackgroundList
        //if has more same id, the last id is valid
        for($i = 0, $count = count($changesAry); $i < $count; $i++) {
        	$id = $changesAry[$i]['id'];
        	$itemType = substr($id, -2, 1);
        	//building
        	if ($itemType == 2) {
        		$changeBuildingList[$id] = $changesAry[$i];
        	}
        	//plant 
        	else if ($itemType == 3) {
        		$changePlantList[$id] = $changesAry[$i];
        	}
        	//background
            else if ($itemType == 1) {
        		$changeBackgroundList[$id] = $changesAry[$i];
        	}
        }
        
        //data filter for remove
        //split to removeBuildingList, removePlantList
        //if has more same id, the last id is valid
        //if has same id at change list, will do none for this id
        for($i = 0, $count = count($removesAry); $i < $count; $i++) {
        	$id = $removesAry[$i]['itemId'];
        	$itemType = substr($id, -2, 1);
        	//building
        	if ($itemType == 2) {
        		if (isset($changeBuildingList[$id])) {
        			unset($changeBuildingList[$id]);
        		} else {
        			$removeBuildingList[$id] = 1;
        		}
        	}
        	//plant 
        	else if ($itemType == 3) {
        		if (isset($changePlantList[$id])) {
        			unset($changePlantList[$id]);
        		} else {
        			$removePlantList[$id] = 1;
        		}
        	}
        	//background
            else if ($itemType == 1) {
        		if (isset($changeBackgroundList[$id])) {
        			unset($changeBackgroundList[$id]);
        		}
        	}
        }
        
        $praiseChange = 0;
        $buildingChange = 0;
        $plantChange = 0;
        $backgroundChange = 0;
        
        if (!empty($changeBuildingList) || !empty($removeBuildingList)) {
        	//building info list
        	$buildingInfoList = Hapyfish2_Island_Cache_BasicInfo::getBuildingList();

            foreach ($changeBuildingList as $id => $item) {
            	$id = substr($id, 0, -2);
            	$building = Hapyfish2_Island_HFC_Building::getOne($uid, $id, 1);
            	//confirm user has the building
            	if($building) {
            		//change info
            		$building['x'] = $item['x'];
            		$building['y'] = $item['y'];
            		$building['z'] = $item['z'];
            		$building['mirro'] = $item['mirro'];
            		$building['can_find'] = $item['canFind'];
            		$building['status'] = 1;
                    //update
                    $ok = Hapyfish2_Island_HFC_Building::updateOne($uid, $id, $building);
					if ($ok) {
						Hapyfish2_Island_Cache_Building::pushOneIdOnIsland($uid, $id);
						$praiseChange += $buildingInfoList[$building['cid']]['add_praise'];
            			$buildingChange = 1;
					}
            	}
            }
            
            foreach ($removeBuildingList as $id => $item) {
            	$id = substr($id, 0, -2);
            	//confirm user has the building
            	$building = Hapyfish2_Island_HFC_Building::getOne($uid, $id, 1);
            	if ($building) {
            		//confirm the buiding is on island
            		if ($building['status'] == 1) {
            			//change info
						$building['status'] = 0;
						//update
						$ok = Hapyfish2_Island_HFC_Building::updateOne($uid, $id, $building);
						if ($ok) {
							Hapyfish2_Island_Cache_Building::popOneIdOnIsland($uid, $id);
							$praiseChange -= $buildingInfoList[$building['cid']]['add_praise'];
							$buildingChange = 1;
						}
            		}
            	}
            }
        }
        
        if (!empty($changePlantList) || !empty($removePlantList)) {
        	//get lock for diy
        	//other user can not change, just like mooch
        	
        	//plant info list
        	$plantInfoList = Hapyfish2_Island_Cache_BasicInfo::getPlantList();
        	$ids = Hapyfish2_Island_Cache_Plant::getOnIslandIds($uid);
			$ids = array_flip($ids);
        	
        	foreach ($changePlantList as $id => $item) {
            	$id = substr($id, 0, -2);
            	$plant = Hapyfish2_Island_HFC_Plant::getOne($uid, $id, 1);
            	
            	//confirm user has the plant
            	if($plant) {
					if (isset($ids[$id])) {
	            		$plant['status'] = 1;
	            	} else {
	            		$plant['status'] = 0;
	            		$plant['start_deposit'] = 0;
						$plant['deposit'] = 0;
						$plant['event'] = 0;
						$plant['wait_visitor_num'] = 0;
	            	}
            		
            		//if the plant is put on island
	            	if ($plant['status'] == 0) {
	            		//change info
	            		$plant['x'] = $item['x'];
	            		$plant['y'] = $item['y'];
	            		$plant['z'] = $item['z'];
	            		$plant['mirro'] = $item['mirro'];
	            		$plant['can_find'] = $item['canFind'];
	            		$plant['status'] = 1;
	            		//update
	            		$ok = Hapyfish2_Island_HFC_Plant::updateOne($uid, $id, $plant);
	            		if ($ok) {
	            			Hapyfish2_Island_Cache_Plant::pushOneIdOnIsland($uid, $id);
	            			$praiseChange += $plantInfoList[$plant['cid']]['add_praise'];
	            			$plantChange = 1;
	            		}
	            	} else {
	            		//if the plant change position
	            		//change info
	            		$plant['x'] = $item['x'];
	            		$plant['y'] = $item['y'];
	            		$plant['z'] = $item['z'];
	            		$plant['mirro'] = $item['mirro'];
	            		$plant['can_find'] = $item['canFind'];
	            		//update
	            		Hapyfish2_Island_HFC_Plant::updateOne($uid, $id, $plant);
	            		$plantChange = 1;
	            	}
	           	
            	}
        	}
	        	
            foreach ($removePlantList as $id => $rmid) {
            	$id = substr($id, 0, -2);
            	$plant = Hapyfish2_Island_HFC_Plant::getOne($uid, $id, 1);
            	//confirm user has plant
            	if($plant) {
					if (isset($ids[$id])) {
	            		$plant['status'] = 1;
	            	}
            		
					if ($plant['status'] == 1) {
            			//change info
            			$plant['status'] = 0;
            			$plant['start_deposit'] = 0;
            			$plant['deposit'] = 0;
            			$plant['event'] = 0;
            			$plant['wait_visitor_num'] = 0;
            			//update
            			$ok = Hapyfish2_Island_HFC_Plant::updateOne($uid, $id, $plant);
            			if ($ok) {
            				Hapyfish2_Island_Cache_Plant::popOneIdOnIsland($uid, $id);
            				$praiseChange -= $plantInfoList[$plant['cid']]['add_praise'];
            				$plantChange = 1;
            				//clear mooch info
            				Hapyfish2_Island_Cache_Mooch::clearMoochPlant($uid, $id);
            			}
					}
            	}
            }
        }
            
		//change user background
		if (!empty($changeBackgroundList)) {
            //user background list
            $userBackgroundList = Hapyfish2_Island_Cache_Background::getAll($uid);
            $fieldInfo = array();
            
            foreach ($changeBackgroundList as $id => $item) {
            	$id = substr($id, 0, -2);
            	//confirm user has background
            	if (isset($userBackgroundList[$id])) {
            		$bgItem = $userBackgroundList[$id];
            		if ($bgItem['item_type'] == 11) {
            			//island
						$fieldInfo['bg_island'] = $bgItem['bgid'];
						$fieldInfo['bg_island_id'] = $bgItem['id'];
            		} else if ($bgItem['item_type'] == 12) {
            			//sky
						$fieldInfo['bg_sky'] = $bgItem['bgid'];
						$fieldInfo['bg_sky_id'] = $bgItem['id'];
            		} else if ($bgItem['item_type'] == 13) {
            			//sea
						$fieldInfo['bg_sea'] = $bgItem['bgid'];
						$fieldInfo['bg_sea_id'] = $bgItem['id'];
            		} else if ($bgItem['item_type'] == 14) {
            			//dock
						$fieldInfo['bg_dock'] = $bgItem['bgid'];
						$fieldInfo['bg_dock_id'] = $bgItem['id'];
            		}
            	}
            }
            
            if (!empty($fieldInfo)) {
            	//update HFC cache if has changed info
            	Hapyfish2_Island_HFC_User::updateFieldUserIsland($uid, $fieldInfo);
            	$backgroundChange = 1;
            }
		}
        
        try {
            if ($buildingChange == 1) {
                //refresh user building cache
                //Hapyfish2_Island_Cache_Building::loadAllOnIsland($uid);
            }
            
            if ($plantChange == 1) {
                //refresh user cache of on island plant ids
                //Hapyfish2_Island_Cache_Plant::reloadOnIslandIds($uid);
            }
            
            if ($backgroundChange == 1) {
				Hapyfish2_Island_Cache_Background::loadAll($uid);
            }

            $checkPraise = false;
            if ($praiseChange != 0) {
            	$checkPraise = true;
            }

            return self::reload($uid, $checkPraise);
        }
        catch (Exception $e) {
            $resultVo['status'] = -1;
            $resultVo['content'] = 'serverWord_110';
            $result['resultVo'] = $resultVo;
            return $result;
        }

    }
    
    public static function initCacheIsland($uid)
    {
        $isFriend = false;
        
        //platform info
        $user = Hapyfish2_Platform_Bll_User::getUser($uid);

        //
        $userVO = Hapyfish2_Island_HFC_User::getUserVO($uid);

        //get buildings info
        $buildings = Hapyfish2_Island_Bll_Building::getAllOnIsland($uid);

		//
		$plantsVO = Hapyfish2_Island_Bll_Plant::getAllOnIsland($uid, $uid);
		$plants = $plantsVO['plants'];
        if (!empty($plants)) {
        	$buildings = array_merge($buildings, $plants);
        }
        
        $cardStates = array();
        $nowTime = time();
        
        //防御卡
        $defenseTime = 12*3600;
        //保安卡
        $insuranceTime = 6*3600;
		$cardStates[] = array('cid' => 26841, 'time' => $defenseTime);
		$cardStates[] = array('cid' => 27141, 'time' => $insuranceTime);
        
        $islandVo = array(
        	'uid' => $uid,
			'uname' => $user['name'],
			'isFriend' => $isFriend,
			'face' => $user['figureurl'],
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
        
        $dockVo = Hapyfish2_Island_Bll_Dock::initDock($uid, $uid, $userVO['position_count']);
        
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
    
	public static function restoreInitUserIsland($uid)
	{
		$file = TEMP_DIR . '/inituserisland.' . $uid . '.cache';
		if (is_file($file)) {
			return file_get_contents($file);
		} else {
			return self::dumpInitUserIsland($uid);
		}
	}
    
	public static function dumpInitUserIsland($uid)
	{
		$userIsland = self::initCacheIsland($uid);
		$file = TEMP_DIR . '/inituserisland.' . $uid . '.cache';
		$data = json_encode($userIsland);
		file_put_contents($file, $data);
		return $data;
	}

}