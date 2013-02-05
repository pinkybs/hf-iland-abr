<?php

class Hapyfish_Island_Bll_Invite
{
	public static function inviteUid($inviteUid, $newUid, $inviteType, $gid)
	{
		try{
            if ($inviteType == 'GIFT' && $gid) {
                //is gift invite
                if ($gid) {
                    Bll_Island_Gift::insertGift($inviteUid, $newUid, $gid);
                }
            }

        	//invite success after add coin 2000
			Hapyfish_Island_Cache_User::incCoin($inviteUid, 2000);

			$dalCard = Dal_Island_Card::getDefaultInstance();
			$newCard = array('uid' => $inviteUid,
                             'cid' => 26341,
                             'count' => 1,
                             'buy_time' => time(),
                             'item_type' => 41);
			
			//add user card
			$dalCard->addUserCard($newCard);
			
		}catch (Exception $e) {
			info_log('[inviteUid(' . $inviteUid . '_' . $newUid . ')]:' . $e->getMessage(), 'Hapyfish_Island_Bll_Invite');
            return false;
		}
		
		try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update invite_count
            $dalMongoAchievement->updateUserAchievementByField($inviteUid, 'invite_count', 1);
		} catch (Exception $e) {
			
		}
		
		try {
			$now = time();
			if ($inviteType == 'GIFT' && $gid) {
				$minifeed = array('uid' => $newUid,
	                              'template_id' => 9,
	                              'actor' => $inviteUid,
	                              'target' => $newUid,
	                              'type' => 3,
	                              'create_time' => $now);
				
	            Hapyfish_Island_Bll_Feed::insertMiniFeed($minifeed);
			}

			$minifeed2 = array('uid' => $inviteUid,
							   'actor' => $inviteUid,
							   'target' => $newUid,
                               'template_id' => 7,
							   'title' => array('cardName' => '加速卡II'),
			                   'type' => 3,
							   'create_time' => $now);
			
            Hapyfish_Island_Bll_Feed::insertMiniFeed($minifeed2);
		} catch (Exception $e) {
			
		}
		
		return true;
	}
}