<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Liz
 */
class Bll_Island_Shop extends Bll_Abstract
{
    /**
     * load shop info
     *
     * @return array
     */
    public function loadShop()
    {
        $result = Bll_Cache_Island::getShopList();
        //$result = $this->getShopList();
        return $result;
    }

    /**
     * get shop list
     *
     * @return array
     */
    public function getShopList()
    {
        $shopList = array();

        //get card list
        $cardList = Bll_Cache_Island::getShopCardList();
        //get island background list
        $backgroundList = Bll_Cache_Island::getShopBackgroundList();
        //get island building list
        $buildingList = Bll_Cache_Island::getShopBuildingList();
        //get island plant list
        $plantList = Bll_Cache_Island::getShopPlantList();

        for ( $i = 0,$iCount = count($cardList); $i < $iCount; $i++ ) {
            $shopList[] = $cardList[$i]['cid'];
        }
        for ( $j = 0,$jCount = count($backgroundList); $j < $jCount; $j++ ) {
            $shopList[] = $backgroundList[$j]['cid'];
        }
        for ( $k = 0,$kCount = count($buildingList); $k < $kCount; $k++ ) {
            $shopList[] = $buildingList[$k]['cid'];
        }
        for ( $l = 0,$lCount = count($plantList); $l < $lCount; $l++ ) {
        	$shopList[] = $plantList[$l]['cid'];
        }

        return $shopList;
    }

