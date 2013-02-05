<?php

class Hapyfish2_Island_Tool_Savediy
{
	public static function savedbAllUser($dbId)
	{
		info_log('# savedbAllUser - start - #', 'savedbAllUser_'.$dbId);
		//2011-05-27 00:00:00
		$startTime = 1306425600;
		
		$db = Hapyfish2_Island_Dal_Savediy::getDefaultInstance();
			
		//for($i=0;$i<8;$i++) {
			for($j=0;$j<10;$j++) {
				info_log('savedbAllUser:tableId-'.$j, 'savedbAllUser_'.$dbId);
				$uidList = $db->getUidListByPage($dbId, $j, $startTime);
				$kCount = count($uidList);
				for ( $k=0;$k<$kCount;$k++) {
					Hapyfish2_Island_Tool_SaveOldUserCache::savePlant($uidList[$k]['uid']);
					Hapyfish2_Island_Tool_SaveOldUserCache::saveBuilding($uidList[$k]['uid']);
					info_log('savedbAllUser:'.$uidList[$k]['uid'], 'savedbAllUser_'.$dbId);
				}
			}
		//}
		info_log('# savedbAllUser - end - #', 'savedbAllUser_'.$dbId);
	}
	
}