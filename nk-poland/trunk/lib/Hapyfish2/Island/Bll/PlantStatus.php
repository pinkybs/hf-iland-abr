<?php

class Hapyfish2_Island_Bll_PlantStatus
{
	/**
	 * out island
	 * @param integer $uid
	 */
	public static function outIslandPeople($uid, &$plants, $islandId)
	{
		if (!$plants) {
			return;
		}
		
		$lastOutIslandPeopleTime = Hapyfish2_Island_Cache_PlantStatus::getLastOutIslandPeopleTime($uid, $islandId);
		$now = time();
		$seconds = $now - $lastOutIslandPeopleTime;
		
	    //< 2 minutes
        if ($seconds < 120) {
            return;
        }
		
		$canDo = Hapyfish2_Island_Cache_PlantStatus::canOutIslandPeople($uid, $islandId);
		if (!$canDo) {
			return;
		}
		
		Hapyfish2_Island_Cache_PlantStatus::updateLastOutIslandPeopleTime($uid, $now, $islandId);
		
		$currentlyVisitor = 0;
		$addExp = 0;
		$OUT_TIME = 120;

		try {
			foreach ($plants as &$value) {
				if ($value['wait_visitor_num'] > 0) {
					$canDoItem = Hapyfish2_Island_Cache_PlantStatus::canOutPlantPeopleOfItem($uid, $value['id']);
					if (!$canDoItem) {
						continue;
					}
                    
                    //plant default action time
					$ACTION_TIME = round($value['pay_time'] * 0.6);
                    
					$cnt = 0;
					$payTime = $now - $value['start_pay_time'] - $value['delay_time'];
					//up to now action
                    $peopleCnt = min($value['wait_visitor_num'], floor($seconds / $OUT_TIME));

                    if ($value['can_find'] < 1) {
                        $cnt = $peopleCnt;
                        $type = 1;
                    }//plant hava event
					else if ($value['event'] > 0) {
						$type = 2;
						if ($payTime >= $ACTION_TIME) {
							$type = 3;
						    $faultTime = $value['start_pay_time'] + $ACTION_TIME;
						    $afterFaultTime = $now - $faultTime;
						    
						    $payCount = floor($ACTION_TIME / $OUT_TIME);
                            if ($peopleCnt > $payCount) {
                            	$type = 4;
	                            //after fault
	                            if ($afterFaultTime > 0) {
	                            	$type = 5;
	                                $s = min($afterFaultTime, $seconds);
	                                $noPayCount = $peopleCnt - $payCount;
	                                $cnt = min($noPayCount, floor($s / $OUT_TIME));
	                                //$cnt = min($value['wait_visitor_num'], floor($s / $OUT_TIME));
	                            }
                            }
						}
					}//checkout after
					else if ($value['pay_time'] <= $payTime && $value['start_pay_time']) {
						$type = 6;
					    $balanceTime = $value['start_pay_time'] + $value['delay_time'] + $value['pay_time'];
                        $afterBalanceTime = $now - $balanceTime;

                        $payCount = floor(($value['delay_time'] + $value['pay_time']) / $OUT_TIME);
                        if ($peopleCnt > $payCount) {
                        	$type = 7;
						    if ($afterBalanceTime > 0) {
						    	$type = 8;
	                            $s = min($afterBalanceTime, $seconds);
	                            
	                            $noPayCount = $peopleCnt - $payCount;
	                            $cnt = min($noPayCount, floor($s / $OUT_TIME));
	                        }
                        }
					}
					//visit add exp
                    $addCount = $peopleCnt - $cnt;
                    
                    if ($addCount > 0) {
                    	$addDeposit = $addCount * $value['ticket'];
                    	$addExp += $addCount;
                    	$value['deposit'] = $value['deposit'] + $addDeposit;
                    	$value['start_deposit'] = $value['start_deposit'] + $addDeposit;
                    }
                    
                    $value['wait_visitor_num'] = $value['wait_visitor_num'] - $peopleCnt;
                    
                    Hapyfish2_Island_HFC_Plant::updateOne($uid, $value['id'], $value);
				}
			}

            //update user info
			if ($addExp > 0) {
				//check double exp
				$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
				$doubleexpCardTime = $userCardStatus['doubleexp'];
				if ($doubleexpCardTime - $now > 0) {
					$addExp = $addExp*2;
				}
				
				Hapyfish2_Island_HFC_User::incUserExp($uid, $addExp);
				$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
			}
		}
		catch (Exception $e) {
			
		}
	}
	
