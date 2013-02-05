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
    public static function initIsland($ownerUid, $uid, $checkPraise = false, $islandId)
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
	    $ownerCurrentIsland = $userVO['current_island'];

        //check user current island
		if (  !in_array($islandId, array('1', '2', '3', '4')) ) {
	        $islandId = $userVO['current_island'];
		}

        //get user island info
        $ownerIslandInfo = Hapyfish2_Island_HFC_User::getUserIsland($ownerUid);

    	if ( $islandId == 2 ) {
        	if ( $userVO['desertIslandState'] != 1 ) {
	        	$islandId = $userVO['current_island'];
        	}
        }
        else if ( $islandId == 3 ) {
        	if ( $userVO['hawaiiIslandState'] != 1 ) {
	        	$islandId = $userVO['current_island'];
        	}
        }
        else if ( $islandId == 4 ) {
        	if ( $userVO['iceLandState'] != 1 ) {
	        	$islandId = $userVO['current_island'];
        	}
        }

        $firstIntoIsland = false;
        if ($ownerUid != $uid) {
            //visit island
            Hapyfish2_Island_Cache_Visit::dailyVisit($uid, $ownerUid);
        }
        else {
        	if ( $ownerCurrentIsland != $islandId ) {
        		//update owner island info,current_island
		        $ownerIslandInfo['current_island'] = $islandId;
		        Hapyfish2_Island_HFC_User::updateFieldUserIsland($ownerUid, $ownerIslandInfo);
		        $userVO = Hapyfish2_Island_HFC_User::getUserVO($ownerUid);
        	}
	        if ( $islandId != 1 ) {
        		$firstIntoIsland = Hapyfish2_Island_Cache_User::isFirstIntoIsland($ownerUid, $islandId);
	        }
        }

        //get owner buildings info
        $buildings = Hapyfish2_Island_Bll_Building::getAllOnIsland($ownerUid, $islandId);

		//
		$plantsVO = Hapyfish2_Island_Bll_Plant::getAllOnIsland($ownerUid, $uid, $islandId);
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
		        //update user achievement info about praise
		        if ($truePraise > $userVO['praise']) {
					$achievement = Hapyfish2_Island_HFC_Achievement::getUserAchievement($ownerUid);
					if ($achievement) {
						if ($achievement['num_13'] < $truePraise) {
							$achievement['num_13'] = $truePraise;

							try {
								Hapyfish2_Island_HFC_Achievement::updateUserAchievement($ownerUid, $achievement);

								//task id 3015,task type 13
								Hapyfish2_Island_Bll_Task::checkTask($uid, 3015);
							} catch (Exception $e) {
							}
						}
					}
		        }
		        //根据岛屿ID，更新不同信息
		        $userIsland = Hapyfish2_Island_HFC_User::getUserIsland($ownerUid);
		        switch ( $islandId ) {
		        	case 2 :
		        		$userIsland['praise_2'] = $truePraise;
		        		$userVO['praise_2'] = $truePraise;
		        		break;
		        	case 3 :
		        		$userIsland['praise_3'] = $truePraise;
		        		$userVO['praise_3'] = $truePraise;
		        		break;
		        	case 4 :
		        		$userIsland['praise_4'] = $truePraise;
		        		$userVO['praise_4'] = $truePraise;
		        		break;
		        	default :
		        		$userIsland['praise'] = $truePraise;
		        		$userVO['praise'] = $truePraise;
		        		break;
		        }
		        Hapyfish2_Island_HFC_User::updateFieldUserIsland($ownerUid, $userIsland);

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
        //财神卡
        $mammonTime = $userVO['mammon'] - $nowTime;
        //穷神卡
        $poorTime = $userVO['poor'] - $nowTime;

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
        if ( $mammonTime > 0 ) {
        	$cardStates[] = array('cid' => 67341, 'time' => $mammonTime);
        }
        if ( $poorTime > 0 ) {
        	$cardStates[] = array('cid' => 67041, 'time' => $poorTime);
        }

		switch ( $islandId ) {
        	case 1 :
        		$currentIslandLevel = $userVO['island_level'];
        		$currentIsland = $userVO['bg_island'];
        		$currentSky = $userVO['bg_sky'];
        		$currentSea = $userVO['bg_sea'];
        		//$currentDock = $userVO['bg_dock'];
        		//$currentDockId = $userVO['bg_dock_id'];
        		$currentIslandId = $userVO['bg_island_id'];
        		$currentSkyId = $userVO['bg_sky_id'];
        		$currentSeaId = $userVO['bg_sea_id'];
        		$currentPraise = $userVO['praise'];
        		break;
        	case 2 :
        		$currentIslandLevel = $userVO['island_level_2'];
        		$currentIsland = $userVO['bg_island_2'];
        		$currentSky = $userVO['bg_sky_2'];
        		$currentSea = $userVO['bg_sea_2'];
        		//$currentDock = $userVO['bg_dock_2'];
        		//$currentDockId = $userVO['bg_dock_id_2'];
        		$currentIslandId = $userVO['bg_island_id_2'];
        		$currentSkyId = $userVO['bg_sky_id_2'];
        		$currentSeaId = $userVO['bg_sea_id_2'];
        		$currentPraise = $userVO['praise_2'];
        		break;
        	case 3 :
        		$currentIslandLevel = $userVO['island_level_3'];
        		$currentIsland = $userVO['bg_island_3'];
        		$currentSky = $userVO['bg_sky_3'];
        		$currentSea = $userVO['bg_sea_3'];
        		//$currentDock = $userVO['bg_dock_3'];
        		//$currentDockId = $userVO['bg_dock_id_3'];
        		$currentIslandId = $userVO['bg_island_id_3'];
        		$currentSkyId = $userVO['bg_sky_id_3'];
        		$currentSeaId = $userVO['bg_sea_id_3'];
        		$currentPraise = $userVO['praise_3'];
        		break;
        	case 4 :
        		$currentIslandLevel = $userVO['island_level_4'];
        		$currentIsland = $userVO['bg_island_4'];
        		$currentSky = $userVO['bg_sky_4'];
        		$currentSea = $userVO['bg_sea_4'];
        		//$currentDock = $userVO['bg_dock_4'];
        		//$currentDockId = $userVO['bg_dock_id_4'];
        		$currentIslandId = $userVO['bg_island_id_4'];
        		$currentSkyId = $userVO['bg_sky_id_4'];
        		$currentSeaId = $userVO['bg_sea_id_4'];
        		$currentPraise = $userVO['praise_4'];
        		break;
        }

        $medalArray = Hapyfish2_Island_Bll_Rank::isTopTen($ownerUid);

        if ($user['figureurl'] == 'http://avatars.nasza-klasa.pl/img/avatar/brakzdjecia') {
            $user['figureurl'] = STATIC_HOST.'/apps/island/images/common/nopic.gif';
        }
        $islandVo = array(
        	'uid' => $ownerUid,
			'uname' => $user['name'],
            'nick' => $user['nick'],
			'isFriend' => $isFriend,
			'face' => $user['figureurl'],
        	'sitLink' => '',
			'exp' => $userVO['exp'],
			'maxExp' => $userVO['next_level_exp'],
			'level' => $userVO['level'],
			'islandLevel' => $currentIslandLevel,
			'island' => $currentIsland,
			'sky' => $currentSky,
			'sea' => $currentSea,
			//'dock' => $currentDock,
			'dock' => $userVO['bg_dock'],
			'islandId' => $currentIslandId . '11',
			'skyId' => $currentSkyId . '12',
			'seaId' => $currentSeaId . '13',
			//'dockId' => $currentDockId . '14',
			'dockId' => $userVO['bg_dock_id'] . '14',
			'praise' => $currentPraise,
			'visitorNum' => $plantsVO['visitorNum'],
			'currentTitle' => $userVO['title'],
			'buildings' => $buildings,
        	'cardStates' => $cardStates,
			'currentIslandId' => $islandId,
			'desertIslandState' => $userVO['desertIslandState'],
			'hawaiiIslandState' => $userVO['hawaiiIslandState'],
			'iceLandState' => $userVO['iceLandState'],
        	'firstIntoIsland' => $firstIntoIsland ? 1 : 0,
        	'medalArray' => $medalArray
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

    public static function reload($uid, $checkPraise = false, $ownerCurrentIsland)
    {
		$resultVo['status'] = 1;
		$resultVo['itemBoxChange'] = true;
		$resultVo['islandChange'] = true;
		$result['resultVo'] = $resultVo;

		//get island info
		$islandVo = self::initIsland($uid, $uid, $checkPraise, $ownerCurrentIsland);
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
    	//get user vo
        $userVO = Hapyfish2_Island_HFC_User::getUserVO($uid);
        //check user current island
        $ownerCurrentIsland = $userVO['current_island'];

        //
        if (empty($changesAry) && empty($removesAry)) {
			return self::reload($uid, false, $ownerCurrentIsland);
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
            		$building['status'] = $ownerCurrentIsland;
                    //update
                    $ok = Hapyfish2_Island_HFC_Building::updateOne($uid, $id, $building, true);
					if ($ok) {
						Hapyfish2_Island_Cache_Building::pushOneIdOnIsland($uid, $id, $ownerCurrentIsland);
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
            		if ($building['status'] == $ownerCurrentIsland) {
            			//change info
						$building['status'] = 0;
						//update
						$ok = Hapyfish2_Island_HFC_Building::updateOne($uid, $id, $building, true);
						if ($ok) {
							Hapyfish2_Island_Cache_Building::popOneIdOnIsland($uid, $id, $ownerCurrentIsland);
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
        	$ids = Hapyfish2_Island_Cache_Plant::getOnIslandIds($uid, $ownerCurrentIsland);
			$ids = array_flip($ids);

        	foreach ($changePlantList as $id => $item) {
            	$id = substr($id, 0, -2);
            	$plant = Hapyfish2_Island_HFC_Plant::getOne($uid, $id, 1, $ownerCurrentIsland);

            	//confirm user has the plant
            	if($plant) {
					if (isset($ids[$id])) {
	            		$plant['status'] = $ownerCurrentIsland;
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
	            		$plant['status'] = $ownerCurrentIsland;
	            		//update
	            		$ok = Hapyfish2_Island_HFC_Plant::updateOne($uid, $id, $plant, true);
	            		if ($ok) {
	            			Hapyfish2_Island_Cache_Plant::pushOneIdOnIsland($uid, $id, $ownerCurrentIsland);
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
	            		Hapyfish2_Island_HFC_Plant::updateOne($uid, $id, $plant, true);
	            		$plantChange = 1;
	            	}

            	}
        	}

            foreach ($removePlantList as $id => $rmid) {
            	$id = substr($id, 0, -2);
            	$plant = Hapyfish2_Island_HFC_Plant::getOne($uid, $id, 1, $ownerCurrentIsland);
            	//confirm user has plant
            	if($plant) {
					if (isset($ids[$id])) {
	            		$plant['status'] = $ownerCurrentIsland;
	            	}

					if ($plant['status'] == $ownerCurrentIsland) {
            			//change info
            			$plant['status'] = 0;
            			$plant['start_deposit'] = 0;
            			$plant['deposit'] = 0;
            			$plant['event'] = 0;
            			$plant['wait_visitor_num'] = 0;
            			//update
            			$ok = Hapyfish2_Island_HFC_Plant::updateOne($uid, $id, $plant, true);
            			if ($ok) {
            				Hapyfish2_Island_Cache_Plant::popOneIdOnIsland($uid, $id, $ownerCurrentIsland);
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
			switch ( $ownerCurrentIsland ) {
            	case 2 :
            		$island = 'bg_island_2';
            		$islandId = 'bg_island_id_2';
            		$sky = 'bg_sky_2';
            		$skyId = 'bg_sky_id_2';
            		$sea = 'bg_sea_2';
            		$seaId = 'bg_sea_id_2';
            		$dock = 'bg_dock';
            		$dockId = 'bg_dock_id';
            		break;
            	case 3 :
            		$island = 'bg_island_3';
            		$islandId = 'bg_island_id_3';
            		$sky = 'bg_sky_3';
            		$skyId = 'bg_sky_id_3';
            		$sea = 'bg_sea_3';
            		$seaId = 'bg_sea_id_3';
            		$dock = 'bg_dock';
            		$dockId = 'bg_dock_id';
            		break;
            	case 4 :
            		$island = 'bg_island_4';
            		$islandId = 'bg_island_id_4';
            		$sky = 'bg_sky_4';
            		$skyId = 'bg_sky_id_4';
            		$sea = 'bg_sea_4';
            		$seaId = 'bg_sea_id_4';
            		$dock = 'bg_dock';
            		$dockId = 'bg_dock_id';
            		break;
            	default :
            		$island = 'bg_island';
            		$islandId = 'bg_island_id';
            		$sky = 'bg_sky';
            		$skyId = 'bg_sky_id';
            		$sea = 'bg_sea';
            		$seaId = 'bg_sea_id';
            		$dock = 'bg_dock';
            		$dockId = 'bg_dock_id';
            		break;
            }

            foreach ($changeBackgroundList as $id => $item) {
            	$id = substr($id, 0, -2);
            	//confirm user has background
            	if (isset($userBackgroundList[$id])) {
            		$bgItem = $userBackgroundList[$id];
            		if ($bgItem['item_type'] == 11) {
            			//island
						$fieldInfo[$island] = $bgItem['bgid'];
						$fieldInfo[$islandId] = $bgItem['id'];
            		} else if ($bgItem['item_type'] == 12) {
            			//sky
						$fieldInfo[$sky] = $bgItem['bgid'];
						$fieldInfo[$skyId] = $bgItem['id'];
            		} else if ($bgItem['item_type'] == 13) {
            			//sea
						$fieldInfo[$sea] = $bgItem['bgid'];
						$fieldInfo[$seaId] = $bgItem['id'];
            		} else if ($bgItem['item_type'] == 14) {
            			//dock
						$fieldInfo[$dock] = $bgItem['bgid'];
						$fieldInfo[$dockId] = $bgItem['id'];
            		}
            	}
            }

            if (!empty($fieldInfo)) {
            	//update HFC cache if has changed info
            	Hapyfish2_Island_HFC_User::updateFieldUserIsland($uid, $fieldInfo, true);
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

            return self::reload($uid, $checkPraise, $ownerCurrentIsland);
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

        if ($user['figureurl'] == 'http://avatars.nasza-klasa.pl/img/avatar/brakzdjecia') {
            $user['figureurl'] = STATIC_HOST.'/apps/island/images/common/nopic.gif';
        }
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

	/**
	 * open new island
	 *
	 * @param int $uid
	 * @param int $islandId
	 */
	public static function openIsland($uid, $islandId, $priceType)
	{
		$newIslandVo = Hapyfish2_Island_Bll_BasicInfo::getNewIslandVo();

		$resultVo = array('status' => -1);
		$newIsland = array('2' => array('needLevel' => $newIslandVo['openIsland2Level'], 'needCoin' => $newIslandVo['openIsland2Coin'], 'needGold' => $newIslandVo['openIsland2Gem'], 'needVipGold' => $newIslandVo['openIsland2HZGem'], 'name' => $newIslandVo['IslandName2']),
						   '3' => array('needLevel' => $newIslandVo['openIsland3Level'], 'needCoin' => $newIslandVo['openIsland3Coin'], 'needGold' => $newIslandVo['openIsland3Gem'], 'needVipGold' => $newIslandVo['openIsland3HZGem'], 'name' => $newIslandVo['IslandName3']),
						   '4' => array('needLevel' => $newIslandVo['openIsland4Level'], 'needCoin' => $newIslandVo['openIsland4Coin'], 'needGold' => $newIslandVo['openIsland4Gem'], 'needVipGold' => $newIslandVo['openIsland4HZGem'], 'name' => $newIslandVo['IslandName4']));
		$openIslandInfo = $newIsland[$islandId];

		if ( !in_array($islandId, array('2', '3', '4')) ) {
            return $resultVo;
		}

		//check user level
        $userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
        if ( $userLevelInfo['level'] < $openIslandInfo['needLevel'] ) {
            $resultVo['content'] = 'serverWord_136';
            return $resultVo;
        }

        //get user info
        $userVo = Hapyfish2_Island_HFC_User::getUserVO($uid);
        if ( $islandId == 2 ) {
        	if ( $userVo['desertIslandState'] == 1 ) {
	            $resultVo['content'] = 'serverWord_1004';
            	return $resultVo;
        	}
        }
        else if ( $islandId == 3 ) {
        	if ( $userVo['hawaiiIslandState'] == 1 ) {
	            $resultVo['content'] = 'serverWord_1004';
	            return $resultVo;
        	}
        }
        else if ( $islandId == 4 ) {
        	if ( $userVo['iceLandState'] == 1 ) {
	            $resultVo['content'] = 'serverWord_1004';
	            return $resultVo;
        	}
        }

        $userLevelInfo = array('level' => $userVo['level'],
							   'island_level' => $userVo['island_level'],
							   'island_level_2' => $userVo['island_level_2'],
							   'island_level_3' => $userVo['island_level_3'],
							   'island_level_4' => $userVo['island_level_4']);
        //get user island info
        $islandInfo = Hapyfish2_Island_HFC_User::getUserIsland($uid);
        //get user unlock island info
        $unlockIsland = $islandInfo['unlock_island'];
        $newUnlockIsland = $unlockIsland.','.$islandId;
        $bgIsland2 = 85911;
        $bgIsland3 = 86111;
        $bgIsland4 = 86011;

        if ( $islandId == 2 ) {
        	$islandInfo['praise_2'] = 0;
        	$islandInfo['bg_island_2'] = $bgIsland2;
        	$islandInfo['bg_island_id_2'] = 11;
        	$islandInfo['bg_sky_2'] = 23212;
        	$islandInfo['bg_sky_id_2'] = 12;
        	$islandInfo['bg_sea_2'] = 22213;
        	$islandInfo['bg_sea_id_2'] = 13;
        	//get user next island level
        	$nextIslandLevel = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfoByUserLevel($userVo['level'], 2);
        	$userLevelInfo['island_level_2'] = $nextIslandLevel;
        }
        else if ( $islandId == 3 ) {
        	$islandInfo['praise_3'] = 0;
        	$islandInfo['bg_island_3'] = $bgIsland3;
        	$islandInfo['bg_island_id_3'] = 15;
        	$islandInfo['bg_sky_3'] = 23212;
        	$islandInfo['bg_sky_id_3'] = 16;
        	$islandInfo['bg_sea_3'] = 22213;
        	$islandInfo['bg_sea_id_3'] = 17;
        	//get user next island level
        	$nextIslandLevel = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfoByUserLevel($userVo['level'], 3);
        	$userLevelInfo['island_level_3'] = $nextIslandLevel;
        }
        else if ( $islandId == 4 ) {
        	$islandInfo['praise_4'] = 0;
        	$islandInfo['bg_island_4'] = $bgIsland4;
        	$islandInfo['bg_island_id_4'] = 19;
        	$islandInfo['bg_sky_4'] = 23212;
        	$islandInfo['bg_sky_id_4'] = 20;
        	$islandInfo['bg_sea_4'] = 22213;
        	$islandInfo['bg_sea_id_4'] = 21;
        	//get user next island level
        	$nextIslandLevel = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfoByUserLevel($userVo['level'], 4);
        	$userLevelInfo['island_level_4'] = $nextIslandLevel;
        }
        $islandInfo['unlock_island'] = $newUnlockIsland;
        $islandInfo['current_island'] = $islandId;

//		if ( $islandId == 3 || $islandId == 4 ) {
//			$priceType = 1;
//		}

        //price type 0：金币,1：宝石
        if ( $priceType == 1 ) {
			$balanceInfo = Hapyfish2_Island_Bll_Gold::get($uid, true);
        	if (!$balanceInfo) {
	        	$result['content'] = 'serverWord_1002';
	        	return array('resultVo' => $result);
	        }

	        //owner platform info
	       $gold = $balanceInfo['balance'];
	       $gold = Hapyfish2_Island_HFC_User::getUserGold($uid);
			if ($gold < $openIslandInfo['needGold'] ) {
				$resultVo['content'] = 'serverWord_140';
				return $resultVo;
			}

			$userLevel = $userVo['level'];
			$itemId = $islandId . '06';

			require_once(CONFIG_DIR . '/language.php');

			$payInfo = array(
				'uid' => $uid,
				'cost' => $openIslandInfo['needGold'],
			    'summary' => LANG_PLATFORM_BASE_TXT_17 . $openIslandInfo['name'],
				'user_level' => $userLevel,
				'cid' => $itemId,
				'num' => 1
			);
			$ok = Hapyfish2_Island_Bll_Gold::consume($uid, $payInfo);
			if ($ok) {
				$ok2 = Hapyfish2_Island_HFC_User::updateFieldUserIsland($uid, $islandInfo);
		        $resultVo['status'] = 1;
		        $resultVo['goldChange'] = -$openIslandInfo['needGold'];
				if ($ok2) {
				}
			} else {
				info_log(json_encode($payInfo), 'payorder_failure');
				$resultVo['content'] = 'serverWord_110';
				return $resultVo;
			}
        }
        else {
			if ($userVo['coin'] < $openIslandInfo['needCoin'] ) {
				$resultVo['content'] = 'serverWord_137';
				return $resultVo;
			}

			$ok = Hapyfish2_Island_HFC_User::updateFieldUserIsland($uid, $islandInfo);
			if (!$ok) {
				$resultVo['content'] = 'serverWord_110';
				return $resultVo;
			}

			$ok2 = Hapyfish2_Island_HFC_User::decUserCoin($uid, $openIslandInfo['needCoin']);
			if ($ok2) {
				//add log
				$summary = LANG_PLATFORM_BASE_TXT_17 . $openIslandInfo['name'];
				Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $openIslandInfo['needCoin'], $summary, time());
			} else {
				info_log(json_encode($openIslandInfo), 'open_island');
			}

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_14', $shipInfo['coin']);

	        $resultVo['status'] = 1;
	        $resultVo['coinChange'] = -$openIslandInfo['needCoin'];
        }

        if ( $resultVo['status'] == 1 ) {
        	Hapyfish2_Island_HFC_User::updateUserLevel($uid, $userLevelInfo);

        	$now = time();
        	switch ( $islandId ) {
        		case 2 :
		        	$newBackground1 = array( 'uid' => $uid, 'bgid' => $bgIsland2, 'buy_time' => $now, 'item_type' => 11);
		        	$newBackground2 = array( 'uid' => $uid, 'bgid' => 23212, 'buy_time' => $now, 'item_type' => 12);
		        	$newBackground3 = array( 'uid' => $uid, 'bgid' => 22213, 'buy_time' => $now, 'item_type' => 13);
		        	$newPlant = array('uid' => $uid, 'cid' => 87032, 'status' => 0, 'item_id' => 870, 'level' => 3, 'buy_time' => $now, 'item_type' => 32);
		        	break;
        		case 3 :
		        	$newBackground1 = array( 'uid' => $uid, 'bgid' => $bgIsland3, 'buy_time' => $now, 'item_type' => 11);
		        	$newBackground2 = array( 'uid' => $uid, 'bgid' => 23212, 'buy_time' => $now, 'item_type' => 12);
		        	$newBackground3 = array( 'uid' => $uid, 'bgid' => 22213, 'buy_time' => $now, 'item_type' => 13);
		        	$newPlant = array('uid' => $uid, 'cid' => 87532, 'status' => 0, 'item_id' => 875, 'level' => 3, 'buy_time' => $now, 'item_type' => 32);
        			break;
        		case 4 :
		        	$newBackground1 = array( 'uid' => $uid, 'bgid' => $bgIsland4, 'buy_time' => $now, 'item_type' => 11);
		        	$newBackground2 = array( 'uid' => $uid, 'bgid' => 23212, 'buy_time' => $now, 'item_type' => 12);
		        	$newBackground3 = array( 'uid' => $uid, 'bgid' => 22213, 'buy_time' => $now, 'item_type' => 13);
		        	$newPlant = array('uid' => $uid, 'cid' => 87832, 'status' => 0, 'item_id' => 878, 'level' => 3, 'buy_time' => $now, 'item_type' => 32);
        			break;
        	}

        	Hapyfish2_Island_Cache_Background::addNewBackground($uid, $newBackground1);
        	Hapyfish2_Island_Cache_Background::addNewBackground($uid, $newBackground2);
        	Hapyfish2_Island_Cache_Background::addNewBackground($uid, $newBackground3);
        	Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant);

        	try {
        		//$priceType, 1：宝石，2：金币
        		if ( $priceType == 1 ) {
        			$priceTypeLog = 2;
        			$price = $openIslandInfo['needGold'];
        		}
        		else {
        			$priceTypeLog = 1;
        			$price = $openIslandInfo['needCoin'];
        		}
	            //add log
				$logger = Hapyfish2_Util_Log::getInstance();
				$logger->report('301', array($uid, $islandId, $priceTypeLog, $price));
        	} catch (Exception $e) {
        	}

        	/*if ( $islandId == 2 ) {
        		$taskId = 3088;
        		$numField = 'num_34';
        	}
        	else if ( $islandId == 3 ) {
        		$taskId = 3089;
        		$numField = 'num_35';
        	}
        	else if ( $islandId == 4 ) {
        		$taskId = 3090;
        		$numField = 'num_36';
        	}

	        //update achievement task,3088
	        try {
	        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, $numField, 1);
				//task id $taskId,task type $numField
				$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, $taskId);
				if ( $checkTask['status'] == 1 ) {
					$resultVo['finishTaskId'] = $checkTask['finishTaskId'];
				}
				$resultVo['status'] = 1;
	        } catch (Exception $e) {
	        }

	        //check unlock all island
	        $newUnlockIslandInfo = split(',', $islandInfo['unlock_island']);
	        if ( count($newUnlockIslandInfo) >= 4 ) {
		        //update achievement task,3091
		        try {
		        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_37', 1);
					//task id 3091,task type num_37
					$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3091);
					if ( $checkTask['status'] == 1 ) {
						$resultVo['finishTaskId'] = $checkTask['finishTaskId'];
					}
					$resultVo['status'] = 1;
		        } catch (Exception $e) {
		        }
	        }*/
        }

		return $resultVo;
	}

	/**
	 * change island tip
	 *
	 * @param int $uid
	 * @param int $mapIconState
	 */
	public static function changeIslandTip($uid, $mapIconState)
	{
		$result = array('status' => -1);
		if ( !in_array($mapIconState, array('0', '1')) ) {
			return $result;
		}

		Hapyfish2_Island_Cache_User::setIslandTip($uid, $mapIconState);

		$result['status'] = 1;
		return $result;

	}

}