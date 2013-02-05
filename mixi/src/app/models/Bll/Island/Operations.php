<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    xial
 */
class Bll_Island_Operations extends Bll_Abstract
{
	protected $table_user_plant = 'island_user_plant';
	protected $table_island_plant = 'island_island_plant';
	protected $table_user_building = 'island_user_building';
    /**
     * shut down/open island people
     * @param array $aryUser
     * @param integer $status 0- open 1 - shut down
     * @return array 
     */
    public function updateIslandPeopleStatus($aryUser,$status)
    {
    	$result = true;
    	if(count($aryUser) > 0){
            //update status for people
    		foreach($aryUser as $value){
    			//check the uid is existed
    			$isExisted = Bll_User::isAppUser($value);
    			if($isExisted){
					try {
						//update status for people
					    Bll_User::changeStatus($value, $status);
					}
					catch (Exception $e) {
					    info_log('[error_message]-[updateuser-status]:'.$e->getMessage(), 'Bll_Operations');
					    $result = FALSE;
					}
    			}
    		}
    	}
    	return $result;
    }
    
    /**
     * shut down/open island people
     * @param array $aryUser
     * @param integer $status 0- open 1 - shut down
     * @return array 
     */
    public function checkForbidden($aryUser)
    {
        $result = array();
        $dalBack = Dal_Island_Back::getDefaultInstance();
		//update status for people
		for($i=0;$i<count($aryUser);$i++){
			//check the uid is existed
			$isExisted = Bll_User::isAppUser($aryUser[$i]);
			$backInfo = $dalBack->getBackUpUserInfo($aryUser[$i]);
			if($isExisted){
				$f = Bll_User::isFibbden($aryUser[$i]);
				$result[$i]['uid'] = $aryUser[$i];
				$result[$i]['isForbidden'] = $f;
				
			} else {
				$result[$i]['uid'] = $aryUser[$i];
				$result[$i]['isForbidden'] = 2;
			}
			$result[$i]['isClean'] = $backInfo['isClean'];
		}
        $strResult = Zend_Json::encode($result);
        return $strResult;
    }

    /**
     * clean island people's gold exp
     * @param array $aryUser
     * @param integer $status 0- open 1 - shut down
     * @return array 
     */
    public function cleanUserInfo($aryUser)
    {
        $rtnInfo = '';
        $dalUser = Dal_Island_User::getDefaultInstance();
        $dalBack = Dal_Island_Back::getDefaultInstance();
		//update status for people
		//$this->_wdb->beginTransaction();
        for($i=0;$i<count($aryUser);$i++){
			//check the uid is existed
			$info= array('coin'=>0,'exp'=>0,'level'=>1,'island_level'=>1,'next_level_exp'=>40,'praise'=>0);
			$userInfo = $dalUser->getUserLevelInfo($aryUser[$i]);
			//$userInfo = Hapyfish_Island_Cache_User::getLevelInfo($aryUser[$i]);
			if($userInfo){
				$info_back = array('backup_coin'=>$userInfo['coin'],
				'backup_exp'=>$userInfo['exp'],
				'backup_level'=>$userInfo['level'],
				'backup_island_level'=>$userInfo['island_level'],
				'backup_next_level_exp'=>$userInfo['next_level_exp'],
				'isClean' => 1);
				$backInfo = $dalBack->getBackUpUserInfo($aryUser[$i]);
				try {
					//back up info 
					if($backInfo){
					   $info_back['last_process_time'] = time();
					   $result = $dalBack->updateBackUser($aryUser[$i],$info_back);
					} else {
					   $info_back['uid']= $aryUser[$i];
					   $result = $dalBack->insertUser($info_back);
					}
					//update status for people
					$dalUser->updateUser($aryUser[$i],$info);
					$dalBack->updateUserBuilding($aryUser[$i]);
					$dalBack->updateUserPlant($aryUser[$i]);
					
		            Hapyfish_Island_Cache_Background::cleanUsingBackground($aryUser[$i]);
		            Hapyfish_Island_Cache_Building::cleanUsingBuilding($aryUser[$i]);
		            Hapyfish_Island_Cache_Dock::cleanUserPositionList($aryUser[$i]);
		            Hapyfish_Island_Cache_User::cleanPositionCount($aryUser[$i]);
		
		            Hapyfish_Island_Cache_Dock::cleanUserShipList($aryUser[$i]);
		            
		            Bll_Cache_Island_User::clearCache('getUserHelpInfo', $aryUser[$i]);
		            Hapyfish_Island_Cache_Plant::cleanUserUsingPlantBasicInfo($aryUser[$i]);
		            Hapyfish_Island_Cache_Plant::cleanCurrentlyVistor($aryUser[$i]);
		            Hapyfish_Island_Cache_User::cleanLevelInfo($aryUser[$i]);
		            Hapyfish_Island_Cache_User::cleanPraise($aryUser[$i]);
		            Hapyfish_Island_Cache_User::cleanExp($aryUser[$i]);

					//$this->_wdb->commit();
					//update the satus for user's plant
					$rtnInfo[$i]['uid'] = $aryUser[$i];
					$rtnInfo[$i]['info']= $info_back;
				} catch (Exception $e) {
					//$this->_wdb->rollback();
					info_log('[error_message]-[updateuser-status]:'.$e->getMessage(), 'Bll_Operations');
				}
				
			}
		}
		$strResult = Zend_Json::encode($rtnInfo);
		return $strResult;
    }
    
