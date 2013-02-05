<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/06/10    huch
 */
class Bll_Island_Pay extends Bll_Abstract
{
    public function order($payinfo)
    {
        $result = false;
        
        $dalIslandPayment = Dal_Island_Payment::getDefaultInstance();
        
        if($dalIslandPayment->getTradeNoStatus($payinfo['trade_no']))
        {
            return true;
        }
        
        try {
            $this->_wdb->beginTransaction();

            //insert into pay log
            $dalPayLog = Dal_PayLog::getDefaultInstance();
            $dalPayLog->addLog($payinfo['uid'], $payinfo['gold'], $payinfo['amont'], $payinfo['trade_no']);
            
            //update user gold
            $dalIslandUser = Dal_Island_User::getDefaultInstance();
            $dalIslandUser->updateUserByField($payinfo['uid'], 'gold', $payinfo['gold']);
            
            //update island_payment
            $payinfo['status'] = 1;
            
            $dalIslandPayment->update($payinfo['id'], $payinfo);
            
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            err_log('[Bll_Island_Pay::order]: ' . $e->getMessage());
        }
        
    	//send a cat //20100831 add send a house
        if ($payinfo['amont'] == 500) {
        	$newBuilding = array('uid' => $payinfo['uid'],
                                     'bid' => 41521,
                                     'status' => 0,
                                     'buy_time' => time(),
                                     'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
        }
        else if ($payinfo['amont'] == 1000) {
        	$newBuilding = array('uid' => $payinfo['uid'],
                                     'bid' => 41621,
                                     'status' => 0,
                                     'buy_time' => time(),
                                     'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
        }
    	else if ($payinfo['amont'] == 2000) {
        	$newBuilding = array('uid' => $payinfo['uid'],
                                     'bid' => 41721,
                                     'status' => 0,
                                     'buy_time' => time(),
                                     'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
        }
    	else if ($payinfo['amont'] == 3000) {
        	$newBuilding = array('uid' => $payinfo['uid'],
                                     'bid' => 41721,
                                     'status' => 0,
                                     'buy_time' => time(),
                                     'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 46821;
            $dalBuilding->addUserBuilding($newBuilding);
        }
        else if ($payinfo['amont'] == 5000) {
        	$newBuilding = array('uid' => $payinfo['uid'],
                                     'bid' => 41721,
                                     'status' => 0,
                                     'buy_time' => time(),
                                     'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 46821;
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 47421;
            $dalBuilding->addUserBuilding($newBuilding);
            /*$newBuilding = array('uid' => $payinfo['uid'],
                                     'bid' => 41521,
                                     'status' => 0,
                                     'buy_time' => time(),
                                     'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
            
            //add a house
            $dalPlant = Dal_Island_Plant::getDefaultInstance();
            $newPlant = array('uid' => $payinfo['uid'],
                                      'bid' => 49432,
                                      'status'=> 0,
                                      'item_id' => 494,
                                      'buy_time'=> time(),
                                      'item_type' => 32);
            $dalPlant->insertUserPlant($newPlant);*/
        	
        }
        //send 3 cat  //20100831 add send a house a bed a box
    	else if ($payinfo['amont'] == 10000) {
    		$newBuilding = array('uid' => $payinfo['uid'],
                                     'bid' => 41721,
                                     'status' => 0,
                                     'buy_time' => time(),
                                     'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 46821;
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 47421;
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 47521;
            $dalBuilding->addUserBuilding($newBuilding);
        	/*$newBuilding = array('uid' => $payinfo['uid'],
                                 'bid' => 41521,
                                 'status' => 0,
                                 'buy_time' => time(),
                                 'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 41621;
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 41721;
            $dalBuilding->addUserBuilding($newBuilding);
            
            //add a rope bed
            $newBuilding['bid'] = 49921;
            $dalBuilding->addUserBuilding($newBuilding);
            //add a house and a box
            $dalPlant = Dal_Island_Plant::getDefaultInstance();
            $newPlant = array('uid' => $payinfo['uid'],
                                      'bid' => 49432,
                                      'status'=> 0,
                                      'item_id' => 494,
                                      'buy_time'=> time(),
                                      'item_type' => 32);
            $dalPlant->insertUserPlant($newPlant);
            $newPlant['bid'] = 48831;
            $newPlant['item_id'] = 488;
            $newPlant['item_type'] = 31;
            $dalPlant->insertUserPlant($newPlant);*/
        }

        return $result;
    }
    
    
	public function webmoneyPay($orderId, $webmoneyPayId='')
    {
        $result = false;
        try {
	        $dalWebmoneyPay = Dal_Island_WebmoneyPay::getDefaultInstance();
	    	$rowPay = $dalWebmoneyPay->getById($orderId);
	    	if (empty($rowPay)) {
	    		return false;
	    	} 	
	        if ((int)$rowPay['complete'] > 0) {
	        	return true;
	        }
        
        
            $this->_wdb->beginTransaction();   
            
            $completeTime = time();
            //update user gold
            $dalIslandUser = Dal_Island_User::getDefaultInstance();           
            $dalIslandUser->updateUserByField($rowPay['uid'], 'gold', $rowPay['diamond']);            
            //update webmoneypay
            $dalWebmoneyPay->update($orderId, array('complete' => 1, 'complete_time' => $completeTime, 'webmoney_payid' => $webmoneyPayId));
            //insert into pay log
            $dalPayLog = Dal_PayLog::getDefaultInstance();
            $dalPayLog->addLog($rowPay['uid'], $rowPay['diamond'], $rowPay['money'], $webmoneyPayId, $completeTime);
                        
            $this->_wdb->commit();                    	
            $result = true;            
        }
        catch (Exception $e) {
        	info_log($e->getMessage(), 'kick');
            $this->_wdb->rollBack();
            err_log('[Bll_Island_Pay::webmoneyPay]: ' . $e->getMessage());
        }
        
    	//send a cat 
    	if ($rowPay['money'] == 500) {
    		 $newBuilding = array('uid' => $rowPay['uid'],
                                 'bid' => 41521,
                                 'status' => 0,
                                 'buy_time' => $completeTime,
                                 'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
    	}
    	else if ($rowPay['money'] == 1000) {
    		$newBuilding = array('uid' => $rowPay['uid'],
                                 'bid' => 41621,
                                 'status' => 0,
                                 'buy_time' => $completeTime,
                                 'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
    	}
    	else if ($rowPay['money'] == 2000) {
    		$newBuilding = array('uid' => $rowPay['uid'],
                                 'bid' => 41721,
                                 'status' => 0,
                                 'buy_time' => $completeTime,
                                 'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
    	}
    	else if ($rowPay['money'] == 3000) {
    		$newBuilding = array('uid' => $rowPay['uid'],
                                 'bid' => 41721,
                                 'status' => 0,
                                 'buy_time' => $completeTime,
                                 'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 46821;
            $dalBuilding->addUserBuilding($newBuilding);
    	}
        else if ($rowPay['money'] == 5000) {
            $newBuilding = array('uid' => $rowPay['uid'],
                                 'bid' => 41721,
                                 'status' => 0,
                                 'buy_time' => $completeTime,
                                 'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 46821;
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 47421;
            $dalBuilding->addUserBuilding($newBuilding);
        }
        else if ($rowPay['money'] == 10000) {
        	$newBuilding = array('uid' => $rowPay['uid'],
                                 'bid' => 41721,
                                 'status' => 0,
                                 'buy_time' => $completeTime,
                                 'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 46821;
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 47421;
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 47521;
            $dalBuilding->addUserBuilding($newBuilding);
        	/*$newBuilding = array('uid' => $rowPay['uid'],
                                 'bid' => 41521,
                                 'status' => 0,
                                 'buy_time' => $completeTime,
                                 'item_type' => 21);
            //add user Building
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 41621;
            $dalBuilding->addUserBuilding($newBuilding);
            $newBuilding['bid'] = 41721;
            $dalBuilding->addUserBuilding($newBuilding);
            
            //add a rope bed
            $newBuilding['bid'] = 49921;
            $dalBuilding->addUserBuilding($newBuilding);
            //add a house and a box
            $dalPlant = Dal_Island_Plant::getDefaultInstance();
            $newPlant = array('uid' => $rowPay['uid'],
                                      'bid' => 49432,
                                      'status'=> 0,
                                      'item_id' => 494,
                                      'buy_time'=> $completeTime,
                                      'item_type' => 32);
            $dalPlant->insertUserPlant($newPlant);
            $newPlant['bid'] = 48831;
            $newPlant['item_id'] = 488;
            $newPlant['item_type'] = 31;
            $dalPlant->insertUserPlant($newPlant);*/
        }

        return $result;
    }
}