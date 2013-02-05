<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     
 */
class Bll_Island_RewardPlus extends Bll_Abstract
{

	public function addPoint($transactionId, $promotionName, $uid, $status, $point=0)
    {
        $result = false;
        try {
	        $dalReward = Dal_Island_RewardPlus::getDefaultInstance();	    		    		    
        	$rowReward = $dalReward->getById($transactionId);
        	//repeat submited
        	if ( !empty($rowReward) && ($rowReward['status'] == 1 || $rowReward['status'] == 9) ) {        		
        		return false;
        	}
        	
        	$now = time();
        	$this->_wdb->beginTransaction(); 	

        	if (empty($rowReward)) {
        		$info = array('transaction_id'=>$transactionId,'promotion_name'=>$promotionName,'uid'=>$uid,'point'=>$point,'status'=>$status,'create_time'=>$now);
        		if (1 == $status) {
        			$info['complete_time'] = $now;
        		}
	    		$dalReward->insert($info);
	    	}
	    	else {
	    		$dalReward->update($transactionId, array('point'=>$point,'status'=>$status,'complete_time'=>$now));
	    	}
	    	//0:未承認 9:否承認	
	    	//承認状態（パラメータ名：status=1）
	        if ($status == 1) {	        		        		        			    	
		    	//update user gold
            	$dalIslandUser = Dal_Island_User::getDefaultInstance();           
            	$dalIslandUser->updateUserByField($uid, 'gold', $point);                        
	            //insert into pay log
	            $dalPayLog = Dal_PayLog::getDefaultInstance();
	            $dalPayLog->addLog($uid, $point, 0, -101, $now);		    	
	        }
            
            $this->_wdb->commit();
            $result = true;            
        }
        catch (Exception $e) {
        	info_log($e->getMessage(), 'rewardplus-error');
            $this->_wdb->rollBack();
            err_log('[Bll_Island_RewardPlus::addPoint]: ' . $e->getMessage());
        }

        return $result;
    }
}