    /**
     * sale item array
     *
     * @param integer $uid
     * @param array $itemArray
     * @return array
     */
    public function saleItemArray($uid, $itemArray)
    {
        $result = array('status' => -1,
                        'content' => 'serverWord_147',
                        'coinChange' => 0,
                        'goldChange' => 0);

        for ( $i=0,$iCount=count($itemArray); $i<$iCount; $i++ ) {
            $saleResult = $this->saleItem($uid, $itemArray[$i]['id']);
            if ( $saleResult['status'] == 1 ) {
            	$result['status'] = 1;
                $result['coinChange'] += $saleResult['coinChange'];
                $result['goldChange'] += $saleResult['goldChange'];
                $result['itemBoxChange'] = $saleResult['itemBoxChange'];
                $result['islandChange'] = $saleResult['islandChange'];
            }
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
    public function saleItem($uid, $id)
    {
    	$result = array('status' => -1,
    	                'content' => 'serverWord_147');
        //get item type
        $itemType = substr($id, -2, 1);
        $id = substr($id, 0, -2);

        //type,1x->card,2x->background,3x->building
        if ( $itemType == 1 ) {
            $result = $this->saleBackground($uid, $id);
        }
        else if ( $itemType == 2 ){
            $id = substr($id, 0, -1);
            $result = $this->saleBuilding($uid, $id);
        }
        else if ( $itemType == 3 ) {
            $id = substr($id, 0, -1);
        	$result = $this->salePlant($uid, $id);
        }
        else if ( $itemType == 4 ) {
            $result = $this->saleCard($uid, $id);
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
    public function saleCard($uid, $id)
    {
        $result = array('status' => -1);

        $dalUser = Dal_Island_User::getDefaultInstance();
        $dalCard = Dal_Island_Card::getDefaultInstance();
        //get user card by id
        $userCard = $dalCard->getUserCardById($id);

        if ( $userCard['uid'] != $uid || $userCard['count'] < 1 ) {
            return $result;
        }

        //get card info
        $cardInfo = Bll_Cache_Island::getCardById($userCard['cid']);

        //begin transaction
        $this->_wdb->beginTransaction();
        try {
            //get user for update
            $forUpdateUser = $dalUser->getUserForUpdate($uid);
            //get user card for update
            $forUpdateUserCard = $dalCard->getUserCardByIdForUpdate($id);
            if ( $forUpdateUserCard['count'] < 1 ) {
                $this->_wdb->rollBack();
                return $result;
            }

            //update user
            $newUser = array('coin' => $forUpdateUser['coin'] + $cardInfo['sale_price']);
            $dalUser->updateUser($uid, $newUser);

            //delete user card by id
            $dalCard->deleteUserCardById($id);

            //end of transaction
            $this->_wdb->commit();

            $result['status'] = 1;
            $result['coinChange'] = $cardInfo['sale_price'];
            $result['goldChange'] = 0;
            $result['itemBoxChange'] = true;
            $result['islandChange'] = false;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[saleCard]:'.$e->getMessage(), 'transaction');
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
    public function saleBackground($uid, $id)
    {
        $result = array('status' => -1);

        $dalUser = Dal_Island_User::getDefaultInstance();
        $dalBuilding = Dal_Island_Building::getDefaultInstance();
        //get user Background by id
        $userBackground = $dalBuilding->getUserBackgroundById($id);

        if ( $userBackground['uid'] != $uid ) {
            return $result;
        }

        //get Background info
        $backgroundInfo = Bll_Cache_Island::getBackgroundById($userBackground['bgid']);

        //begin transaction
        $this->_wdb->beginTransaction();
        try {
            //delete user background by id
            $dalBuilding->deleteUserBackgroundById($id);
            
            $dalUser->updateUserByField($uid, 'coin', $backgroundInfo['sale_price']);

            //end of transaction
            $this->_wdb->commit();

            $result['status'] = 1;
            $result['coinChange'] = $backgroundInfo['sale_price'];
            $result['goldChange'] = 0;
            $result['itemBoxChange'] = true;
            if ( $userBackground['status'] == 1 ) {
                $result['islandChange'] = true;
                //clear user background cache
                Bll_Cache_Island_User::clearCache('getUsingBackground', $uid);
            }
            else {
                $result['islandChange'] = false;
            }
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[saleBackground]:'.$e->getMessage(), 'transaction');
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
    public function saleBuilding($uid, $id)
    {
        $result = array('status' => -1);

        $dalUser = Dal_Island_User::getDefaultInstance();
        $dalBuilding = Dal_Island_Building::getDefaultInstance();
        //get user Building by id
        $userBuilding = $dalBuilding->getUserBuildingById($id, $uid);

        if ( $userBuilding['uid'] != $uid ) {
            return $result;
        }

        //get Building info
        $buildingInfo = Bll_Cache_Island::getBuildingById($userBuilding['bid']);

        //begin transaction
        $this->_wdb->beginTransaction();
        try {
            //delete user Building by id
            $dalBuilding->deleteUserBuildingById($id, $uid);
            
            $dalUser->updateUserByField($uid, 'coin', $buildingInfo['sale_price']);

            //end of transaction
            $this->_wdb->commit();

            $result['status'] = 1;
            $result['coinChange'] = $buildingInfo['sale_price'];
            $result['goldChange'] = 0;
            $result['itemBoxChange'] = true;
            if ( $userBuilding['status'] == 1 ) {
                $result['islandChange'] = true;
                //clear user building cache
                Bll_Cache_Island_User::clearCache('getUsingBuilding', $uid);
            }
            else {
                $result['islandChange'] = false;
            }
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[saleBuilding]:'.$e->getMessage(), 'transaction');
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
    public function salePlant($uid, $id)
    {
        $result = array('status' => -1);

        $dalUser = Dal_Island_User::getDefaultInstance();
        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        //get user plant by id
        $userPlant = $dalPlant->getUserPlantById($id, $uid);

        if ( $userPlant['uid'] != $uid ) {
            return $result;
        }

        //get Plant info
        $plantInfo = Bll_Cache_Island::getPlantById($userPlant['bid']);

        //begin transaction
        $this->_wdb->beginTransaction();
        try {
            //delete user Plant by id
            $dalPlant->deleteUserPlantById($id, $uid);
            
            $dalUser->updateUserByField($uid, 'coin', $plantInfo['sale_price']);

            //end of transaction
            $this->_wdb->commit();
            
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
            Bll_Cache_Island_User::clearCache('getUsingPlant', $uid);
            Bll_Cache_Island_User::clearCache('getUserPlantListAll', $uid);
            Bll_Cache_Island_User::clearCache('getListIslandPlant', $uid);
            Bll_Cache_Island_User::clearCache('getUserPlantList', $uid);
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[salePlant]:'.$e->getMessage(), 'transaction');
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
    public function buyIslandArray($uid, $islandArray)
    {
        $result = array('status' => -1);

        $dalBuilding = Dal_Island_Building::getDefaultInstance();
        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get island user
        $islandUser = $dalUser->getUserLevelInfo($uid);

        $praiseChange = 0;
        $needCoin = 0;
        $needGold = 0;
        $buyBuildingAry = array();
        $buyPlantAry = array();
        $buyBackgroundAry = array();
        $buyGoldAry = array();
        $nowTime = time();
        $goldIds = '';
        $backgroundChange = 0;
        $buildingChange = 0;
        $plantChange = 0;

        for ( $i=0,$iCount=count($islandArray); $i<$iCount; $i++ ) {
        	$itemType = substr($islandArray[$i]['cid'], -2, 1);
        	if ( $itemType == 1 ) {
        		//get buildinfo by bid
	            $bgInfo = Bll_Cache_Island::getBackgroundById($islandArray[$i]['cid']);

	            if ( $bgInfo && $bgInfo['can_buy'] == 1 ) {
	                $islandArray[$i]['item_type'] = $bgInfo['item_type'];
	                $buyBackgroundAry[] = $islandArray[$i];

	                //add need coin
	                if ( $bgInfo['price_type'] == 1 ) {
	                    $needCoin += $bgInfo['price'];
	                }
	                else if ( $bgInfo['price_type'] == 2 ) {
	                    $needGold += $bgInfo['price'];
	                    $goldIds .= ','.$islandArray[$i]['cid'];
	                    $buyGoldAry[] = array('name'=>$bgInfo['name'],
	                                          'item_id'=>$bgInfo['bgid'],
                                              'price' => $bgInfo['price']);
	                }
	                $backgroundChange = 1;
	            }
        	}
        	else if ( $itemType == 2 ) {
	        	//get buildinfo by bid
	            $buildingInfo = Bll_Cache_Island::getBuildingById($islandArray[$i]['cid']);

	            if ( $buildingInfo && $buildingInfo['can_buy'] == 1 ) {
	                $islandArray[$i]['item_type'] = $buildingInfo['item_type'];
	                $buyBuildingAry[] = $islandArray[$i];

	                //add need coin
	                if ( $buildingInfo['price_type'] == 1 ) {
	                    $needCoin += $buildingInfo['price'];
	                }
	                else if ( $buildingInfo['price_type'] == 2 ) {
	                    $needGold += $buildingInfo['price'];
	                    $goldIds .= ','.$islandArray[$i]['cid'];
	                    $buyGoldAry[] = array('name'=>$buildingInfo['name'],
	                                          'item_id'=>$buildingInfo['bid'],
                                              'price' => $buildingInfo['price']);
	                }
	                if ( $buildingInfo['add_praise'] ) {
                        $praiseChange += $buildingInfo['add_praise'];
                    }
                    $buildingChange = 1;
	            }
        	}
        	else if ( $itemType == 3 ) {
	        	//get plant by bid
	            $plantInfo = Bll_Cache_Island::getPlantById($islandArray[$i]['cid']);

	            if ( $plantInfo && $plantInfo['can_buy'] == 1 ) {
	                $islandArray[$i]['item_type'] = $plantInfo['item_type'];
	                $buyPlantAry[] = $islandArray[$i];

	                //add need coin
	                if ( $plantInfo['price_type'] == 1 ) {
	                    $needCoin += $plantInfo['price'];
	                }
	                else if ( $plantInfo['price_type'] == 2 ) {
	                    $needGold += $plantInfo['price'];
	                    $goldIds .= ','.$islandArray[$i]['cid'];
	                    $buyGoldAry[] = array('name' => $plantInfo['level'].'星'.$plantInfo['name'],
	                                          'item_id' => $plantInfo['bid'],
	                                          'price' => $plantInfo['price']);
	                }
	                if ( $plantInfo['add_praise'] ) {
                        $praiseChange += $plantInfo['add_praise'];
                    }
                    $plantChange = 1;
	            }
        	}
        }

        if ( $islandUser['coin'] < $needCoin ) {
            $result['content'] = 'serverWord_137';
            return $result;
        }
        else if ( $islandUser['gold'] < $needGold ) {
            $result['content'] = 'serverWord_140';
            return $result;
        }

        $hasFifa = 0;
        //begin transaction
        $this->_wdb->beginTransaction();
        try {
            //get user for update
            $forUpdateUser = $dalUser->getUserForUpdate($uid);
            if ( $forUpdateUser['coin'] < $needCoin ) {
                $this->_wdb->rollBack();
                $result['content'] = 'serverWord_137';
                return $result;
            }
            else if ( $forUpdateUser['gold'] < $needGold ) {
                $this->_wdb->rollBack();
                $result['content'] = 'serverWord_140';
                return $result;
            }

            for ( $j=0,$jCount=count($buyBuildingAry); $j<$jCount; $j++ ) {
                $newBuilding = array('uid' => $uid,
                                     'bid' => $buyBuildingAry[$j]['cid'],
                                     'x' => $buyBuildingAry[$j]['x'],
                                     'y' => $buyBuildingAry[$j]['y'],
                                     'z' => $buyBuildingAry[$j]['z'],
                                     'mirro' => $buyBuildingAry[$j]['mirro'],
                                     'can_find' => $buyBuildingAry[$j]['canFind'],
                                     'status' => 1,
                                     'buy_time' => $nowTime,
                                     'item_type' => $buyBuildingAry[$j]['item_type']);
                //add user building
                $dalBuilding->addUserBuilding($newBuilding);
            }

            for ( $l=0,$lCount=count($buyPlantAry); $l<$lCount; $l++ ) {
                $bid = $buyPlantAry[$l]['cid'];
                $nbPlantInfo = Bll_Cache_Island::getPlantById($bid);
                $newPlant = array('uid' => $uid,
                                  'bid' => $bid,
                                  'item_id' => $nbPlantInfo['item_id'],
                                  'x' => $buyPlantAry[$l]['x'],
                                  'y' => $buyPlantAry[$l]['y'],
                                  'z' => $buyPlantAry[$l]['z'],
                                  'mirro' => $buyPlantAry[$l]['mirro'],
                                  'can_find' => $buyPlantAry[$l]['canFind'],
                                  'level' => $nbPlantInfo['level'],
                                  'status' => 1,
                                  'buy_time' => $nowTime,
                                  'item_type' => $buyPlantAry[$l]['item_type']);
                //add user plant
                $dalPlant->insertUserPlant($newPlant);
                if ( $bid > 38232 && $bid < 41532 ) {
                	$hasFifa = 1;
                	$fifaName = $nbPlantInfo['name'];
                	$imgUrl = Zend_Registry::get('static') . '/apps/island/images/feed/fifa/'.$nbPlantInfo['bid'].'.jpg';
                }
            }

            for ( $k=0,$kCount=count($buyBackgroundAry); $k<$kCount; $k++ ) {
                $newBackground = array('uid' => $uid,
                                       'bgid' => $buyBackgroundAry[$k]['cid'],
                                       'status' => 1,
                                       'buy_time' => $nowTime,
                                       'item_type' => $buyBackgroundAry[$k]['item_type']);
                //add user building
                $dalBuilding->addUserBackground($uid, $newBackground);
            }

            //update user
            $newUser = array('coin' => $forUpdateUser['coin'] - $needCoin,
                             'gold' => $forUpdateUser['gold'] - $needGold,
                             'praise' => $forUpdateUser['praise'] + $praiseChange);
            $dalUser->updateUser($uid, $newUser);
            
            //end of transaction
            $this->_wdb->commit();
        
            if ( $needGold > 0 ) {
                //add user gold info
                $userGoldInfo = array('uid' => $uid,
                                      'content' => '[buyIslandArray]:buyCids='.$goldIds,
                                      'create_time' => $nowTime);
                $dalGold = Dal_Island_Gold::getDefaultInstance();
                $remainGold = 0;
                
                foreach ($buyGoldAry as $goldItem) {
                    if ( $remainGold > 0 ) {
                        $remainGold = $remainGold - $goldItem['price'];
                    }
                    else {
                        $remainGold = $forUpdateUser['gold'] - $goldItem['price'];
                    }
                    $userGoldInfo['gold'] = $goldItem['price'];
                    $userGoldInfo['remain_gold'] = $remainGold;
                    $userGoldInfo['item_id'] = $goldItem['item_id'];
                    $userGoldInfo['name'] = $goldItem['name'];
                    $userGoldInfo['count'] = 1;
                    $dalGold->insertUserGoldInfo($userGoldInfo);
                }
            }
            
            if ( $buildingChange == 1 ) {
                //clear user building cache
                Bll_Cache_Island_User::clearCache('getUsingBuilding', $uid);
            }
            if ( $plantChange == 1 ) {
                //clear user plant cache
                Bll_Cache_Island_User::clearCache('getUsingPlant', $uid);
                Bll_Cache_Island_User::clearCache('getUserPlantListAll', $uid);
                Bll_Cache_Island_User::clearCache('getListIslandPlant', $uid);
                Bll_Cache_Island_User::clearCache('getUserPlantList', $uid);
            }
            if ( $backgroundChange == 1 ) {
                //clear user background cache
                Bll_Cache_Island_User::clearCache('getUsingBackground', $uid);
            }
            
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user buy count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'buy_count', 1);

            //update user achievement praise
            $userAchievementPraise = $dalMongoAchievement->getUserAchievementByField($uid, 'num_13');
            if ( $userAchievementPraise < $forUpdateUser['praise'] + $praiseChange ) {
                $dalMongoAchievement->updateUserAchievement($uid, array('num_13' => $forUpdateUser['praise'] + $praiseChange));
            }

            //update user achievement plant count
            $buyPlantCount = count($buyPlantAry);
            if ( $buyPlantCount > 0 ) {
                $dalMongoAchievement->updateUserAchievementByField($uid, 'num_17', $buyPlantCount);
            }

            if ( $needCoin > 0 ) {
                //update user buy coin
                $dalMongoAchievement->updateUserAchievementByField($uid, 'num_14', $needCoin);
            }
            if ( $needGold > 0 ) {
                //update user buy gold
                $dalMongoAchievement->updateUserAchievementByField($uid, 'buy_gold', $needGold);
            }
            
            if ( $hasFifa == 1 ) {
	            $feed = Bll_Island_Activity::send('BUY_FIFA', $uid, array('plant' => $fifaName, 'imgUrl' => $imgUrl));
	            $result['feed'] = $feed;
            }

            $result['status'] = 1;
            $result['coinChange'] = -$needCoin;
            $result['goldChange'] = -$needGold;
            $result['itemBoxChange'] = true;
            $result['islandChange'] = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[buyIslandArray]:'.$e->getMessage(), 'transaction');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
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
    public function buyItemArray($uid, $itemArray)
    {
        $result = array('status' => -1);

        $dalUser = Dal_Island_User::getDefaultInstance();
        //get island user
        $islandUser = $dalUser->getUserLevelInfo($uid);

        $dalCard = Dal_Island_Card::getDefaultInstance();
        $dalBuilding = Dal_Island_Building::getDefaultInstance();
        $dalPlant = Dal_Island_Plant::getDefaultInstance();

        $backgroundArray = array();
        $buildingArray = array();
        $plantArray = array();
        $cardArray = array();
        $buyGoldAry = array();
        $needCoin = 0;
        $needGold = 0;
        $buyCount = 0;
        $now = time();
        $goldIds = '';

        for ( $i=0,$iCount=count($itemArray); $i<$iCount; $i++ ) {
            //get item type
            $itemType = substr($itemArray[$i]['cid'], -2, 1);
            $type = substr($itemArray[$i]['cid'], -2);
            $cid = $itemArray[$i]['cid'];

            if ( $itemArray[$i]['num'] < 1 || !is_int($itemArray[$i]['num'])  ) {
                return $result;
            }

            //type,1x->background,2x->building,3x->plant,4x->card
            if ( $itemType == 1 ) {
                $bgInfo = Bll_Cache_Island::getBackgroundById($cid);
                if ( !$bgInfo || $bgInfo['can_buy'] != 1 ) {
                    $reuslt['content'] = 'serverWord_148';
                    return $result;
                }
                if ( $bgInfo['price_type'] == 1 ) {
                    $needCoin += $bgInfo['price'] * $itemArray[$i]['num'];
                }
                else if ( $bgInfo['price_type'] == 2 ) {
                    $needGold += $bgInfo['price'] * $itemArray[$i]['num'];
                    $goldIds .= ',cid='.$cid.'-count='.$itemArray[$i]['num'];
                    $buyGoldAry[] = array('item_id' => $cid,
                                          'name' => $bgInfo['name'],
                                          'price' => $bgInfo['price'] * $itemArray[$i]['num'],
                                          'count' => $itemArray[$i]['num']); 
                }
                $backgroundArray[] = array('cid' => $cid, 'item_type' => $type, 'count' => $itemArray[$i]['num']);
                $buyCount = 1;
            }
            else if ( $itemType == 2 ){
                $buildingInfo = Bll_Cache_Island::getBuildingById($cid);
                if ( !$buildingInfo || $buildingInfo['can_buy'] != 1 ) {
                    $reuslt['content'] = 'serverWord_148';
                    return $result;
                }
                if ( $buildingInfo['price_type'] == 1 ) {
                    $needCoin += $buildingInfo['price'] * $itemArray[$i]['num'];
                }
                else if ( $buildingInfo['price_type'] == 2 ) {
                    $needGold += $buildingInfo['price'] * $itemArray[$i]['num'];
                    $goldIds .= ',cid='.$cid.'-count='.$itemArray[$i]['num'];
                    $buyGoldAry[] = array('item_id' => $cid,
                                          'name' => $buildingInfo['name'],
                                          'price' => $buildingInfo['price'] * $itemArray[$i]['num'],
                                          'count' => $itemArray[$i]['num']); 
                }
                $buildingArray[] = array('cid' => $cid, 'item_type' => $type, 'count' => $itemArray[$i]['num']);
                $buyCount = 1;
            }
            else if ( $itemType == 3 ) {
                $plantInfo = Bll_Cache_Island::getPlantById($cid);

                if ( !$plantInfo || $plantInfo['can_buy'] != 1 ) {
                    $reuslt['content'] = 'serverWord_148';
                    return $result;
                }
                if ( $plantInfo['price_type'] == 1 ) {
                    $needCoin += $plantInfo['price'] * $itemArray[$i]['num'];
                }
                else if ( $plantInfo['price_type'] == 2 ) {
                    $needGold += $plantInfo['price'] * $itemArray[$i]['num'];
                    $goldIds .= ',cid='.$cid.'-count='.$itemArray[$i]['num'];
                    $buyGoldAry[] = array('item_id' => $cid,
                                          'name' => $plantInfo['name'],
                                          'price' => $plantInfo['price'] * $itemArray[$i]['num'],
                                          'count' => $itemArray[$i]['num']); 
                }
                $plantArray[] = array('cid' => $cid, 'item_type' => $type, 'count' => $itemArray[$i]['num'], 'item_id' => $plantInfo['item_id']);
                $buyCount = 1;
            }
            else if ( $itemType == 4 ) {
                $cardInfo = Bll_Cache_Island::getCardById($cid);
                if ( !$cardInfo || $cardInfo['can_buy'] != 1 ) {
                    $reuslt['content'] = 'serverWord_148';
                    return $result;
                }
                if ( $cardInfo['price_type'] == 1 ) {
                    $needCoin += $cardInfo['price'] * $itemArray[$i]['num'];
                }
                else if ( $cardInfo['price_type'] == 2 ) {
                    $needGold += $cardInfo['price'] * $itemArray[$i]['num'];
                    $goldIds .= ',cid='.$cid.'-count='.$itemArray[$i]['num'];
                    $buyGoldAry[] = array('item_id' => $cid,
                                          'name' => $cardInfo['name'],
                                          'price' => $cardInfo['price'] * $itemArray[$i]['num'],
                                          'count' => $itemArray[$i]['num']); 
                }
                $cardArray[] = array('cid' => $cid, 'item_type' => $type, 'count' => $itemArray[$i]['num']);
            }
        }

        if ( $islandUser['coin'] < $needCoin ) {
            $result['content'] = 'serverWord_137';
            return $result;
        }
        if (  $islandUser['gold'] < $needGold ) {
            $result['content'] = 'serverWord_140';
            return $result;
        }

        $backgroundChange = 0;
        //begin transaction
        $this->_wdb->beginTransaction();

        try {
            //get user for update
            $forUpdateUser = $dalUser->getUserForUpdate($uid);
            if ( $forUpdateUser['coin'] < $needCoin ) {
                $this->_wdb->rollBack();
                $result['content'] = 'serverWord_137';
                return $result;
            }
            else if ( $forUpdateUser['gold'] < $needGold ) {
                $this->_wdb->rollBack();
                $result['content'] = 'serverWord_140';
                return $result;
            }

            //update user
            $newUser = array('coin' => $forUpdateUser['coin'] - $needCoin,
                             'gold' => $forUpdateUser['gold'] - $needGold);
            $dalUser->updateUser($uid, $newUser);
                        
            for ( $j=0,$jCount=count($backgroundArray); $j<$jCount; $j++ ) {
                $newBackground = array('uid' => $uid,
                                       'bgid' => $backgroundArray[$j]['cid'],
                                       'status' => 1,
                                       'buy_time' => $now,
                                       'item_type' => $backgroundArray[$j]['item_type']);
                //add user background
                $dalBuilding->addUserBackground($uid, $newBackground);
                $backgroundChange = 1;
            }

            for ( $m=0,$mCount=count($buildingArray); $m<$mCount; $m++ ) {
                $newBuilding = array('uid' => $uid,
                                     'bid' => $buildingArray[$m]['cid'],
                                     'status' => 0,
                                     'buy_time' => $now,
                                     'item_type' => $buildingArray[$m]['item_type']);
                //add user Building
                $dalBuilding->addUserBuilding($newBuilding);
            }

            for ( $k=0,$kCount=count($plantArray); $k<$kCount; $k++ ) {
                $newBuilding = array('uid' => $uid,
                                     'bid' => $plantArray[$k]['cid'],
                                     'status' => 0,
                                     'item_id' => $plantArray[$k]['item_id'],
                                     'buy_time' => $now,
                                     'item_type' => $plantArray[$k]['item_type']);
                //add user plant
                $dalPlant->insertUserPlant($newBuilding);
            }

            for ( $n=0,$nCount=count($cardArray); $n<$nCount; $n++ ) {
                $newCard = array('uid' => $uid,
                                 'cid' => $cardArray[$n]['cid'],
                                 'count' => $cardArray[$n]['count'],
                                 'buy_time' => $now,
                                 'item_type' => $cardArray[$n]['item_type']);
                //add user card
                $dalCard->addUserCard($newCard);
            }

            //end of transaction
            $this->_wdb->commit();
        
            if ( $needGold > 0 ) {            
                //add user gold info
                $userGoldInfo = array('uid' => $uid,
                                      'content' => '[buyItemArray]:buyCids='.$goldIds,
                                      'create_time' => time());
                $dalGold = Dal_Island_Gold::getDefaultInstance();
                $remainGold = 0;
                
                foreach ($buyGoldAry as $goldItem) {
                    if ( $remainGold > 0 ) {
                        $remainGold = $remainGold - $goldItem['price'];
                    }
                    else {
                        $remainGold = $forUpdateUser['gold'] - $goldItem['price'];
                    }
                    
                    $userGoldInfo['gold'] = $goldItem['price'];
                    $userGoldInfo['remain_gold'] = $remainGold;
                    $userGoldInfo['item_id'] = $goldItem['item_id'];
                    $userGoldInfo['name'] = $goldItem['name'];
                    $userGoldInfo['count'] = $goldItem['count'];
                    $dalGold->insertUserGoldInfo($userGoldInfo);
                }
            }
            
            if ( $backgroundChange == 1 ) {
            	//clear user background cache
                Bll_Cache_Island_User::clearCache('getUsingBackground', $uid);
            }

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user achievement plant count
            $plantArrayCount = count($plantArray);
            if ( $plantArrayCount > 0 ) {
                $dalMongoAchievement->updateUserAchievementByField($uid, 'num_17', $plantArrayCount);
            }
            if ( $buyCount == 1 ) {
                //update user buy count
                $dalMongoAchievement->updateUserAchievementByField($uid, 'buy_count', 1);
            }
            if ( $needCoin > 0 ) {
                //update user buy coin
                $dalMongoAchievement->updateUserAchievementByField($uid, 'num_14', $needCoin);
            }
            if ( $needGold > 0 ) {
                //update user buy gold
                $dalMongoAchievement->updateUserAchievementByField($uid, 'buy_gold', $needGold);
            }

            $result['status'] = 1;
            $result['coinChange'] = -$needCoin;
            $result['goldChange'] = -$needGold;
            $result['itemBoxChange'] = true;
            $result['islandChange'] = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[buyItemArray]:'.$e->getMessage(), 'transaction');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }

        return $result;
    }
}