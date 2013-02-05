<?php

class Hapyfish2_Island_Bll_Statics
{
	public static function getUidList()
	{
		try {
			$dalStatics = Hapyfish2_Island_Dal_Statics::getDefaultInstance();
			return $dalStatics->getUidList();
		} catch (Exception $e) {
			return null;
		}
	}
	
	public static function getMaxUid()
	{
		$list = self::getUidList();
		$max = 0;
		if($list) {
			foreach ($list as $uid) {
				if ($max < $uid) {
					$max = $uid;
				}
			}
		}
		
		return $max;
	}

}