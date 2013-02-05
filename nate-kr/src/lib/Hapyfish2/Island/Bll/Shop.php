<?php
require_once(CONFIG_DIR . '/language.php');
class Hapyfish2_Island_Bll_Shop
{
    /**
     * load shop info
     *
     * @return array
     */
    public static function loadShop()
    {
        return Hapyfish2_Island_Cache_Shop::getCanBuyList();
    }

    /**
     * sale item array
     *
     * @param integer $uid
     * @param array $itemArray
     * @return array
     */
    public static function saleItemArray($uid, $itemArray)
    {
        $result = array(
			'status' => -1,
			'content' => 'serverWord_147',
			'coinChange' => 0,
			'goldChange' => 0
        );

        for($i=0, $iCount=count($itemArray); $i < $iCount; $i++) {
            $saleResult = self::saleItem($uid, $itemArray[$i]['id']);
            if ($saleResult['status'] == 1) {
            	$result['status'] = 1;
                $result['coinChange'] += $saleResult['coinChange'];
                $result['goldChange'] += $saleResult['goldChange'];
                $result['itemBoxChange'] = $saleResult['itemBoxChange'];
                $result['islandChange'] = $saleResult['islandChange'];
            }
        }
        if ($result['status'] != 1) {
            if (isset($saleResult['content'])) {
            	$result['content'] = $saleResult['content'];
            }
        }

        return $result;
    }

