<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Island Cache
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Liz
*/
class Bll_Cache_Island
{
    private static $_prefix = 'Bll_Cache_Island';
    
    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }
    
    /**
     * init island
     *
     * @return array
     */
    public static function initIsland($ownerUid, $uid)
    {
        $key = self::getCacheKey('initIsland'.$ownerUid.$uid);

        if (!$result = Bll_Cache::get($key)) {
            $bllIsland = new Bll_Island_Island();
            $result = $bllIsland->initIsland($ownerUid, $uid);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX);
            }
        }

        return $result;
    }
    
    /**
     * get island shop list
     *
     * @return array
     */
    public static function getShopList()
    {
        $key = self::getCacheKey('15getShopList');

        if (!$result = Bll_Cache::get($key)) {
            $bllIslandShop = new Bll_Island_Shop();
            $result = $bllIslandShop->getShopList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }
    
    public static function getGiftList()
    {
        $key = self::getCacheKey('getGiftList');

        if (!$result = Bll_Cache::get($key)) {
            $dalGift = Dal_Island_Gift::getDefaultInstance();
            $result = $dalGift->getSendGiftList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }
    
    public static function getGiftById($gid)
    {
        $gifts = self::getGiftList();
        if ($gifts) {
            foreach ($gifts as $g) {
            	if ($g['gid'] == $gid) {
            	    return $g;
            	}
            }
        }
        
        return null;
    }

    /**
     * get island notice list
     *
     * @return array
     */
    public static function getNoticeList()
    {
        $key = self::getCacheKey('getNoticeList');

        if (!$result = Bll_Cache::get($key)) {
            $dalFeed = Dal_Island_Feed::getDefaultInstance();
            $result = $dalFeed->getNoticeList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }

    /**
     * get island background list
     *
     * @return array
     */
    public static function getBackgroundList()
    {
        $key = self::getCacheKey('3getBackgroundList');

        if (!$result = Bll_Cache::get($key)) {
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $result = $dalBuilding->getBgList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }
    
    /**
     * get island building list
     *
     * @return array
     */
    public static function getBuildingList()
    {
        $key = self::getCacheKey('15getBuildingList');

        if (!$result = Bll_Cache::get($key)) {
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $result = $dalBuilding->getBuildingList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }

    /**
     * get island plant list
     *
     * @return array
     */
    public static function getPlantList()
    {
        $key = self::getCacheKey('15getPlantList');

        if (!$result = Bll_Cache::get($key)) {
            $dalPlant = Dal_Island_Plant::getDefaultInstance();
            $result = $dalPlant->getPlantList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }

    /**
     * get island boat class list
     *
     * @return array
     */
    public static function getBoatClass()
    {
        $key = self::getCacheKey('getBoatClass');

        if (!$result = Bll_Cache::get($key)) {
            $bllUser = new Bll_Island_User();
            $result = $bllUser->boatClass();
            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }

    /**
     * get island building by id
     *
     * @return array
     */
    public static function getBuildingById($id)
    {
        $key = self::getCacheKey('15getBuildingById' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $result = $dalBuilding->getBuildingById($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }

    /**
     * get island background by id
     *
     * @return array
     */
    public static function getBackgroundById($id)
    {
        $key = self::getCacheKey('1getBackgroundById' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $result = $dalBuilding->getBackgroundById($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }

    /**
     * get island plant by id
     *
     * @return array
     */
    public static function getPlantById($id)
    {
        $key = self::getCacheKey('3getPlantById' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalPlant = Dal_Island_Plant::getDefaultInstance();
            $result = $dalPlant->getNbPlantById($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }

    /**
     * gget plant info by level
     *
     * @return array
     */
    public static function getPlantListByLevel($level)
    {
        $key = self::getCacheKey('15getPlantListByLevel' . $level);

        if (!$result = Bll_Cache::get($key)) {
            $dalPlant = Dal_Island_Plant::getDefaultInstance();
            $result = $dalPlant->getPlantListByLevel($level);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }
    
    /**
     * get island card list
     *
     * @return array
     */
    public static function getCardList()
    {
        $key = self::getCacheKey('1getCardList');

        if (!$result = Bll_Cache::get($key)) {
            $dalCard = Dal_Island_Card::getDefaultInstance();
            $result = $dalCard->getLstCard();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }

    /**
     * get island card by id
     *
     * @return array
     */
    public static function getCardById($id)
    {
        $key = self::getCacheKey('2getCardById' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalCard = Dal_Island_Card::getDefaultInstance();
            $result = $dalCard->getCardById($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }

    /**
     * get dock info by position id
     *
     * @return array
     */
    public static function getAddBoatByid($id)
    {
        $key = self::getCacheKey('getAddBoatByid' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalDock = Dal_Island_Dock::getDefaultInstance();
            $result = $dalDock->getAddBoatByid($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }

    /**
     * get the max island level
     *
     * @return array
     */
    public static function getIslandMaxLevel()
    {
        $key = self::getCacheKey('getIslandMaxLevel');

        if (!$result = Bll_Cache::get($key)) {
            $dalLevel = Dal_Island_Level::getDefaultInstance();
            $result = $dalLevel->getIslandMaxLevel();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }

        return $result;
    }

    /**
     * get user level info
     *
     * @return array
     */
    public static function getUserLevelInfo($id)
    {
        $key = self::getCacheKey('getUserLevelInfo' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalLevel = Dal_Island_Level::getDefaultInstance();
            $result = $dalLevel->getLevelInfo($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get island level info
     *
     * @return array
     */
    public static function getIslandLevelInfo($id)
    {
        $key = self::getCacheKey('getIslandLevelInfo' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalLevel = Dal_Island_Level::getDefaultInstance();
            $result = $dalLevel->getIslandLevelInfo($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get island level info by user level
     *
     * @return array
     */
    public static function getIslandLevelInfoByUserLevel($level)
    {
        $key = self::getCacheKey('getIslandLevelInfoByUserLevelb' . $level);

        if (!$result = Bll_Cache::get($key)) {
            $dalLevel = Dal_Island_Level::getDefaultInstance();
            $result = $dalLevel->getIslandLevelInfoByUserLevel($level);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }
    
    /**
     * get level gift info
     *
     * @return array
     */
    public static function getLevelGift($id)
    {
        $key = self::getCacheKey('2getLevelGift' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalLevel = Dal_Island_Level::getDefaultInstance();
            $result = $dalLevel->getLevelGift($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get ship add visitore count by praise
     *
     * @return array
     */
    public static function getShipAddVisitorByPraise($shipId, $praise)
    {
        $key = self::getCacheKey('getShipAddVisitorByPraise2' . $praise . $shipId);

        if (!$result = Bll_Cache::get($key)) {
            $dalPraise = Dal_Island_Praise::getDefaultInstance();
            $result = $dalPraise->getShipAddVisitorByPraise($shipId, $praise);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get ship add visitore count by sid
     *
     * @return array
     */
    public static function getShipAddVisitorBySid($shipId)
    {
        $key = self::getCacheKey('getShipAddVisitorBySid' . $shipId);

        if (!$result = Bll_Cache::get($key)) {
            $dalPraise = Dal_Island_Praise::getDefaultInstance();
            $result = $dalPraise->getShipAddVisitorBySid($shipId);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }
    
    /**
     * get add visitore count by praise
     *
     * @return array
     */
    public static function getAddVisitorByPraise($id)
    {
        $key = self::getCacheKey('getAddVisitorByPraise' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalPraise = Dal_Island_Praise::getDefaultInstance();
            $result = $dalPraise->getAddVisitorByPraise($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get ship info by ship id
     *
     * @return array
     */
    public static function getShip($id)
    {
        $key = self::getCacheKey('getShip' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalShip = Dal_Island_Ship::getDefaultInstance();
            $result = $dalShip->getShip($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get ship list
     *
     * @return array
     */
    public static function getShipList()
    {
        $key = self::getCacheKey('getShipList');

        if (!$result = Bll_Cache::get($key)) {
            $dalShip = Dal_Island_Ship::getDefaultInstance();
            $result = $dalShip->getShipList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get shop card list
     *
     * @return array
     */
    public static function getShopCardList()
    {
        $key = self::getCacheKey('1getShopCardList');

        if (!$result = Bll_Cache::get($key)) {
            $dalShop = Dal_Island_Shop::getDefaultInstance();
            $result = $dalShop->getCardList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get shop background list
     *
     * @return array
     */
    public static function getShopBackgroundList()
    {
        $key = self::getCacheKey('3getShopBackgroundList');

        if (!$result = Bll_Cache::get($key)) {
            $dalShop = Dal_Island_Shop::getDefaultInstance();
            $result = $dalShop->getBackgroundList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get shop building list
     *
     * @return array
     */
    public static function getShopBuildingList()
    {
        $key = self::getCacheKey('15getShopBuildingList');

        if (!$result = Bll_Cache::get($key)) {
            $dalShop = Dal_Island_Shop::getDefaultInstance();
            $result = $dalShop->getBuildingList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get shop plant list
     *
     * @return array
     */
    public static function getShopPlantList()
    {
        $key = self::getCacheKey('15getShopPlantList');

        if (!$result = Bll_Cache::get($key)) {
            $dalShop = Dal_Island_Shop::getDefaultInstance();
            $result = $dalShop->getPlantList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get achievement task info by id
     *
     * @return array
     */
    public static function getAchievementTask($id)
    {
        $key = self::getCacheKey('1getAchievementTask' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalTask = Dal_Island_Task::getDefaultInstance();
            $result = $dalTask->getAchievementTask($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get build task info by id
     *
     * @return array
     */
    public static function getBuildTask($id)
    {
        $key = self::getCacheKey('15getBuildTask' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalTask = Dal_Island_Task::getDefaultInstance();
            $result = $dalTask->getBuildTask($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get daily task info by id
     *
     * @return array
     */
    public static function getDailyTask($id)
    {
        $key = self::getCacheKey('1getDailyTask' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalTask = Dal_Island_Task::getDefaultInstance();
            $result = $dalTask->getDailyTask($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get achievement task list
     *
     * @return array
     */
    public static function getAchievementTaskList()
    {
        $key = self::getCacheKey('1getAchievementTaskList');

        if (!$result = Bll_Cache::get($key)) {
            $dalTask = Dal_Island_Task::getDefaultInstance();
            $result = $dalTask->getAchievementTaskList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get build task list
     *
     * @return array
     */
    public static function getBuildTaskList()
    {
        $key = self::getCacheKey('15getBuildTaskList');

        if (!$result = Bll_Cache::get($key)) {
            $dalTask = Dal_Island_Task::getDefaultInstance();
            $result = $dalTask->getBuildTaskList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get build task list
     *
     * @return array
     */
    public static function getBuildTaskNeedInfoList()
    {
        $key = self::getCacheKey('15getBuildTaskNeedInfoList');

        if (!$result = Bll_Cache::get($key)) {
            $dalTask = Dal_Island_Task::getDefaultInstance();
            $result = $dalTask->getBuildTaskNeedInfoList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }
    
    /**
     * get daily task list
     *
     * @return array
     */
    public static function getDailyTaskList()
    {
        $key = self::getCacheKey('1getDailyTaskList');

        if (!$result = Bll_Cache::get($key)) {
            $dalTask = Dal_Island_Task::getDefaultInstance();
            $result = $dalTask->getDailyTaskList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get task info by title
     *
     * @return array
     */
    public static function getTaskInfoByTitle($id)
    {
        $key = self::getCacheKey('getTaskInfoByTitle' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalTask = Dal_Island_Task::getDefaultInstance();
            $result = $dalTask->getTaskInfoByTitle($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get title list info
     *
     * @return array
     */
    public static function getTitleList()
    {
        $key = self::getCacheKey('getTitleList');

        if (!$result = Bll_Cache::get($key)) {
            $dalTask = Dal_Island_Task::getDefaultInstance();
            $result = $dalTask->getTitleList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }
    
    /**
     * get title info by id
     *
     * @return array
     */
    public static function getTitleById($id)
    {
        $key = self::getCacheKey('getTitleById' . $id);

        if (!$result = Bll_Cache::get($key)) {
            $dalTask = Dal_Island_Task::getDefaultInstance();
            $result = $dalTask->getTitleById($id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }
    
    /**
     * get user title 
     *
     * @return array
     */
    public static function getUserTitle($uid)
    {
        $key = self::getCacheKey('getUserTitle' . $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalUser = Dal_Island_User::getDefaultInstance();
            $result = $dalUser->getUserTitle($uid);

            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get level list info
     *
     * @return array
     */
    public static function getLevelList()
    {
        $key = self::getCacheKey('1getLevelList');

        if (!$result = Bll_Cache::get($key)) {
            $dalLevel = Dal_Island_Level::getDefaultInstance();
            $levelList = $dalLevel->getLevelList();
            
            $lastCount = 0;
            for ( $i=0,$iCount=count($levelList); $i<$iCount; $i++ ) {
                $levelList[$i]['addVisitor'] = 0;
                if ( $levelList[$i]['visitor_count'] > 0 ) {
                    if ( $lastCount > 0 ) {
                        $levelList[$i]['addVisitor'] = $levelList[$i]['visitor_count'] - $lastCount;
                    }
                    $lastCount = $levelList[$i]['visitor_count'];
                }
                unset($levelList[$i]['visitor_count']);
            }

            $result = $levelList;
            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }
    
    /**
     * clean tag cache
     *
     * @return void
     */
    public static function cleanIslandShop($name, $param = null)
    {
        if ( $param ) {
            $name = $name . $param;
        }
        Bll_Cache::delete(self::getCacheKey($name));
    }

    /**
     * clean tag cache
     *
     * @param int $number
     * @return void
     */
    public static function cleanNoticeList($id)
    {
        Bll_Cache::delete(self::getCacheKey('getNoticeList' . $id));
    }
}