    /**
     * out plant by item id
     * 
     * @param integer $uid
     * @param integer $itemId
     * @return void
     */
    public static function outPlantPeople($uid, &$plant, $now)
    {
    	if (!$plant) {
			return false;
		}
		
		$itemId = $plant['id'];
    	//
        $userVO = Hapyfish2_Island_HFC_User::getUserVO($uid);
	    $userCurrentIsland = $userVO['current_island'];
	    
    	$lastOutIslandPeopleTime = Hapyfish2_Island_Cache_PlantStatus::getLastOutIslandPeopleTime($uid, $userCurrentIsland);
    	$lastOutPlantPeopleTime = Hapyfish2_Island_Cache_PlantStatus::getLastOutPlantPeopleTime($uid, $itemId, $userCurrentIsland);
    	
    	$outTime = max($lastOutIslandPeopleTime, $lastOutPlantPeopleTime);
        
        $seconds = $now - $outTime;

        //last login time < 2 minute
        if ($seconds < 120) {
            return false;
        }
    	
    	$canDoItem = Hapyfish2_Island_Cache_PlantStatus::canOutPlantPeopleOfItem($uid, $itemId);
		if (!$canDoItem) {
			return false;
		}
		
		Hapyfish2_Island_Cache_PlantStatus::updateLastOutPlantPeopleTime($uid, $itemId, $now);
		
		if ($plant['wait_visitor_num'] < 1) {
			return false;
		}

        $addExp = 0;
        $OUT_TIME = 120;
        //plant default action time
        $ACTION_TIME = round($plant['pay_time'] * 0.6);
        $cnt = 0;
        //up to now action
        $payTime = $now - $plant['start_pay_time'] - $plant['delay_time'];
        $peopleCnt = min($plant['wait_visitor_num'], floor($seconds / $OUT_TIME));
        
        if ($peopleCnt < 1) {
        	return false;
        }
        
        if ($plant['can_find'] < 1) {
            $cnt = $peopleCnt;
        }//plant hava event
        else if ($plant['event'] > 0) {
            if ($payTime >= $ACTION_TIME) {
	            $faultTime = $plant['start_pay_time'] + $ACTION_TIME;
	            $afterFaultTime = $now - $faultTime;
	                            
	            $payCount = floor($ACTION_TIME / $OUT_TIME);
	            if ($peopleCnt > $payCount) {
		            //after fault
		            if ($afterFaultTime > 0) {
			            $s = min($afterFaultTime, $seconds);
			            $noPayCount = $peopleCnt - $payCount;
			            $cnt = min($noPayCount, floor($s / $OUT_TIME));
                    }
                }
            }
        }//checkout after
        else if ($plant['pay_time'] <= $payTime && $plant['start_pay_time'] ) {
            $balanceTime = $plant['start_pay_time'] + $plant['delay_time'] + $plant['pay_time'];
            $afterBalanceTime = $now - $balanceTime;

            $payCount = floor(($plant['delay_time'] + $plant['pay_time']) / $OUT_TIME);
            if ( $peopleCnt > $payCount ) {
                if ( $afterBalanceTime > 0 ) {
                    $s = min($afterBalanceTime, $seconds);
                    $noPayCount = $peopleCnt - $payCount;
                    $cnt = min($noPayCount, floor($s / $OUT_TIME));
                }
            }
        }
		//visit add exp
		$addCount = $peopleCnt - $cnt;
		if ($addCount > 0) {
			$addExp += $addCount;
			$addDeposit = $addCount * $plant['ticket'];
			$plant['deposit'] = $plant['deposit'] + $addDeposit;
			$plant['start_deposit'] = $plant['start_deposit'] + $addDeposit;
		}
		
		$plant['wait_visitor_num'] = $plant['wait_visitor_num'] - $peopleCnt;

		Hapyfish2_Island_HFC_Plant::updateOne($uid, $plant['id'], $plant);
		
        //update user info
        if ($addExp > 0) {
			//check double exp
			$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
			$doubleexpCardTime = $userCardStatus['doubleexp'];
			if ($doubleexpCardTime - $now > 0) {
				$addExp = $addExp*2;
			}
			
			Hapyfish2_Island_HFC_User::incUserExp($uid, $addExp);
			$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
        }
        
        return true;
    }

}