    /**
     * sale item
     *
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public static function saleItem($uid, $id)
    {
    	$result = array(
			'status' => -1,
			'content' => 'serverWord_147'
    	);

        //get item type
        $itemType = substr($id, -2, 1);
        $id = substr($id, 0, -2);

        //type
        //1x : background
        //2x : building
        //3x : plant
        //4x : card
        if ($itemType == 1) {
            $result = self::saleBackground($uid, $id);
        }
        else if ($itemType == 2){
            $result = self::saleBuilding($uid, $id);
        }
        else if ($itemType == 3) {
        	$result = self::salePlant($uid, $id);
        }
        else if ($itemType == 4) {
            $result = self::saleCard($uid, $id);
        }

        return $result;
    }

    /**
     * sale card
     *
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public static function saleCard($uid, $id)
    {
        $result = array('status' => -1);

        $cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($id);
        if (!$cardInfo) {
        	return $result;
        }

        $userCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
        if (!$userCard || !isset($userCard[$id]) || $userCard[$id]['count'] < 1) {
        	return $result;
        }

        $userCard[$id]['count'] -= 1;
		$userCard[$id]['update'] = 1;
        $ok = Hapyfish2_Island_HFC_Card::updateUserCard($uid, $userCard);

        if ($ok) {
			Hapyfish2_Island_HFC_User::incUserCoin($uid, $cardInfo['sale_price']);

        	$result['status'] = 1;
        	$result['coinChange'] = $cardInfo['sale_price'];
        	$result['goldChange'] = 0;
        	$result['itemBoxChange'] = true;
        	$result['islandChange'] = false;
        } else {
        	$result['status'] = -1;
        	$result['content'] = 'serverWord_110';
        }

        return $result;
    }

    /**
     * sale background
     *
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public static function saleBackground($uid, $id)
    {
        $result = array('status' => -1);

        $bgs = Hapyfish2_Island_Cache_Background::getInWareHouse($uid);
        if (empty($bgs) || !isset($bgs[$id])) {
        	return $result;
        } else {
        	$bg = $bgs[$id];
        }

        $backgroundInfo = Hapyfish2_Island_Cache_BasicInfo::getBackgoundInfo($bg['bgid']);
        if (!$backgroundInfo) {
        	return $result;
        }

		//delete user background by id
		$ok = Hapyfish2_Island_Cache_Background::delBackground($uid, $id);
		if ($ok) {
			Hapyfish2_Island_HFC_User::incUserCoin($uid, $backgroundInfo['sale_price']);

            $result['status'] = 1;
            $result['coinChange'] = $backgroundInfo['sale_price'];
            $result['goldChange'] = 0;
            $result['itemBoxChange'] = true;
            $result['islandChange'] = false;
		} else {
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
		}

        return $result;
    }

    /**
     * sale building
     *
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public static function saleBuilding($uid, $id)
    {
        $result = array('status' => -1);

        $building = Hapyfish2_Island_HFC_Building::getOne($uid, $id);
        if (!$building) {
			return $result;
        }

        $buildingInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($building['cid']);
        if (!$buildingInfo) {
        	return $result;
        }

        $status = $building['status'];
		//delete user Building
		$ok = Hapyfish2_Island_HFC_Building::delOne($uid, $id, $status);

		if ($ok) {
			Hapyfish2_Island_HFC_User::incUserCoin($uid, $buildingInfo['sale_price']);

			$result['status'] = 1;
            $result['coinChange'] = $buildingInfo['sale_price'];
            $result['goldChange'] = 0;
            $result['itemBoxChange'] = true;

			if ($status == 1) {
				//dec praise
				$praiseChange = -$buildingInfo['add_praise'];
				Hapyfish2_Island_HFC_User::changeIslandPraise($uid, $praiseChange);

				$result['islandChange'] = true;
			} else {
				$result['islandChange'] = false;
			}
		} else {
			$result['status'] = -1;
			$result['content'] = 'serverWord_110';
		}

        return $result;
    }

    /**
     * sale plant
     *
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public static function salePlant($uid, $id)
    {
        $result = array('status' => -1);

		$plant = Hapyfish2_Island_HFC_Plant::getOne($uid, $id, 0);

		if (!$plant) {
			return $result;
		}

        $plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($plant['cid']);
        if (!$plantInfo) {
        	return $result;
        }

		$status = $plant['status'];
		//delete user Plant by id
		$ok = Hapyfish2_Island_HFC_Plant::delOne($uid, $id, $status);

		if ($ok) {
			Hapyfish2_Island_HFC_User::incUserCoin($uid, $plantInfo['sale_price']);

			$result['status'] = 1;
			$result['coinChange'] = $plantInfo['sale_price'];
			$result['goldChange'] = 0;
			$result['itemBoxChange'] = true;

			if ($status == 1 ) {
				//dec praise
				$praiseChange = -$plantInfo['add_praise'];
				Hapyfish2_Island_HFC_User::changeIslandPraise($uid, $praiseChange);

				$result['islandChange'] = true;
			} else {
				$result['islandChange'] = false;
			}

			//clear mooch info
			Hapyfish2_Island_Cache_Mooch::clearMoochPlant($uid, $id);
		} else {
			$result['status'] = -1;
			$result['content'] = 'serverWord_110';
		}

        return $result;
    }

    /**
     * buy island array
     *
     * @param integer $uid
     * @param array $islandArray
     * @return array
     */
    public static function buyIslandArray($uid, $islandArray)
    {
        $result = array('status' => -1);

        $praiseChange = 0;
        $needCoin = 0;
        $needGold = 0;
        $buyBuildingAry = array();
        $buyPlantAry = array();
        $buyBackgroundAry = array();
        $nowTime = time();

        foreach ($islandArray as $item) {

        	$itemType = substr($item['cid'], -2, 1);
        	if ($itemType == 1) {
        		//get background info by cid
        		$bgInfo = Hapyfish2_Island_Cache_BasicInfo::getBackgoundInfo($item['cid']);

	            if ($bgInfo && $bgInfo['can_buy'] == 1) {
	                $item['item_type'] = $bgInfo['item_type'];
	                $item['price'] = $bgInfo['price'];
	                $item['price_type'] = $bgInfo['price_type'];
	                $item['name'] = $bgInfo['name'];
	                $item['item_id'] = $bgInfo['bgid'];
	                $item['buy_time'] = $nowTime;
	                $item['count'] = 1;

	                $buyBackgroundAry[] = $item;

	                //add need coin
	                if ($bgInfo['price_type'] == 1) {
	                    $needCoin += $bgInfo['price'];
	                } else if ($bgInfo['price_type'] == 2) {
	                    $needGold += $bgInfo['price'];
	                }
	            }
        	}
        	else if ($itemType == 2) {
	        	//get building info by cid
	        	$buildingInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($item['cid']);

	            if ($buildingInfo && $buildingInfo['can_buy'] == 1) {
	                $item['item_type'] = $buildingInfo['item_type'];
	                $item['price'] = $buildingInfo['price'];
	                $item['price_type'] = $buildingInfo['price_type'];
	                $item['name'] = $buildingInfo['name'];
	                $item['item_id'] = $buildingInfo['cid'];
	                $item['buy_time'] = $nowTime;
	                $item['add_praise'] = $buildingInfo['add_praise'];
	                $item['count'] = 1;
	                $buyBuildingAry[] = $item;

	                //add need coin
	                if ($buildingInfo['price_type'] == 1) {
	                    $needCoin += $buildingInfo['price'];
	                } else if ($buildingInfo['price_type'] == 2) {
	                    $needGold += $buildingInfo['price'];
	                }
	            }
        	}
        	else if ($itemType == 3) {
	        	//get plant by cid
	        	$plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($item['cid']);

	            if ($plantInfo && $plantInfo['can_buy'] == 1) {
	                $item['item_type'] = $plantInfo['item_type'];
	                $item['price'] = $plantInfo['price'];
	                $item['price_type'] = $plantInfo['price_type'];
	                $item['name'] = $plantInfo['level']. LANG_PLATFORM_BASE_TXT_12 .$plantInfo['name'];
	                $item['item_id'] = $plantInfo['item_id'];
	                $item['level'] = $plantInfo['level'];
	                $item['buy_time'] = $nowTime;
	                $item['add_praise'] = $plantInfo['add_praise'];
	                $item['count'] = 1;
	                $buyPlantAry[] = $item;

	                //add need coin
	                if ($plantInfo['price_type'] == 1) {
	                    $needCoin += $plantInfo['price'];
	                } else if ($plantInfo['price_type'] == 2) {
	                    $needGold += $plantInfo['price'];
	                }
	            }
        	}
        }

        if ($needCoin > 0) {
	        $userCoin = Hapyfish2_Island_HFC_User::getUserCoin($uid);
	        if ($userCoin < $needCoin) {
	            $result['content'] = 'serverWord_137';
	            return $result;
	        }
        }

        $isVip = 0;
        $userLevel = 0;

        if ($needGold > 0) {
	        $balanceInfo = Hapyfish2_Island_Bll_Gold::get($uid, true);
	        if (!$balanceInfo) {
	        	$result['content'] = 'serverWord_1002';
	        	return $result;
	        }

			$userGold = $balanceInfo['balance'];
			if ($userGold < $needGold) {
				$result['content'] = 'serverWord_140';
				return $result;
			}

			$isVip = $balanceInfo['is_vip'];
			$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
			$userLevel = $userLevelInfo['level'];
        }

        $resultBuyBackground = self::buyBackgroundOnIsland($uid, $buyBackgroundAry, $isVip, $userLevel);

		$resultByBuilding = self::buyBuildingOnIsland($uid, $buyBuildingAry, $isVip, $userLevel);

		$resultBuyPlant = self::buyPlantOnIsland($uid, $buyPlantAry, $isVip, $userLevel);

		$praiseChange = $resultByBuilding['praise'] + $resultBuyPlant['praise'];
		if ($praiseChange > 0) {
            $userIsland = Hapyfish2_Island_HFC_User::getUserIsland($uid);
            $praise = $userIsland['praise'];
            Hapyfish2_Island_HFC_User::changeIslandPraise($uid, $praiseChange, $userIsland);
		}

		if ($resultByBuilding['count'] > 0) {
			//clear user building cache
		}
		if ($resultBuyPlant['count'] > 0) {
			//clear user plant cache
		}
		if ($resultBuyBackground['count'] > 0) {
			//clear user background cache
		}

		$costCoin = $resultBuyBackground['coin'] + $resultByBuilding['coin'] + $resultBuyPlant['coin'];
		$costGold = $resultBuyBackground['gold'] + $resultByBuilding['gold'] + $resultBuyPlant['gold'];

		//update user achievement praise
		if ($praiseChange > 0) {
            $userPraise = $praise + $praiseChange;
            $userAchievement = Hapyfish2_Island_HFC_Achievement::getUserAchievement($uid);
            $userAchievementPraise = $userAchievement['num_13'];
            if ($userAchievementPraise < $userPraise) {
            	$userAchievement['num_13'] = $userPraise;
            	Hapyfish2_Island_HFC_Achievement::updateUserAchievement($uid, $userAchievement);
            }
		}

		//update user achievement plant count
		$buyPlantCount = $resultBuyPlant['count'];
		if ($buyPlantCount > 0) {
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_17', $buyPlantCount);
		}

		//update user buy coin
		if ($costCoin > 0) {
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_14', $costCoin);
		}

		$result['status'] = 1;
		$result['coinChange'] = -$costCoin;
		$result['goldChange'] = -$costGold;
		$result['itemBoxChange'] = true;
		$result['islandChange'] = true;

        return $result;
    }


