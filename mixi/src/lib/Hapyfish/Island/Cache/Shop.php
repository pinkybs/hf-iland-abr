<?php

class Hapyfish_Island_Cache_Shop
{
    /**
     * get island shop list
     *
     * @return array
     */
    public static function getShopList()
    {
        $shopList = array();

        //get card list
        $cardList = self::getShopCardList();
        //get island background list
        $backgroundList = self::getShopBackgroundList();
        //get island building list
        $buildingList = self::getShopBuildingList();
        //get island plant list
        $plantList = self::getShopPlantList();

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
     * get shop card list
     *
     * @return array
     */
    public static function getShopCardList()
    {
		$key = 'ShopCardList';
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$list = $cache->get($key);
		if ($list === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Shop::getDefaultInstance();
			$list = $db->getCardList();
			if ($list) {
				$cache->add($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $list;
    }
    
    public static function refreshShopCardList()
    {
		//load from database
		$db = Hapyfish_Island_Dal_Shop::getDefaultInstance();
		$list = $db->getCardList();
		if ($list) {
    		$key = 'ShopCardList';
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->replace($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $list;    	
    }
    
    /**
     * get shop background list
     *
     * @return array
     */
    public static function getShopBackgroundList()
    {
		$key = 'BackgroundList';
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$list = $cache->get($key);
		if ($list === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Shop::getDefaultInstance();
			$list = $db->getBackgroundList();
			if ($list) {
				$cache->add($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $list;
    }
    
    public static function refreshShopBackgroundList()
    {
    	//load from database
		$db = Hapyfish_Island_Dal_Shop::getDefaultInstance();
		$list = $db->getBackgroundList();
		if ($list) {
			$key = 'BackgroundList';
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->replace($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $list;
    }
    
    /**
     * get shop building list
     *
     * @return array
     */
    public static function getShopBuildingList()
    {
		$key = 'BuildingList';
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$list = $cache->get($key);
		if ($list === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Shop::getDefaultInstance();
			$list = $db->getBuildingList();
			if ($list) {
				$cache->add($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $list;
    }
    
    public static function refreshShopBuildingList()
    {
    	//load from database
		$db = Hapyfish_Island_Dal_Shop::getDefaultInstance();
		$list = $db->getBuildingList();
		if ($list) {
			$key = 'BuildingList';
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->replace($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $list;
    }
    
    /**
     * get shop plant list
     *
     * @return array
     */
    public static function getShopPlantList()
    {
		$key = 'PlantList';
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$list = $cache->get($key);
		if ($list === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Shop::getDefaultInstance();
			$list = $db->getPlantList();
			if ($list) {
				$cache->add($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $list;
    }
    
    public static function refreshShopPlantList()
    {
    	//load from database
		$db = Hapyfish_Island_Dal_Shop::getDefaultInstance();
		$list = $db->getPlantList();
		if ($list) {
			$key = 'PlantList';
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->replace($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $list;
    }
    
    /**
     * get island card by id
     *
     * @return array
     */
    public static function getCardById($id)
    {
		$key = 'CardById_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$card = $cache->get($key);
		if ($card === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Card::getDefaultInstance();
			$card = $db->getCardById($id);
			if ($card) {
				$cache->add($key, $card, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $card;
    }
    
    /**
     * get island background by id
     *
     * @return array
     */
    public static function getBackgroundById($id)
    {
		$key = 'BackgroundById_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$background = $cache->get($key);
		if ($background === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Background::getDefaultInstance();
			$background = $db->getBackgroundById($id);
			if ($background) {
				$cache->add($key, $background, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $background;
    }

    /**
     * get island building by id
     *
     * @return array
     */
    public static function getBuildingById($id)
    {
		$key = '1BuildingById_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$building= $cache->get($key);
		if ($building === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Building::getDefaultInstance();
			$building = $db->getBuildingById($id);
			if ($building) {
				$cache->add($key, $building, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $building;
    }
    
    /**
     * get island plant by id
     *
     * @return array
     */
    public static function getPlantById($id)
    {
		$key = '1PlantById_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$plant= $cache->get($key);
		if ($plant === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Plant::getDefaultInstance();
			$plant = $db->getNbPlantById($id);
			if ($plant) {
				$cache->add($key, $plant, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $plant;
    }
}