<?php

class Hapyfish2_Island_Bll_Warehouse
{
	/**
	 * load one user's all items in warehouse
	 * @param integer $uid
	 * @return array $resultVo
	 */
	public static function loadItems($uid)
	{
		//get cards
		$lstCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		
		$cardVo = array();
		if ($lstCard) {
			foreach ($lstCard as $cid => $item) {
				$cardVo[] = array($cid, $cid, $item['count']);
			}
		}

		//get buildings
		$lstBuilding = Hapyfish2_Island_HFC_Building::getInWareHouse($uid);
		$buildingVo = array();
		if ($lstBuilding) {
			foreach ($lstBuilding as $building) {
				$buildingVo[] = array($building['id'] . $building['item_type'] , $building['cid'], 1);
			}
		}

		//get plants
		$lstPlant = Hapyfish2_Island_HFC_Plant::getInWareHouse($uid);
		$plantVo = array();
		if ($lstPlant) {
			foreach ($lstPlant as $plant) {
				$plantVo[] = array($plant['id'] . $plant['item_type'] , $plant['cid'], 1, $plant['level']);
			}
		}

		//get background
		$lstBackground = Hapyfish2_Island_Cache_Background::getInWareHouse($uid);
		$backgroundVo = array();
		if ($lstBackground) {
			foreach ($lstBackground as $bg) {
				$backgroundVo[] = array($bg['id'] .  $bg['item_type'], $bg['bgid'], 1);
			}
		}

		return array_merge($cardVo, $buildingVo, $plantVo, $backgroundVo);

	}
}