<?php

class Hapyfish2_Island_Stat_Bll_Campaign
{

    public static $aryCampaignInfo = array(
    			'1' => array('name'=>'', 'shop'=>'', 'des'=>''),
    			'2' => array('name'=>'', 'shop'=>'', 'des'=>''),
    			'3' => array('name'=>'', 'shop'=>'', 'des'=>'')
                );

	public static function fromCampaign($campaignId, $uid)
	{

	    $campaignId = base64_decode($campaignId);
	    /*if (!array_key_exists($campaignId, self::$aryCampaignInfo)) {
            return false;
        }*/

		try {
            $log = Hapyfish2_Util_Log::getInstance();
            $log->report('campJoinStat', array($campaignId, $uid));

            setcookie('hf_fromcamp', '', 0, '/', str_replace('http://', '.', HOST));
		}
		catch (Exception $e) {
		}
		return true;
	}

    public static function fromCampaignPv($campaignId, $clientIp)
	{

	    /*if (!array_key_exists($campaignId, self::$aryCampaignInfo)) {
            return false;
        }*/

		try {
            $log = Hapyfish2_Util_Log::getInstance();
            $log->report('campPvStat', array($campaignId, $clientIp));

            setcookie('hf_fromcamp', base64_encode($campaignId), 0, '/', str_replace('http://', '.', HOST));
		}
		catch (Exception $e) {
		}
		return true;
	}

}