    /**
     * buy item array
     *
     * @param integer $uid
     * @param array $itemArray
     * @return array
     */
    public static function buyItemArray($uid, $itemArray)
    {
        $result = array('status' => -1);

        $backgroundArray = array();
        $buildingArray = array();
        $plantArray = array();
        $cardArray = array();
        $needCoin = 0;
        $needGold = 0;
        $buyCount = 0;
        $now = time();

        for ($i=0, $iCount = count($itemArray); $i<$iCount; $i++ ) {
            //get item type
            $itemType = substr($itemArray[$i]['cid'], -2, 1);
            $type = substr($itemArray[$i]['cid'], -2);
            $cid = $itemArray[$i]['cid'];

            if ($itemArray[$i]['num'] < 1 || !is_int($itemArray[$i]['num'])) {
                return $result;
            }

            //type,1x->background,2x->building,3x->plant,4x->card

            //background
            if ($itemType == 1) {
            	$bgInfo = Hapyfish2_Island_Cache_BasicInfo::getBackgoundInfo($cid);
                if (!$bgInfo || $bgInfo['can_buy'] != 1) {
                    $reuslt['content'] = 'serverWord_148';
                    return $result;
                }
                if ($bgInfo['price_type'] == 1) {
                    $needCoin += $bgInfo['price'] * $itemArray[$i]['num'];
                }
                else if ($bgInfo['price_type'] == 2) {
                    $needGold += $bgInfo['price'] * $itemArray[$i]['num'];
                }
                $backgroundArray[] = array(
                	'cid' => $cid,
                	'item_id' => $cid,
                	'item_type' => $type,
                	'name' => $bgInfo['name'],
                	'price' => $bgInfo['price'],
                	'price_type' => $bgInfo['price_type'],
                	'count' => 1,
                	'buy_time' => $now
                );

                $buyCount = 1;
            }
            //building
            else if ($itemType == 2) {
            	$buildingInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($cid);
                if (!$buildingInfo || $buildingInfo['can_buy'] != 1) {
                    $reuslt['content'] = 'serverWord_148';
                    return $result;
                }
                if ($buildingInfo['price_type'] == 1) {
                    $needCoin += $buildingInfo['price'] * $itemArray[$i]['num'];
                }
                else if ($buildingInfo['price_type'] == 2) {
                    $needGold += $buildingInfo['price'] * $itemArray[$i]['num'];
                }

                $buildingArray[] = array(
                	'cid' => $cid,
                	'item_id' => $cid,
                	'item_type' => $type,
                	'name' => $buildingInfo['name'],
                	'price' => $buildingInfo['price'],
                	'price_type' => $buildingInfo['price_type'],
                	'count' => 1,
                	'buy_time' => $now
                );

                $buyCount = 1;
            }
            else if ($itemType == 3) {
            	$plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($cid);

                if (!$plantInfo || $plantInfo['can_buy'] != 1) {
                    $reuslt['content'] = 'serverWord_148';
                    return $result;
                }
                if ($plantInfo['price_type'] == 1) {
                    $needCoin += $plantInfo['price'] * $itemArray[$i]['num'];
                }
                else if ($plantInfo['price_type'] == 2) {
                    $needGold += $plantInfo['price'] * $itemArray[$i]['num'];
                }

                $plantArray[] = array(
                	'cid' => $cid,
                	'item_type' => $type,
                	'name' => $plantInfo['level'].LANG_PLATFORM_BASE_TXT_12.$plantInfo['name'],
                	'count' => 1,
                	'price' => $plantInfo['price'],
                	'level' => $plantInfo['level'],
                	'price_type' => $plantInfo['price_type'],
                	'item_id' => $plantInfo['item_id'],
                	'buy_time' => $now
                );

                $buyCount = 1;
            }
            else if ($itemType == 4) {
            	$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);

                if (!$cardInfo || $cardInfo['can_buy'] != 1) {
                    $reuslt['content'] = 'serverWord_148';
                    return $result;
                }
                if ($cardInfo['price_type'] == 1) {
                    $needCoin += $cardInfo['price'] * $itemArray[$i]['num'];
                }
                else if ($cardInfo['price_type'] == 2) {
                    $needGold += $cardInfo['price'] * $itemArray[$i]['num'];
                }

                $cardArray[] = array(
                	'cid' => $cid,
                	'item_id' => $cid,
                	'item_type' => $type,
                	'name' => $cardInfo['name'],
                	'price' => $cardInfo['price'],
                	'price_type' => $cardInfo['price_type'],
                	'count' => $itemArray[$i]['num'],
                	'buy_time' => $now
                );
            }
        }