   /**
     * resume island people's gold exp
     * @param array $aryUser
     * @param integer $status 0- open 1 - shut down
     * @return array 
     */
    public function resumeUserInfo($aryUser)
    {
    	$result = false;
        $dalUser = Dal_Island_User::getDefaultInstance();
        $dalBack = Dal_Island_Back::getDefaultInstance();
        //update status for people
        foreach($aryUser as $value){
            //check the uid is existed
            $backInfo = $dalBack->getBackUpUserInfo($value);
            if($backInfo){
                //$info_back = array ('gold'=>$backInfo['backup_gold'],'exp'=>$backInfo['backup_exp']);
                $info_back = array('coin'=>$backInfo['backup_coin'],
                'exp'=>$backInfo['backup_exp'],
                'level'=>$backInfo['backup_level'],
                'island_level'=>$backInfo['backup_island_level'],
                'next_level_exp'=>$backInfo['backup_next_level_exp']);
                try {
                	 $result = $dalBack->updateBackUser($value,array('isClean'=>0));
                      //update status for people
                      $updateResult = $dalUser->updateUser($value,$info_back);
                } catch (Exception $e) {
                    info_log('[error_message]-[updateuser-status]:'.$e->getMessage(), 'Bll_Operations');
                    $result = false;
                }
                $result = true;
                Hapyfish_Island_Cache_User::cleanLevelInfo($value);
                Hapyfish_Island_Cache_User::cleanExp($value);
            }
        }
        return $result;
    }
    
    public function addMoney($aryUser,$gold,$coin)
    {
    	$result = array();
    	for($i=0;$i<count($aryUser);$i++){
    		$result[$i]['uid'] = $aryUser[$i];
	    	$isAppUser = Bll_User::isAppUser($aryUser[$i]);
			if ( !$isAppUser ) {
				$result[$i]['status'] = -1; 
				$result[$i]['content'] = '没有参加应用。';
				continue;
			}
			//get user for update
            /*$forUpdateUser = $dalUser->getUserForUpdate($aryUser[$i]);
                        //update user
            $newUser = array('gold' => $forUpdateUser['gold'] + $gold, 
                             'coin' => $forUpdateUser['coin'] + $coin);*/
            try{
            	if(!empty($gold)){
            		Hapyfish_Island_Cache_User::incGold($aryUser[$i],$gold);
            	}
                if(!empty($coin)){
                    Hapyfish_Island_Cache_User::incCoin($aryUser[$i],$coin);
                }
                //$dalUser->updateUser($aryUser[$i], $newUser);
			$result[$i]['status'] = 1;
			}  catch (Exception $e) {
				err_log($e->getMessage());
				$result[$i]['status'] = -2;
				$result[$i]['content'] = '数据库出错。';
			}
		}
		return Zend_Json::encode($result);
    }
    
    public function addCard($aryUser,$cid,$count)
    {
    	$result = array();
    	$dalCard = Dal_Island_Card::getDefaultInstance();
    	for($i=0;$i<count($aryUser);$i++){
            $newCard = array('uid' => $aryUser[$i],
                             'cid' => $cid,
                             'count' => $count,
                             'buy_time' => time(),
                             'item_type' => 41);
            //add user card
            try {
	            $dalCard->addUserCard($newCard);
	            $result[$i]['status'] = 1;
            } catch (Exception $e) {
            	err_log($e->getMessage());
            	$result[$i]['status'] = -2;
				$result[$i]['content'] = '数据库出错。';
            }
        }
        return Zend_Json::encode($result);;
    }
    
