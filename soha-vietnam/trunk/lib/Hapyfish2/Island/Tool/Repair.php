<?php

class Hapyfish2_Island_Tool_Repair
{
	public static function repairUserPlant($cid, $uid = 1)
	{
		info_log('# repairUserPlant - start - #'.$cid, 'repairUserPlant_'.$cid);
		
        $plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($cid);
        if (empty($plantInfo)) {
        	return ;
        }
		
		$db = Hapyfish2_Island_Dal_Repair::getDefaultInstance();
		$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
		
		if ( $uid == 1 ) {
			for($i=0;$i<8;$i++) {
		info_log('/***  db  ***/-'.$i, 'repairUserPlant_'.$cid);
				for($j=0;$j<50;$j++) {
		info_log('/******  table  ******/-'.$j, 'repairUserPlant_'.$cid);
					$uidList = $db->getUidListByPage($i, $j, $cid, $plantInfo['level']);
					$kCount = count($uidList);
					for ( $k=0;$k<$kCount;$k++ ) {
						$uid = $uidList[$k]['uid'];
		info_log('uid-'.$uid, 'repairUserPlant_'.$cid);
						$plantList = Hapyfish2_Island_HFC_Plant::getAll($uid);
						foreach ( $plantList as $plant ) {
							if ( $plant['cid'] == $cid ) {
		info_log('plantId-'.$plant['id'], 'repairUserPlant_'.$cid);
								$info['level'] = $plantInfo['level'];
								$info['item_id'] = $plantInfo['item_id'];
		    					$dalPlant->update($uid, $plant['id'], $info);
								$plant = Hapyfish2_Island_HFC_Plant::loadOne($uid, $plant['id']);
								Hapyfish2_Island_Cache_Plant::reloadAllByItemKind($uid);
							}
						}
					}
				}
			}
		}
		else {
			$plantList = Hapyfish2_Island_HFC_Plant::getAll($uid);
			foreach ( $plantList as $plant ) {
				if ( $plant['cid'] == $cid ) {
		info_log('plantId-'.$plant['id'], 'repairUserPlant_'.$cid);
					$info['level'] = $plantInfo['level'];
					$info['item_id'] = $plantInfo['item_id'];
    				$dalPlant->update($uid, $plant['id'], $info);
					$plant = Hapyfish2_Island_HFC_Plant::loadOne($uid, $plant['id']);
					Hapyfish2_Island_Cache_Plant::reloadAllByItemKind($uid);
				}
			}
		}
		
		info_log('# repairUserPlant - end - #', 'repairUserPlant_'.$cid);
	}
	
	public static function repairUserInfo($uid)
	{
		$user = Hapyfish2_Platform_Bll_User::getUser($uid);
		if (empty($user)) {
			return false;
		}

		$step = 0;
		$today = date('Ymd');
		try {
			$dalUser = Hapyfish2_Island_Dal_User::getDefaultInstance();
			$dalUserSequence = Hapyfish2_Island_Dal_UserSequence::getDefaultInstance();
			$dalBackground = Hapyfish2_Island_Dal_Background::getDefaultInstance();
			$dalBuilding = Hapyfish2_Island_Dal_Building::getDefaultInstance();
			$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
			$dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();
			$dalCard = Hapyfish2_Island_Dal_Card::getDefaultInstance();
			$dalCardStatus = Hapyfish2_Island_Dal_CardStatus::getDefaultInstance();
			$dalUserIsland = Hapyfish2_Island_Dal_UserIsland::getDefaultInstance();
			$dalAchievement = Hapyfish2_Island_Dal_Achievement::getDefaultInstance();
			$dalAchievementDaily = Hapyfish2_Island_Dal_AchievementDaily::getDefaultInstance();
	
			$dalUserIsland->init($uid);
			$step++;
			$dalCard->init($uid);
			$step++;
			$dalCardStatus->init($uid);
			$step++;
			$dalAchievement->init($uid);
			$step++;
			$dalAchievementDaily->init($uid, $today);
			$step++;
		}
		catch (Exception $e) {
			info_log('[' . $step . ']' . $e->getMessage(), 'island.user.init');
            return false;
		}
		
		return true;
	}
}