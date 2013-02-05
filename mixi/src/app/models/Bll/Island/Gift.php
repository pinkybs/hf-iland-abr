<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/29    xial
 */
class Bll_Island_Gift
{
	/**
	 * add gift BackGround
	 * @param : integer uid
	 * @param : integer fid
	 * @param : integer id
	 * @param : integer $itemType
	 * @return: boolean
	 */
	public static function addBackGround($uid, $fid, $id, $itemType)
	{
        $dalBuilding = Dal_Island_Building::getDefaultInstance();

        $db = $dalBuilding->getWriter();

		try {
			 $newBackground = array('uid' => $fid,
                                    'bgid' => $id,
			 						'item_type' => $itemType,
                                    'status' => 0,
                                    'buy_time' => time());
            //add user background
            $dalBuilding->insertUserBackground($newBackground);
            
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today send gift count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_4', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_4', 1);
            //update friend accept gift count
            $dalMongoAchievement->updateUserAchievementByField($fid, 'accept_gift_count', 1);

		} catch (Exception $e) {
		    $db->rollBack();
		    err_log($e->getMessage());
            return false;
		}
		return true;
	}

	/**
	 * add gift card
	 * @param : integer uid
	 * @param : integer fid
	 * @param : integer id
	 * @param : integer $itemType
	 * @return: boolean
	 */
	public static function addCard($uid, $fid, $id, $itemType)
	{
        $dalCard = Dal_Island_Card::getDefaultInstance();

        $db = $dalCard->getWriter();
		try {
			$newCard = array('uid' => $fid,
                             'cid' => $id,
							 'item_type' => $itemType,
                             'count' => 1,
                             'buy_time' => time());
            //add user card
            $dalCard->addUserCard($newCard);
            
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today send gift count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_4', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_4', 1);
            //update friend accept gift count
            $dalMongoAchievement->updateUserAchievementByField($fid, 'accept_gift_count', 1);
            
		} catch (Exception $e) {
		    $db->rollBack();
		    err_log($e->getMessage());
            return false;
		}
		return true;
	}

	/**
	 * add gift Building
	 * @param : integer uid
	 * @param : integer fid
	 * @param : integer id
	 * @param : integer $itemType
	 * @return: boolean
	 */
	public static function addBuilding($uid, $fid, $id, $itemType)
	{
        $dalBuilding = Dal_Island_Building::getDefaultInstance();

        $db = $dalBuilding->getWriter();
		try {
			$newBuilding = array('uid' => $fid,
                                 'bid' => $id,
								 'item_type' => $itemType,
                                 'status' => 0,
                                 'buy_time' => time());
            //add user Building
            $dalBuilding->addUserBuilding($newBuilding);
            
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today send gift count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_4', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_4', 1);
            //update friend accept gift count
            $dalMongoAchievement->updateUserAchievementByField($fid, 'accept_gift_count', 1);
            
		} catch (Exception $e) {
		    $db->rollBack();
		    err_log($e->getMessage());
            return false;
		}
		return true;
	}

	public static function insertPlant($uid, $fid, $id, $itemType)
	{
        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        $plantInfo = Bll_Cache_Island::getPlantById($id);

        $db = $dalPlant->getWriter();
		try {
			$newPlant = array('uid' => $fid,
                              'bid' => $id,
							  'item_type' => $itemType,
							  'item_id' => $plantInfo['item_id'],
                              'status' => 0,
                              'buy_time' => time());
            //add user card
            $dalPlant->insertUserPlant($newPlant);
            
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today send gift count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_4', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_4', 1);
            //update friend accept gift count
            $dalMongoAchievement->updateUserAchievementByField($fid, 'accept_gift_count', 1);
            
		} catch (Exception $e) {
		    $db->rollBack();
            return false;
		}
		return true;
	}

	/**
	 * type add gift
	 * @param integer $actorUid
	 * @param integer $fid
	 * @param integer $gid
	 * @return boolean $result
	 */
	public static function insertGift($uid, $fid, $gid)
	{
		$type = substr($gid, -2);
		$itemType = substr($gid, -2, 1);
		//itemType,1x->card,2x->background,3x->plant,4x->building
		if ( $itemType == 1 ) {
            $result = self::addBackground($uid, $fid, $gid, $type);
        }
        else if ( $itemType == 2 ){
            $result = self::addBuilding($uid, $fid, $gid, $type);
        }
        else if ( $itemType == 3 ) {
        	$result = self::insertPlant($uid, $fid, $gid, $type);
        }
        else if ( $itemType == 4 ) {
            $result = self::addCard($uid, $fid, $gid, $type);
        }

        return $result;
	}

	/**
	 * send gift
	 * @param array $g
	 * @param array $ids (friend uid)
	 * @return boolean
	 */
	public static function sendGift($gid, $uid, $count, $in_fids, $out_fids)
	{
	    try {
		    if ($in_fids) {
		        foreach ($in_fids as $fid) {
		            self::insertGift($uid, $fid, $gid);
		        }

                $time = time();
                foreach ($in_fids as $fid) {
                    $feeds[] = array('uid' => $fid,
                                  'template_id' => 9,
                                  'actor' => $uid,
                                  'target' => $fid,
                                  'type' => 3,
                                  'create_time' => $time);
                    
                    Bll_Island_Message::sendGiftToAppUser($uid, $fid);
                }

                Bll_Island_Feed::batchInsertMinifeed($feeds);
		    }
            
		    if ($out_fids) {
		        $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
		        
		        foreach ($out_fids as $fid) {
		            Bll_Island_Message::send('GIFT', $uid, $fid, array('gift_id' => $gid));
		            
                    //update user today send gift count
                    $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_4', 1);
                    //update user send gift count
                    $dalMongoAchievement->updateUserAchievementByField($uid, 'num_4', 1);
                    //update friend accept gift count
                    $dalMongoAchievement->updateUserAchievementByField($fid, 'accept_gift_count', 1);
		        }
		    }

            $dalGift = Dal_Mongo_Gift::getDefaultInstance();
            $dalGift->updateGiftStatus($uid, $count);

            return true;

		} catch (Exception $e) {
            err_log($e->getMessage());
		}

		return false;
	}
}