        if ($needCoin == 0 && $needGold == 0) {
        	return $result;
        }

        $isVip = 0;
        $userLevel = 0;

        if ($needCoin > 0) {
	        $userCoin = Hapyfish2_Island_HFC_User::getUserCoin($uid);
	        if ($userCoin < $needCoin) {
	            $result['content'] = 'serverWord_137';
	            return $result;
	        }
        }

        if ($needGold > 0) {
	        $balanceInfo = Hapyfish2_Island_Bll_Gold::get($uid, true);
	        if (!$balanceInfo) {
	        	$result['content'] = 'serverWord_1002';
	        	return $result;
	        }

			$userGold = $balanceInfo['balance'];
			if ($userGold < $needGold) {
				$result['content'] = 'serverWord_140';
				return $result;
			}

			$isVip = $balanceInfo['is_vip'];
			$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
			$userLevel = $userLevelInfo['level'];
        }


		$resultOfBackground = self::buyBackgroundInWarehouse($uid, $backgroundArray, $isVip, $userLevel);

		$resultOfBuilding = self::buyBuildingInWarehouse($uid, $buildingArray, $isVip, $userLevel);

		$resultOfPlant = self::buyPlantInWarehouse($uid, $plantArray, $isVip, $userLevel);

		$resultOfCard = self::buyCard($uid, $cardArray, $isVip, $userLevel);

