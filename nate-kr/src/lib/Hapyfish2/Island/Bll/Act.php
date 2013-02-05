<?php

class Hapyfish2_Island_Bll_Act
{
	public static function get($uid = 0)
	{
		$now = time();
		$actState = array();

		if ($uid > 0) {
//		    $eventEndTime = 1314262000;//'2011-05-02 00:00:00'  1304262000
//        	if (time() < $eventEndTime) {
            	$inviteFlowStep = Hapyfish2_Island_Event_Bll_InviteFlow::getInviteStep($uid);
    			if ($inviteFlowStep >= 0 && $inviteFlowStep < 4) {
    				$yaoQingHaoYou = array(
    					'actName' => 'yaoQingHaoYou',
    					'btn' => 'yaoQingHaoYouActBtn',
    					'index' => 2,
    					'module' => 'swf/yaoQingHaoYou.swf?v=2011050502',
    					'state' => 0
    				);
    				$actState['yaoQingHaoYou'] = $yaoQingHaoYou;
    			}
//        	}
		}
		$starList = array(
			'actName' => 'dailyGetConstellation',
			'btn' => 'dailyGetConstellationActBtn',
			'module' => 'swf/dailyGetConstellation.swf',
		);
		$actState['dailyGetConstellation'] = $starList;
		return $actState;
	}

}