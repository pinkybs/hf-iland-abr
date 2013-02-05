<?php

class Hapyfish2_Island_Cache_Shop
{
	public static function getCanBuyList()
	{
		$cardList = self::getCanBuyCardList();
		$backgroundList = self::getCanBuyBackgroundList();
		$buildingList = self::getCanBuyBuildingList();
		$plantList = self::getCanBuyPlantList();
		
		return array_merge($cardList, $backgroundList, $buildingList, $plantList);
	}
	
	public static function getCanBuyCardList()
	{
		$list = Hapyfish2_Island_Cache_BasicInfo::getCardList();
		$canbuyList = array();
		if ($list) {
			foreach ($list as $id => $item) {
				if ($item['can_buy'] == 1) {
					$canbuyList[] = $id;
				}
			}
		}
		
		return $canbuyList;
	}
	
	public static function getCanBuyBackgroundList()
	{
		$list = Hapyfish2_Island_Cache_BasicInfo::getBackgroundList();
		$canbuyList = array();
		if ($list) {
			foreach ($list as $id => $item) {
				if ($item['can_buy'] == 1) {
					$canbuyList[] = $id;
				}
			}
		}
		
		return $canbuyList;
	}
	
	public static function getCanBuyBuildingList()
	{
		$list = Hapyfish2_Island_Cache_BasicInfo::getBuildingList();
		$canbuyList = array();
		if ($list) {
			foreach ($list as $id => $item) {
				if ($item['can_buy'] == 1) {
					$canbuyList[] = $id;
				}
			}
		}
		
		return $canbuyList;
	}
	
	public static function getCanBuyPlantList()
	{
		$list = Hapyfish2_Island_Cache_BasicInfo::getPlantList();
		$canbuyList = array();
		if ($list) {
			foreach ($list as $id => $item) {
				if ($item['can_buy'] == 1) {
					$canbuyList[] = $id;
				}
			}
		}
		
		return $canbuyList;
	}
}