    public function givePresent($aryUser,$aryPresentInfo)
    {
    	$result = array();
    	$sql = '';
    	$table = '';
    	$dalBack = Dal_Island_Back::getDefaultInstance();
    	foreach($aryPresentInfo as $value){
	    	if($value['type'] == 1){
	    		$presentInfo = Bll_Cache_Island::getPlantById($value['cid']);
		        if ( !$presentInfo ) {
		            $result[$value['type']]['content'] = '设施不存在';
		            continue;
		        }
		        $table = $this->table_user_plant;
	        } elseif($value['type'] == 2){
	        	$presentInfo = Bll_Cache_Island::getBuildingById($value['cid']);
		        if ( !$presentInfo ) {
		            $result[$value['type']]['content'] = '建筑不存在。';
		            continue;
		        }
		         $table = $this->table_user_building;
	        } elseif($value['type'] == 3){
                $presentInfo = Bll_Cache_Island::getCardById($value['cid']);
                if ( !$presentInfo ) {
                    $result[$value['type']]['content'] = '道具卡不存在。';
                    continue;
                }
                 //$table = $this->table_user_card;
                 $table = 'island_user_card';
            }

	    	for($i=0;$i<count($aryUser);$i++){
	    		if($value['type']== 3){
	    			$reCount = $dalBack->isHaveCardById($aryUser[$i],$value['cid']);
	    			if($reCount){
	    				$sql .= "UPDATE ".$table." SET count = count + ".$value['count']." WHERE cid=".$value['cid']." AND uid=".$aryUser[$i]."; ";
	    			} else {
	    			    $sql .= "insert into " . $table . "(uid,cid,count,buy_time,item_type) values("
                            . $aryUser[$i]. "," . $value['cid'] . ",".$value['count']."," . time() . "," . $presentInfo['item_type']."); ";
	    			}
	    		} else if($value['type']== 1){
		    		$table_name = $this->getTableName($aryUser[$i],$table);
		    		for ($j=0;$j<$value['count'];$j++){
			            $sql .= "insert into " . $table_name . "(uid,bid,status,item_id,buy_time,item_type) values("
			            	. $aryUser[$i]. "," . $value['cid'] . ",0," .$presentInfo['item_id'].",". time() . "," . $presentInfo['item_type']."); ";
		            }
	    		} else if($value['type']== 2){
                    $table_name = $this->getTableName($aryUser[$i],$table);
                    for ($j=0;$j<$value['count'];$j++){
                        $sql .= "insert into " . $table_name . "(uid,bid,status,buy_time,item_type) values("
                            . $aryUser[$i]. "," . $value['cid'] . ",0," . time() . "," . $presentInfo['item_type']."); ";
                    }
                }
	        }
        }

        if(empty($result)&& !empty($sql)){
            //add user buliding
	        try {
	            $dalBack->updateUserPresent($sql);
	            for($i=0;$i<count($aryUser);$i++){
		            Hapyfish_Island_Cache_Building::cleanUsingBuilding($aryUser[$i]);
		            Hapyfish_Island_Cache_Dock::cleanUserShipList($aryUser[$i]);
	            }
	            $result['status'] = 1;
	        } catch (Exception $e) {
	        	err_log($e->getMessage());
	        	$result['status'] = -2;
				$result['content'] = '数据库出错。';
	        }
        }
        
        return Zend_Json::encode($result);
    }
    
    public function getPresentInfo()
    {
    	$dalBack = Dal_Island_Back::getDefaultInstance();
    	
        $plant = $dalBack->getPresenPlantList();
        $building = $dalBack->getPresenBuildingList();
        $card = Bll_Cache_Island::getCardList();
        $relPlant = array();
        for($i=0;$i<count($plant);$i++){
        	$relPlant[$i]['cid'] =  $plant[$i]['cid'];
        	$relPlant[$i]['name'] =  $plant[$i]['name'];
        }
        for($i=0;$i<count($building);$i++){
            $relBuiding[$i]['cid'] =  $building[$i]['cid'];
            $relBuiding[$i]['name'] =  $building[$i]['name'];
        }
        for($i=0;$i<count($card);$i++){
            $relCard[$i]['cid'] =  $card[$i]['cid'];
            $relCard[$i]['name'] =  $card[$i]['name'];
        }
        
        $result = array('plant'=>$relPlant,'building'=>$relBuiding,'card'=>$relCard);
        return Zend_Json::encode($result);
    }
    
    public function getTableName($uid, $tbname = null)
    {
        if ( $tbname == null ) {
            $tbname = $this->table_user_plant;
        }
        $n = $uid % 10;
        return $tbname . '_' . $n;
    }
	
	public function updateAdvertisement($info)
    {
    	if(empty($info)){
    		return false;
    	}
        try {
        	$dalBack = Dal_Island_Back::getDefaultInstance();
        	if(empty($info['id'])){
        		$dalBack->insertNotice($info);
        	} else {
	            $aryNotice = $dalBack->getNoticeById($info['id']);
	        	if(!empty($aryNotice)){
	        		$dalBack->updateNotice($info['id'],$info);
	        	}
        	}
            return true;
        } catch (Exception $e) {
        	info_log('updatenotice'.$e->getMessage(),'Bll_Operations');
            return false;
        }
    }
	
	public function getNotice()
    {  
        $dalBack = Dal_Island_Back::getDefaultInstance();
        $result = $dalBack->getAllNotcie();
        return Zend_Json::encode($result);
    }
   
}