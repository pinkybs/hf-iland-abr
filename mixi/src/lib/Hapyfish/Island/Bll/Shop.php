<?php

class Hapyfish_Island_Bll_Shop
{
    /**
     * load shop info
     *
     * @return array
     */
    public static function loadShop()
    {
        return Hapyfish_Island_Cache_Shop::getShopList();
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
        $result = array('status' => -1,
                        'content' => 'serverWord_147',
                        'coinChange' => 0,
                        'goldChange' => 0);

        for ( $i=0,$iCount=count($itemArray); $i<$iCount; $i++ ) {
            $saleResult = self::saleItem($uid, $itemArray[$i]['id']);
            if ( $saleResult['status'] == 1 ) {
            	$result['status'] = 1;
                $result['coinChange'] += $saleResult['coinChange'];
                $result['goldChange'] += $saleResult['goldChange'];
                $result['itemBoxChange'] = $saleResult['itemBoxChange'];
                $result['islandChange'] = $saleResult['islandChange'];
            }
        }
        if ($result['status'] != 1) {
            if ( isset($saleResult['content']) ) {
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
    	$result = array('status' => -1,
    	                'content' => 'serverWord_147');
    	
        //get item type
        $itemType = substr($id, -2, 1);
        $id = substr($id, 0, -2);

        //type
        //1x : background
        //2x : building
        //3x : plant
        //4x : card
        if ( $itemType == 1 ) {
            $result = self::saleBackground($uid, $id);
        }
        else if ( $itemType == 2 ){
            $id = substr($id, 0, -1);
            $result = self::saleBuilding($uid, $id);
        }
        else if ( $itemType == 3 ) {
            $id = substr($id, 0, -1);
        	$result = self::salePlant($uid, $id);
        }
        else if ( $itemType == 4 ) {
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

        $dalCard = Dal_Island_Card::getDefaultInstance();
        //get user card by id
        $userCard = $dalCard->getUserCardById($id);

        if ( $userCard['uid'] != $uid || $userCard['count'] < 1 ) {
            return $result;
        }

        //get card info
        $cardInfo = Hapyfish_Island_Cache_Shop::getCardById($userCard['cid']);

        try {
            //delete user card by id
            $dalCard->deleteUserCardById($id);
        	
            //
            Hapyfish_Island_Cache_User::incCoin($uid, $cardInfo['sale_price']);

            $result['status'] = 1;
            $result['coinChange'] = $cardInfo['sale_price'];
            $result['goldChange'] = 0;
            $result['itemBoxChange'] = true;
            $result['islandChange'] = false;
        }
        catch (Exception $e) {
            info_log('[saleCard]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Shop');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
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

        $dalBuilding = Dal_Island_Building::getDefaultInstance();
        //get user Background by id
        $userBackground = $dalBuilding->getUserBackgroundById($id);

        if ( $userBackground['uid'] != $uid ) {
            return $result;
        }

        //get Background info
        $backgroundInfo = Hapyfish_Island_Cache_Shop::getBackgroundById($userBackground['bgid']);

        try {
            //delete user background by id
            $dalBuilding->deleteUserBackgroundById($id);
            
            //
            Hapyfish_Island_Cache_User::incCoin($uid, $backgroundInfo['sale_price']);

            $result['status'] = 1;
            $result['coinChange'] = $backgroundInfo['sale_price'];
            $result['goldChange'] = 0;
            $result['itemBoxChange'] = true;
            
            if ( $userBackground['status'] == 1 ) {
                $result['islandChange'] = true;
                //clear user background cache
                Hapyfish_Island_Cache_Background::cleanUsingBackground($uid);
            }
            else {
                $result['islandChange'] = false;
            }
        }
        catch (Exception $e) {
            info_log('[saleBackground]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Shop');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
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
        
        $dalBuilding = Dal_Island_Building::getDefaultInstance();
        //get user Building by id
        $userBuilding = $dalBuilding->getUserBuildingById($id, $uid);

        if ( $userBuilding['uid'] != $uid ) {
            return $result;
        }

        //get Building info
        $buildingInfo = Hapyfish_Island_Cache_Shop::getBuildingById($userBuilding['bid']);

        try {
            //delete user Building by id
            $dalBuilding->deleteUserBuildingById($id, $uid);

            //
            Hapyfish_Island_Cache_User::incCoin($uid, $buildingInfo['sale_price']);

            if ($userBuilding['status'] == 1 ) {
            	Hapyfish_Island_Cache_User::updatePraise($uid, -$buildingInfo['add_praise']);
            }
            
            $result['status'] = 1;
            $result['coinChange'] = $buildingInfo['sale_price'];
            $result['goldChange'] = 0;
            $result['itemBoxChange'] = true;
            
            if ( $userBuilding['status'] == 1 ) {
                $result['islandChange'] = true;
                //clear user building cache
                Hapyfish_Island_Cache_Building::cleanUsingBuilding($uid);
            }
            else {
                $result['islandChange'] = false;
            }
        }
        catch (Exception $e) {
            info_log('[saleBuilding]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Shop');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
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

        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        //get user plant by id
        $userPlant = $dalPlant->getUserPlantById($id, $uid);

        if ( $userPlant['uid'] != $uid ) {
            return $result;
        }

        //get Plant info
        $plantInfo = Hapyfish_Island_Cache_Shop::getPlantById($userPlant['bid']);

        try {
            //delete user Plant by id
            $dalPlant->deleteUserPlantById($id, $uid);
            
            //
            Hapyfish_Island_Cache_User::incCoin($uid, $plantInfo['sale_price']);
        
            if ( $userPlant['status'] == 1 ) {
            	Hapyfish_Island_Cache_User::updatePraise($uid, -$plantInfo['add_praise']);
            }
            
            $result['status'] = 1;
            $result['coinChange'] = $plantInfo['sale_price'];
            $result['goldChange'] = 0;
            $result['itemBoxChange'] = true;
            
            if ( $userPlant['status'] == 1 ) {
                $result['islandChange'] = true;
            }
            else {
                $result['islandChange'] = false;
            }
            
            //clear user plant cache
            Bll_Cache_Island_User::clearCache('getListIslandPlant', $uid);
            Bll_Cache_Island_User::clearCache('getUserPlantList', $uid);
            
            Hapyfish_Island_Cache_Plant::cleanUserUsingPlantBasicInfo($uid);
            Hapyfish_Island_Cache_Plant::cleanUserPlantIds($uid);
            Hapyfish_Island_Cache_Plant::cleanUserPlantPayInfoById($uid, $id);
            
        }
        catch (Exception $e) {
            info_log('[salePlant]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Shop');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
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
        	debug_log('itemType: ' . $itemType);
        	if ( $itemType == 1 ) {
        		//get buildinfo by bid
	            $bgInfo = Hapyfish_Island_Cache_Shop::getBackgroundById($item['cid']);

	            if ( $bgInfo && $bgInfo['can_buy'] == 1 ) {
	                $item['item_type'] = $bgInfo['item_type'];
	                $item['price'] = $bgInfo['price'];
	                $item['price_type'] = $bgInfo['price_type'];
	                $item['name'] = $bgInfo['name'];
	                $item['item_id'] = $bgInfo['bgid'];
	                $item['buy_time'] = $nowTime;
	                $item['count'] = 1;
	                
	                $buyBackgroundAry[] = $item;

	                //add need coin
	                if ( $bgInfo['price_type'] == 1 ) {
	                    $needCoin += $bgInfo['price'];
	                }
	                else if ( $bgInfo['price_type'] == 2 ) {
	                    $needGold += $bgInfo['price'];
	                }
	            }
        	}
        	else if ( $itemType == 2 ) {
	        	//get buildinfo by bid
	            $buildingInfo = Hapyfish_Island_Cache_Shop::getBuildingById($item['cid']);
debug_log('aaaaa');
	            if ( $buildingInfo && $buildingInfo['can_buy'] == 1 ) {
	            	debug_log('bbbbb');
	                $item['item_type'] = $buildingInfo['item_type'];
	                $item['price'] = $buildingInfo['price'];
	                $item['price_type'] = $buildingInfo['price_type'];
	                $item['name'] = $buildingInfo['name'];
	                $item['item_id'] = $buildingInfo['bid'];
	                $item['buy_time'] = $nowTime;
	                $item['add_praise'] = $buildingInfo['add_praise'];
	                $item['count'] = 1;
	                $buyBuildingAry[] = $item;

	                //add need coin
	                if ( $buildingInfo['price_type'] == 1 ) {
	                    $needCoin += $buildingInfo['price'];
	                }
	                else if ( $buildingInfo['price_type'] == 2 ) {
	                    $needGold += $buildingInfo['price'];
	                }
	            }
        	}
        	else if ( $itemType == 3 ) {
	        	//get plant by bid
	            $plantInfo = Hapyfish_Island_Cache_Shop::getPlantById($item['cid']);

	            if ( $plantInfo && $plantInfo['can_buy'] == 1 ) {
	                $item['item_type'] = $plantInfo['item_type'];
	                $item['price'] = $plantInfo['price'];
	                $item['price_type'] = $plantInfo['price_type'];
	                $item['name'] = $plantInfo['level'].'★'.$plantInfo['name'];
	                $item['item_id'] = $plantInfo['item_id'];
	                $item['level'] = $plantInfo['level'];
	                $item['buy_time'] = $nowTime;
	                $item['add_praise'] = $plantInfo['add_praise'];
	                $item['count'] = 1;
	                $buyPlantAry[] = $item;

	                //add need coin
	                if ( $plantInfo['price_type'] == 1 ) {
	                    $needCoin += $plantInfo['price'];
	                }
	                else if ( $plantInfo['price_type'] == 2 ) {
	                    $needGold += $plantInfo['price'];
	                }
	            }
        	}
        }

        $userCoin = Hapyfish_Island_Cache_User::getCoin($uid);
        if ( $userCoin < $needCoin ) {
            $result['content'] = 'serverWord_137';
            return $result;
        }
        
        $userGold = Hapyfish_Island_Cache_User::getGold($uid);
        if ( $userGold < $needGold ) {
            $result['content'] = 'serverWord_140';
            return $result;
        }

        try {

        	$resultBuyBackground = self::buyBackgroundOnIsland($uid, $buyBackgroundAry);
        	
            $resultByBuilding = self::buyBuildingOnIsland($uid, $buyBuildingAry);

            $resultBuyPlant = self::buyPlantOnIsland($uid, $buyPlantAry);

            $praiseChange = $resultByBuilding['praise'] + $resultBuyPlant['praise'];
            if ($praiseChange > 0) {
            	Hapyfish_Island_Cache_User::updatePraise($uid, $praiseChange);
            }
            
            if ( $resultByBuilding['count'] > 0 ) {
                //clear user building cache
                Hapyfish_Island_Cache_Building::cleanUsingBuilding($uid);
            }
            if ( $resultBuyPlant['count'] > 0 ) {
                //clear user plant cache
                Bll_Cache_Island_User::clearCache('getListIslandPlant', $uid);
                Bll_Cache_Island_User::clearCache('getUserPlantList', $uid);
                
            	Hapyfish_Island_Cache_Plant::cleanUserUsingPlantBasicInfo($uid);
            	Hapyfish_Island_Cache_Plant::cleanUserPlantIds($uid);
            }
            if ( $resultBuyBackground['count'] > 0 ) {
                //clear user background cache
                Hapyfish_Island_Cache_Background::cleanUsingBackground($uid);
            }
            
            $costCoin = $resultBuyBackground['coin'] + $resultByBuilding['coin'] + $resultBuyPlant['coin'];
            $costGold = $resultBuyBackground['gold'] + $resultByBuilding['gold'] + $resultBuyPlant['gold'];

            $result['status'] = 1;
            $result['coinChange'] = -$costCoin;
            $result['goldChange'] = -$costGold;
            $result['itemBoxChange'] = true;
            $result['islandChange'] = true;
        }
        catch (Exception $e) {
            info_log('[[buyIslandArray]:'.$e->getMessage(), 'transaction');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }
        
        try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user buy count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'buy_count', 1);

            //update user achievement praise
            $userAchievementPraise = $dalMongoAchievement->getUserAchievementByField($uid, 'num_13');
            $userPraise = Hapyfish_Island_Cache_User::getPraise($uid);
            if ( $userAchievementPraise < $userPraise ) {
                $dalMongoAchievement->updateUserAchievement($uid, array('num_13' => $userPraise));
            }

            //update user achievement plant count
            $buyPlantCount = $resultBuyPlant['count'];
            if ( $buyPlantCount > 0 ) {
                $dalMongoAchievement->updateUserAchievementByField($uid, 'num_17', $buyPlantCount);
            }

            if ( $costCoin > 0 ) {
                //update user buy coin
                $dalMongoAchievement->updateUserAchievementByField($uid, 'num_14', $costCoin);
            }
            if ( $costGold > 0 ) {
                //update user buy gold
                $dalMongoAchievement->updateUserAchievementByField($uid, 'buy_gold', $costGold);
            }
        } catch (Exception $e) {
        }

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

        for ( $i=0,$iCount=count($itemArray); $i<$iCount; $i++ ) {
            //get item type
            $itemType = substr($itemArray[$i]['cid'], -2, 1);
            $type = substr($itemArray[$i]['cid'], -2);
            $cid = $itemArray[$i]['cid'];

            if ( $itemArray[$i]['num'] < 1 || !is_int($itemArray[$i]['num'])  ) {
                return $result;
            }

            //type,1x->background,2x->building,3x->plant,4x->card
            
            //background
            if ( $itemType == 1 ) {
                $bgInfo = Hapyfish_Island_Cache_Shop::getBackgroundById($cid);
                if ( !$bgInfo || $bgInfo['can_buy'] != 1 ) {
                    $reuslt['content'] = 'serverWord_148';
                    return $result;
                }
                if ( $bgInfo['price_type'] == 1 ) {
                    $needCoin += $bgInfo['price'] * $itemArray[$i]['num'];
                }
                else if ( $bgInfo['price_type'] == 2 ) {
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
            else if ( $itemType == 2 ) {
                $buildingInfo = Hapyfish_Island_Cache_Shop::getBuildingById($cid);
                if ( !$buildingInfo || $buildingInfo['can_buy'] != 1 ) {
                    $reuslt['content'] = 'serverWord_148';
                    return $result;
                }
                if ( $buildingInfo['price_type'] == 1 ) {
                    $needCoin += $buildingInfo['price'] * $itemArray[$i]['num'];
                }
                else if ( $buildingInfo['price_type'] == 2 ) {
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
            else if ( $itemType == 3 ) {
                $plantInfo = Hapyfish_Island_Cache_Shop::getPlantById($cid);

                if ( !$plantInfo || $plantInfo['can_buy'] != 1 ) {
                    $reuslt['content'] = 'serverWord_148';
                    return $result;
                }
                if ( $plantInfo['price_type'] == 1 ) {
                    $needCoin += $plantInfo['price'] * $itemArray[$i]['num'];
                }
                else if ( $plantInfo['price_type'] == 2 ) {
                    $needGold += $plantInfo['price'] * $itemArray[$i]['num'];
                }
                
                $plantArray[] = array(
                	'cid' => $cid, 
                	'item_id' => $cid,
                	'item_type' => $type,
                	'name' => $plantInfo['level'].'★'.$plantInfo['name'],
                	'count' => 1,
                	'price' => $plantInfo['price'],
                	'level' => $plantInfo['level'],
                	'price_type' => $plantInfo['price_type'],
                	'item_id' => $plantInfo['item_id'],
                	'buy_time' => $now
                );
                
                $buyCount = 1;
            }
            else if ( $itemType == 4 ) {
                $cardInfo = Hapyfish_Island_Cache_Shop::getCardById($cid);
                
                if ( !$cardInfo || $cardInfo['can_buy'] != 1 ) {
                    $reuslt['content'] = 'serverWord_148';
                    return $result;
                }
                if ( $cardInfo['price_type'] == 1 ) {
                    $needCoin += $cardInfo['price'] * $itemArray[$i]['num'];
                }
                else if ( $cardInfo['price_type'] == 2 ) {
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

        $userCoin = Hapyfish_Island_Cache_User::getCoin($uid);
        if ($userCoin < $needCoin) {
            $result['content'] = 'serverWord_137';
            return $result;
        }
        $userGold = Hapyfish_Island_Cache_User::getGold($uid);
        if ($userGold < $needGold) {
            $result['content'] = 'serverWord_140';
            return $result;
        }

        try {
            $resultOfBackground = self::buyBackgroundInWarehouse($uid, $backgroundArray);

            $resultOfBuilding = self::buyBuildingInWarehouse($uid, $buildingArray);

            $resultOfPlant = self::buyPlantInWarehouse($uid, $plantArray);

            $resultOfCard = self::buyCard($uid, $cardArray);
        
            $costCoin = $resultOfBackground['coin'] + $resultOfBuilding['coin'] + $resultOfPlant['coin'] + $resultOfCard['coin'];
            $costGold = $resultOfBackground['gold'] + $resultOfBuilding['gold'] + $resultOfPlant['gold'] + $resultOfCard['gold'];
            
            if ( $resultOfBackground['count'] > 0 ) {
            	//clear user background cache
                Hapyfish_Island_Cache_Background::cleanUsingBackground($uid);
            }

            $result['status'] = 1;
            $result['coinChange'] = -$costCoin;
            $result['goldChange'] = -$costGold;
            $result['itemBoxChange'] = true;
            $result['islandChange'] = true;
        }
        catch (Exception $e) {
            info_log('[buyItemArray]:'.$e->getMessage(), 'transaction');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }
        
        try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user achievement plant count
            $plantArrayCount = $resultOfPlant['count'];
            if ( $plantArrayCount > 0 ) {
                $dalMongoAchievement->updateUserAchievementByField($uid, 'num_17', $plantArrayCount);
            }
            if ( $buyCount == 1 ) {
                //update user buy count
                $dalMongoAchievement->updateUserAchievementByField($uid, 'buy_count', 1);
            }
            if ( $costCoin > 0 ) {
                //update user buy coin
                $dalMongoAchievement->updateUserAchievementByField($uid, 'num_14', $costCoin);
            }
            if ( $costGold > 0 ) {
                //update user buy gold
                $dalMongoAchievement->updateUserAchievementByField($uid, 'buy_gold', $costGold);
            }
        } catch (Exception $e) {

        }

        return $result;
    }

    public static function buyBackgroundOnIsland($uid, $buyBackgroundAry)
    {
		$result = array ('coin' => 0, 'gold' => 0, 'count' => 0);
		
    	$dalBuilding = Dal_Island_Building::getDefaultInstance();
    	$dalGold = Dal_Island_Gold::getDefaultInstance();
		
		foreach ($buyBackgroundAry as $background) {
			$newBackground = array(
				'uid' => $uid,
				'bgid' => $background['cid'],
				'status' => 1,
				'buy_time' => $background['buy_time'],
				'item_type' => $background['item_type']
			);
			
			//add user building
			$price = $background['price'];

			try {
				//add user background
				$dalBuilding->addUserBackground($uid, $newBackground);
				
				//coin
				if ($background['price_type'] == 1) {
					Hapyfish_Island_Cache_User::decCoin($uid, $price);
					$result['coin'] += $price;
				}
				//gold
				else if ($background['price_type'] == 2) {
					Hapyfish_Island_Cache_User::decGold($uid, $price);
					$result['gold'] += $price;
					
				    //add user gold cost log info
                	$userGoldInfo = array(
                		'uid' => $uid,
                        'content' => '[buyBackground]:' . $background['cid'],
                		'gold' => $price,
                		'item_id' => $background['item_id'],
                		'name' => $background['name'],
                		'count' => $background['count'],
                        'create_time' => $background['buy_time']
                	);
                	$dalGold->insertUserGoldInfo($userGoldInfo);
				}
				
				$result['count']++;
			}catch (Exception $e) {
				
			}
    	}
    	
    	return $result;
    }
    
    public static function buyBackgroundInWarehouse($uid, $backgroundArray)
    {
    	$result = array ('coin' => 0, 'gold' => 0, 'count' => 0);
    	$dalBuilding = Dal_Island_Building::getDefaultInstance();
    	$dalGold = Dal_Island_Gold::getDefaultInstance();
    	
    	foreach ($backgroundArray as $background) {
			$newBackground = array(
				'uid' => $uid,
				'bgid' => $background['cid'],
				'status' => 1,
				'buy_time' => $background['buy_time'],
				'item_type' => $background['item_type']
			);
			
			$price = $background['price'];

			try {
				//add user background
				$dalBuilding->addUserBackground($uid, $newBackground);
				
				//coin
				if ($background['price_type'] == 1) {
					Hapyfish_Island_Cache_User::decCoin($uid, $price);
					$result['coin'] += $price;
				}
				//gold
				else if ($background['price_type'] == 2) {
					Hapyfish_Island_Cache_User::decGold($uid, $price);
					$result['gold'] += $price;
					
				    //add user gold cost log info
                	$userGoldInfo = array(
                		'uid' => $uid,
                        'content' => '[buyBackground]:' . $background['cid'],
                		'gold' => $price,
                		'item_id' => $background['item_id'],
                		'name' => $background['name'],
                		'count' => $background['count'],
                        'create_time' => $background['buy_time']
                	);
                	$dalGold->insertUserGoldInfo($userGoldInfo);
				}
				
				$result['count']++;
			}catch (Exception $e) {
				
			}
    	}
    	
    	return $result;
    }
    
    public static function buyBuildingOnIsland($uid, $buyBuildingAry)
    {
        $result = array ('coin' => 0, 'gold' => 0, 'count' => 0, 'praise' => 0);
        
    	$dalBuilding = Dal_Island_Building::getDefaultInstance();
    	$dalGold = Dal_Island_Gold::getDefaultInstance();
    	
        foreach ($buyBuildingAry as $building) {
			$newBuilding = array(
				'uid' => $uid,
				'bid' => $building['cid'],
				'x' => $building['x'],
				'y' => $building['y'],
				'z' => $building['z'],
				'mirro' => $building['mirro'],
				'can_find' => $building['canFind'],
				'status' => 1,
				'buy_time' => $building['buy_time'],
				'item_type' => $building['item_type']
			);
			
			$price = $building['price'];
			
			try {
                //add user building
                $dalBuilding->addUserBuilding($newBuilding);
                
				//coin
				if ($building['price_type'] == 1) {
					Hapyfish_Island_Cache_User::decCoin($uid, $price);
					$result['coin'] += $price;
				}
				//gold
				else if ($building['price_type'] == 2) {
					Hapyfish_Island_Cache_User::decGold($uid, $price);
					$result['gold'] += $price;
					
				    //add user gold cost log info
                	$userGoldInfo = array(
                		'uid' => $uid,
                        'content' => '[buyBuilding]:' . $building['cid'],
                		'gold' => $price,
                		'item_id' => $building['item_id'],
                		'name' => $building['name'],
                		'count' => $building['count'],
                        'create_time' => $building['buy_time']
                	);
                	$dalGold->insertUserGoldInfo($userGoldInfo);
				}
				
				$result['count']++;
				$result['praise'] += $building['add_praise'];
				
			}catch (Exception $e) {
				
			}
        }
        
        return $result;
    }
    
    public static function buyBuildingInWarehouse($uid, $buildingArray)
    {
        $result = array ('coin' => 0, 'gold' => 0, 'count' => 0);
        
    	$dalBuilding = Dal_Island_Building::getDefaultInstance();
    	$dalGold = Dal_Island_Gold::getDefaultInstance();
    	
    	foreach ($buildingArray as $building) {
        	$newBuilding = array(
        		'uid' => $uid,
				'bid' => $building['cid'],
				'status' => 0,
				'buy_time' => $building['buy_time'],
				'item_type' => $building['item_type']);
        	
        	$price = $building['price'];
            
    		try {
            	//add user Building
            	$dalBuilding->addUserBuilding($newBuilding);

				//coin
				if ($building['price_type'] == 1) {
					Hapyfish_Island_Cache_User::decCoin($uid, $price);
					$result['coin'] += $price;
				}
				//gold
				else if ($building['price_type'] == 2) {
					Hapyfish_Island_Cache_User::decGold($uid, $price);
					$result['gold'] += $price;
					
				    //add user gold cost log info
                	$userGoldInfo = array(
                		'uid' => $uid,
                        'content' => '[buyBuilding]:' . $building['cid'],
                		'gold' => $price,
                		'item_id' => $building['item_id'],
                		'name' => $building['name'],
                		'count' => $building['count'],
                        'create_time' => $building['buy_time']
                	);
                	$dalGold->insertUserGoldInfo($userGoldInfo);
				}
				
				$result['count']++;
			}catch (Exception $e) {
				
			}
    	}
    	
    	return $result;
    }
    
    public static function buyPlantOnIsland($uid, $buyPlantAry)
    {
    	$result = array ('coin' => 0, 'gold' => 0, 'count' => 0, 'praise' => 0);
    	
    	$dalPlant = Dal_Island_Plant::getDefaultInstance();
    	$dalGold = Dal_Island_Gold::getDefaultInstance();
    	
    	foreach ($buyPlantAry as $plant) {
			$newPlant = array(
				'uid' => $uid,
				'bid' => $plant['cid'],
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
			
    	    try {
            	//add user plant
            	$dalPlant->insertUserPlant($newPlant);
				
            	$price = $plant['price'];
				//coin
				if ($plant['price_type'] == 1) {
					Hapyfish_Island_Cache_User::decCoin($uid, $price);
					$result['coin'] += $price;
				}
				//gold
				else if ($plant['price_type'] == 2) {
					Hapyfish_Island_Cache_User::decGold($uid, $price);
					$result['gold'] += $price;
					
				    //add user gold cost log info
                	$userGoldInfo = array(
                		'uid' => $uid,
                        'content' => '[buyPlant]:' . $plant['cid'],
                		'gold' => $price,
                		'item_id' => $plant['cid'],
                		'name' => $plant['name'],
                		'count' => $plant['count'],
                        'create_time' => $plant['buy_time']
                	);
                	$dalGold->insertUserGoldInfo($userGoldInfo);
				}
				
				$result['count']++;
				$result['praise'] += $plant['add_praise'];
			}catch (Exception $e) {
				
			}
    	}
    	
    	return $result;
    }
    
    public static function buyPlantInWarehouse($uid, $plantArray)
    {
        $result = array ('coin' => 0, 'gold' => 0, 'count' => 0);
        
    	$dalPlant = Dal_Island_Plant::getDefaultInstance();
    	$dalGold = Dal_Island_Gold::getDefaultInstance();
    	
    	foreach ($plantArray as $plant) {
        	$newPlant = array(
        		'uid' => $uid,
				'bid' => $plant['cid'],
				'status' => 0,
        		'item_id' => $plant['item_id'],
        		'level' => $plant['level'],
				'buy_time' => $plant['buy_time'],
				'item_type' => $plant['item_type']);
        	
    	    try {
            	//add user plant
            	$dalPlant->insertUserPlant($newPlant);
				
            	$price = $plant['price'];
				//coin
				if ($plant['price_type'] == 1) {
					Hapyfish_Island_Cache_User::decCoin($uid, $price);
					$result['coin'] += $price;
				}
				//gold
				else if ($plant['price_type'] == 2) {
					Hapyfish_Island_Cache_User::decGold($uid, $price);
					$result['gold'] += $price;
					
				    //add user gold cost log info
                	$userGoldInfo = array(
                		'uid' => $uid,
                        'content' => '[buyPlant]:' . $plant['cid'],
                		'gold' => $price,
                		'item_id' => $plant['item_id'],
                		'name' => $plant['name'],
                		'count' => $plant['count'],
                        'create_time' => $plant['buy_time']
                	);
                	$dalGold->insertUserGoldInfo($userGoldInfo);
				}
				
				$result['count']++;
			}catch (Exception $e) {
				
			}
    	}
    	
    	return $result;
    }
    
    public static function buyCard($uid, $cardArray)
    {
        $result = array ('coin' => 0, 'gold' => 0, 'count' => 0);
    	$dalCard = Dal_Island_Card::getDefaultInstance();
    	$dalGold = Dal_Island_Gold::getDefaultInstance();
    	
    	foreach ($cardArray as $card) {
        	$newCard = array(
        		'uid' => $uid,
				'cid' => $card['cid'],
        		'count' => $card['count'],
				'buy_time' => $card['buy_time'],
				'item_type' => $card['item_type']);
        	
    	   	try {
            	//add user card
            	$dalCard->addUserCard($newCard);
				
            	$totalPrice = $card['price'] * $card['count'];
				//coin
				if ($card['price_type'] == 1) {
					Hapyfish_Island_Cache_User::decCoin($uid, $totalPrice);
					$result['coin'] += $totalPrice;
				}
				//gold
				else if ($card['price_type'] == 2) {
					Hapyfish_Island_Cache_User::decGold($uid, $totalPrice);
					$result['gold'] += $totalPrice;
					
				    //add user gold cost log info
                	$userGoldInfo = array(
                		'uid' => $uid,
                        'content' => '[buyCard]:' . $card['cid'],
                		'gold' => $totalPrice,
                		'item_id' => $card['item_id'],
                		'name' => $card['name'],
                		'count' => $card['count'],
                        'create_time' => $card['buy_time']
                	);
                	$dalGold->insertUserGoldInfo($userGoldInfo);
				}
				
				$result['count']++;
			}catch (Exception $e) {
				
			}
    	}
    	
    	return $result;
    }
}