		$costCoin = $resultOfBackground['coin'] + $resultOfBuilding['coin'] + $resultOfPlant['coin'] + $resultOfCard['coin'];
		$costGold = $resultOfBackground['gold'] + $resultOfBuilding['gold'] + $resultOfPlant['gold'] + $resultOfCard['gold'];

		if ($resultOfBackground['count'] > 0) {
            //clear user background cache
		}

		//update user achievement plant count
		$plantArrayCount = $resultOfPlant['count'];
		if ($plantArrayCount > 0) {
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_17', $plantArrayCount);
		}
		if ($costCoin > 0) {
			//update user buy coin
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_14', $costCoin);
		}

		$result['status'] = 1;
		$result['coinChange'] = -$costCoin;
		$result['goldChange'] = -$costGold;
		$result['itemBoxChange'] = true;
		$result['islandChange'] = true;

        return $result;
    }

    public static function buyBackgroundOnIsland($uid, $buyBackgroundAry, $isVip = 0, $userLevel = 0)
    {
		$result = array ('coin' => 0, 'gold' => 0, 'count' => 0);

		$now = time();
		foreach ($buyBackgroundAry as $background) {
			$newBackground = array(
				'uid' => $uid,
				'bgid' => $background['cid'],
				'buy_time' => $background['buy_time'],
				'item_type' => $background['item_type']
			);

			if ($background['price_type'] == 1) {
				$price = $background['price'];

				$ok = Hapyfish2_Island_Cache_Background::addNewBackgroundOnIsland($uid, $newBackground);
				if ($ok) {
					$ok2 = Hapyfish2_Island_HFC_User::decUserCoin($uid, $price);
					if ($ok2) {
						//add log
						$summary = LANG_PLATFORM_BASE_TXT_13 . $background['name'];
						Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $price, $summary, $now);
						$result['coin'] += $price;
					}
					$result['count']++;
				}
			} else if ($background['price_type'] == 2) {
				$price = $background['price'];
				$ok = Hapyfish2_Island_Cache_Background::addNewBackgroundOnIsland($uid, $newBackground);
				if ($ok) {
		        	$goldInfo = array(
		        		'uid' => $uid,
		        		'cost' => $price,
		        		'summary' => LANG_PLATFORM_BASE_TXT_13 . $background['name'],
		        		'user_level' => $userLevel,
		        		'cid' => $background['cid'],
		        		'num' => 1
		        	);
		        	$ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
		        	if (!$ok2) {

		        	}
					$result['gold'] += $price;
					$result['count']++;
				}
			}

    	}

    	return $result;
    }

    public static function buyBackgroundInWarehouse($uid, $backgroundArray, $isVip = 0, $userLevel = 0)
    {
    	$result = array ('coin' => 0, 'gold' => 0, 'count' => 0);

    	$now = time();
    	foreach ($backgroundArray as $background) {
			$newBackground = array(
				'uid' => $uid,
				'bgid' => $background['cid'],
				'buy_time' => $background['buy_time'],
				'item_type' => $background['item_type']
			);

			if ($background['price_type'] == 1) {
				$price = $background['price'];

				$ok = Hapyfish2_Island_Cache_Background::addNewBackground($uid, $newBackground);
				if ($ok) {
					$ok2 = Hapyfish2_Island_HFC_User::decUserCoin($uid, $price);
					if ($ok2) {
						//add log
						$summary = LANG_PLATFORM_BASE_TXT_13 . $background['name'];
						Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $price, $summary, $now);
						$result['coin'] += $price;
					}
					$result['count']++;
				}
			} else if ($background['price_type'] == 2) {
				$price = $background['price'];

				$ok = Hapyfish2_Island_Cache_Background::addNewBackgroundOnIsland($uid, $newBackground);
				if ($ok) {
		        	$goldInfo = array(
		        		'uid' => $uid,
		        		'cost' => $price,
		        		'summary' => LANG_PLATFORM_BASE_TXT_13 . $background['name'],
		        		'user_level' => $userLevel,
		        		'cid' => $background['cid'],
		        		'num' => 1
		        	);
		        	$ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
		        	if (!$ok2) {

		        	}
					$result['gold'] += $price;
					$result['count']++;
				}
			}
    	}

    	return $result;
    }

    public static function buyBuildingOnIsland($uid, $buyBuildingAry, $isVip = 0, $userLevel = 0)
    {
        $result = array ('coin' => 0, 'gold' => 0, 'count' => 0, 'praise' => 0);

        $now = time();
        foreach ($buyBuildingAry as $building) {
			$newBuilding = array(
				'uid' => $uid,
				'cid' => $building['cid'],
				'x' => $building['x'],
				'y' => $building['y'],
				'z' => $building['z'],
				'mirro' => $building['mirro'],
				'status' => 1,
				'buy_time' => $building['buy_time'],
				'item_type' => $building['item_type']
			);

        	if ($building['price_type'] == 1) {
        		$price = $building['price'];

        		$ok = Hapyfish2_Island_HFC_Building::addOne($uid, $newBuilding);
        		if ($ok) {
					$ok2 = Hapyfish2_Island_HFC_User::decUserCoin($uid, $price);
        			if ($ok2) {
						//add log
						$summary = LANG_PLATFORM_BASE_TXT_13 . $building['name'];
						Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $price, $summary, $now);
						$result['coin'] += $price;
					}
        			$result['count']++;
        			$result['praise'] += $building['add_praise'];
        		}
        	} else if ($building['price_type'] == 2) {
				$price = $building['price'];
		        $payInfo = array(
		        	'amount' => $price,
		        	'is_vip' => $isVip,
		        	'item_id' => $building['cid'],
		        	'item_num' => 1,
		        	'uid' => $uid,
		        	'user_level' => $userLevel
		        );

				$ok = Hapyfish2_Island_HFC_Building::addOne($uid, $newBuilding);
				if ($ok) {
		        	$goldInfo = array(
		        		'uid' => $uid,
		        		'cost' => $price,
		        		'summary' => LANG_PLATFORM_BASE_TXT_13 . $building['name'],
		        		'user_level' => $userLevel,
		        		'cid' => $building['cid'],
		        		'num' => 1
		        	);
		        	$ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
		        	if (!$ok2) {

		        	}

					$result['gold'] += $price;
					$result['count']++;
					$result['praise'] += $building['add_praise'];
				}
        	}
        }

        return $result;
    }

    public static function buyBuildingInWarehouse($uid, $buildingArray, $isVip = 0, $userLevel = 0)
    {
        $result = array ('coin' => 0, 'gold' => 0, 'count' => 0);

        $now = time();
    	foreach ($buildingArray as $building) {
        	$newBuilding = array(
        		'uid' => $uid,
				'cid' => $building['cid'],
				'status' => 0,
				'buy_time' => $building['buy_time'],
				'item_type' => $building['item_type']);

			if ($building['price_type'] == 1) {
        		$price = $building['price'];

        		$ok = Hapyfish2_Island_HFC_Building::addOne($uid, $newBuilding);
        		if ($ok) {
					$ok2 = Hapyfish2_Island_HFC_User::decUserCoin($uid, $price);
        		    if ($ok2) {
						//add log
						$summary = LANG_PLATFORM_BASE_TXT_13 . $building['name'];
						Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $price, $summary, $now);
						$result['coin'] += $price;
					}
        			$result['count']++;
        		}
        	} else if ($building['price_type'] == 2) {
				$price = $building['price'];

				$ok = Hapyfish2_Island_HFC_Building::addOne($uid, $newBuilding);
				if ($ok) {
		        	$goldInfo = array(
		        		'uid' => $uid,
		        		'cost' => $price,
		        		'summary' => LANG_PLATFORM_BASE_TXT_13 . $building['name'],
		        		'user_level' => $userLevel,
		        		'cid' => $building['cid'],
		        		'num' => 1
		        	);
		        	$ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
					if (!$ok2) {

					}

					$result['gold'] += $price;
					$result['count']++;
				}
        	}
    	}

    	return $result;
    }

    public static function buyPlantOnIsland($uid, $buyPlantAry, $isVip = 0, $userLevel = 0)
    {
    	$result = array ('coin' => 0, 'gold' => 0, 'count' => 0, 'praise' => 0);

    	$now = time();
    	foreach ($buyPlantAry as $plant) {
			$newPlant = array(
				'uid' => $uid,
				'cid' => $plant['cid'],
				'item_id' => $plant['item_id'],
				'x' => $plant['x'],
				'y' => $plant['y'],
				'z' => $plant['z'],
				'mirro' => $plant['mirro'],
				'can_find' => $plant['canFind'],
				'level' => $plant['level'],
				'status' => 1,
				'buy_time' => $plant['buy_time'],
				'item_type' => $plant['item_type']
			);

        	if ($plant['price_type'] == 1) {
        		$price = $plant['price'];

        		$ok = Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant);
        		if ($ok) {
					$ok2 = Hapyfish2_Island_HFC_User::decUserCoin($uid, $price);
        		    if ($ok2) {
						//add log
						$summary = LANG_PLATFORM_BASE_TXT_13 . $plant['name'];
						Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $price, $summary, $now);
						$result['coin'] += $price;
					}
        			$result['count']++;
        			$result['praise'] += $plant['add_praise'];
        		}
        	} else if ($plant['price_type'] == 2) {
				$price = $plant['price'];
		        $payInfo = array(
		        	'amount' => $price,
		        	'is_vip' => $isVip,
		        	'item_id' => $plant['cid'],
		        	'item_num' => 1,
		        	'uid' => $uid,
		        	'user_level' => $userLevel
		        );

				$ok = Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant);
				if ($ok) {
		        	$goldInfo = array(
		        		'uid' => $uid,
		        		'cost' => $price,
		        		'summary' => LANG_PLATFORM_BASE_TXT_13 . $plant['name'],
		        		'user_level' => $userLevel,
		        		'cid' => $plant['cid'],
		        		'num' => 1
		        	);
		        	$ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
		        	if (!$ok2) {

		        	}

					$result['gold'] += $price;
					$result['count']++;
					$result['praise'] += $plant['add_praise'];
				}
        	}
    	}

    	return $result;
    }

    public static function buyPlantInWarehouse($uid, $plantArray, $isVip = 0, $userLevel = 0)
    {
        $result = array ('coin' => 0, 'gold' => 0, 'count' => 0);

        $now = time();
        foreach ($plantArray as $plant) {
        	$newPlant = array(
        		'uid' => $uid,
				'cid' => $plant['cid'],
				'status' => 0,
        		'item_id' => $plant['item_id'],
        		'level' => $plant['level'],
				'buy_time' => $plant['buy_time'],
				'item_type' => $plant['item_type']
        	);

        	if ($plant['price_type'] == 1) {
        		$price = $plant['price'];
        		$ok = Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant);
        		if ($ok) {
					Hapyfish2_Island_HFC_User::decUserCoin($uid, $price);
        		    if ($ok2) {
						//add log
						$summary = LANG_PLATFORM_BASE_TXT_13 . $plant['name'];
						Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $price, $summary, $now);
						$result['coin'] += $price;
					}
        			$result['count']++;
        		}
        	} else if ($plant['price_type'] == 2) {
				$price = $plant['price'];
		        $payInfo = array(
		        	'amount' => $price,
		        	'is_vip' => $isVip,
		        	'item_id' => $plant['cid'],
		        	'item_num' => 1,
		        	'uid' => $uid,
		        	'user_level' => $userLevel
		        );

				$ok = Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant);
				if ($ok) {
		        	$goldInfo = array(
		        		'uid' => $uid,
		        		'cost' => $price,
		        		'summary' => LANG_PLATFORM_BASE_TXT_13 . $plant['name'],
		        		'user_level' => $userLevel,
		        		'cid' => $plant['cid'],
		        		'num' => 1
		        	);
		        	$ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
		        	if (!$ok2) {

		        	}

					$result['gold'] += $price;
					$result['count']++;
				}
        	}
        }

    	return $result;
    }

    public static function buyCard($uid, $cardArray, $isVip = 0, $userLevel = 0)
    {
        $result = array ('coin' => 0, 'gold' => 0, 'count' => 0);

        $coinCost = 0;
        $goldCost = 0;
        $summary = '';
        $userCards = Hapyfish2_Island_HFC_Card::getUserCard($uid);
        foreach ($cardArray as $card) {
        	$coinCost = 0;
        	$goldCost = 0;
        	$summary = LANG_PLATFORM_BASE_TXT_13 . $card['name']. 'x' .$card['count'];
        	if (isset($userCards[$card['cid']])) {
        		$userCards[$card['cid']]['count'] += $card['count'];
        		$userCards[$card['cid']]['update'] = 1;
        	} else {
				$userCards[$card['cid']] = array('count' => $card['count'], 'update' => 1);
        	}

			if ($card['price_type'] == 1) {
				$coinCost = $card['price'] * $card['count'];
				$ok = Hapyfish2_Island_HFC_Card::updateUserCard($uid, $userCards);
				if ($ok) {
					$ok2 = Hapyfish2_Island_HFC_User::decUserCoin($uid, $coinCost);
				    if ($ok2) {
						//add log
						Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $coinCost, $summary, time());
						$result['coin'] += $coinCost;
					}
					$result['count']++;
				}
			} else if ($card['price_type'] == 2) {
				$goldCost = $card['price'] * $card['count'];

				$ok = Hapyfish2_Island_HFC_Card::updateUserCard($uid, $userCards, true);
			    if ($ok) {
		        	$goldInfo = array(
		        		'uid' => $uid,
		        		'cost' => $goldCost,
		        		'summary' => $summary,
		        		'user_level' => $userLevel,
		        		'cid' => $card['cid'],
		        		'num' => $card['count']
		        	);
		        	$ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
		        	if (!$ok2) {

		        	}

					$result['gold'] += $goldCost;
					$result['count'] += $card['count'];
				}
			}

        	//only one
        	break;
        }

    	return $result;
    }
}