<?php

class Hapyfish_Island_Bll_Warehouse
{
	/**
	 * load one user's all items in warehouse
	 * @param integer $uid
	 * @return array $resultVo
	 */
	public static function loadItems($uid)
	{
		//get cards
		$dalCard = Dal_Island_Card::getDefaultInstance();
		$lstCard = $dalCard->getLstCardById($uid);
		$cardVo = array();
		if ($lstCard) {
			foreach ($lstCard as $var) {
				$itemVo = array($var['id'] . $var['item_type'], $var['cid'], $var['count']);
				$cardVo[] = $itemVo;
			}
		}

		//get buildings
		$dalBuilding = Dal_Island_Building::getDefaultInstance();
		$lstBuilding = $dalBuilding->getItemBoxBuilding($uid);
		$buildingVo = array();
		if ($lstBuilding) {
			foreach ($lstBuilding as $var) {
				$itemVo = array($var['id'] , $var['cid'], 1);
				$buildingVo[] = $itemVo;
			}
		}

		//get plants
		$dalPlant = Dal_Island_Plant::getDefaultInstance();
		$lstPlant = $dalPlant->getItemBoxPlant($uid);
		$plantVo = array();
		if ($lstPlant) {
			foreach ($lstPlant as $var) {
				$itemVo = array($var['id'] , $var['cid'], "1", $var['level']);
				$plantVo[] = $itemVo;
			}
		}

		//get background
		$lstBackground = $dalBuilding->getItemBoxBackground($uid);
		$backgroundVo = array();
		if ($lstBackground) {
			foreach ($lstBackground as $var) {
				$itemVo = array($var['id'] , $var['cid'], 1);
				$backgroundVo[] = $itemVo;
			}
		}

		$resultVo = array_merge($cardVo, $buildingVo, $plantVo, $backgroundVo);
		return $resultVo;
	}
}