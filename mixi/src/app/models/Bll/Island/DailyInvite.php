<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     
 */
class Bll_Island_DailyInvite extends Bll_Abstract
{

	public function addCoin($uid, $inviteUid, $coin=100)
    {
        $result = false;
        try {
        	$sendCoin = 0;
        	$now = time();
	        $dalIvt = Dal_Island_DailyInvite::getDefaultInstance();
	        $rowIvt = $dalIvt->getByKey($uid, $inviteUid);
	        if (empty($rowIvt)) {
	        	$dalIvt->insert(array('uid' => $uid, 'invite_uid' => $inviteUid, 'invite_count' => 1, 'last_invite_time' => $now));
	        	$sendCoin = 1;
	        }
	        else {
	        	$last = $rowIvt['last_invite_time'];
	        	if (date('Y-m-d', $last) < date('Y-m-d', $now)) {
	        		$dalIvt->update($uid, $inviteUid, array('invite_count' => ((int)$rowIvt['invite_count'] + 1), 'last_invite_time' =>$now));
	        		$sendCoin = 1;
	        	}
	        }
	        
	        if ($sendCoin) {
	        	//update user coin
            	$dalIslandUser = Dal_Island_User::getDefaultInstance();           
            	$dalIslandUser->updateUserByField($uid, 'coin', $coin);   
            	$result = true;
	        }
	        else {
	        	$result = false;
	        }
        }
        catch (Exception $e) {
        	info_log($e->getMessage(), 'invite-addCoin-error');
            err_log('[Bll_Island_DailyInvite::addCoin]: ' . $e->getMessage());
        }

        return $result;
    }
}