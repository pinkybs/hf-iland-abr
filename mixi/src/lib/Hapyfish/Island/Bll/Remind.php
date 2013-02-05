<?php

class Hapyfish_Island_Bll_Remind
{
	public static function getRemindStatus($uid, $fid)
	{
        //$remindArray = array();
        $dalRemind = Dal_Mongo_Remind::getDefaultInstance();
        $remindList = $dalRemind->getRemind($fid);
        $remindTime1 = 0;
        $remindTime2 = 0;
        $remindTime3 = 0;
        $remindTime4 = 0;
        $nowTime = time();
        foreach ( $remindList as $key => $remind ) {
            if ( $remind['type'] == 1 && $remindTime1 < 1 ) {
                $remindTime1 = $remind['create_time'];
            }
            if ( $remind['type'] == 2 && $remindTime2 < 1 ) {
                $remindTime2 = $remind['create_time'];
            }
            if ( $remind['type'] == 3 && $remind['actor'] == $uid && $remindTime3 < 1 ) {
                $remindTime3 = $remind['create_time'];
            }
            if ( $remind['type'] == 4 && $remind['actor'] == $uid && $remindTime4 < 1 ) {
                $remindTime4 = $remind['create_time'];
            }
        }
        $canSend1 = 1;
        $canSend2 = 1;
        $canSend3 = 1;
        $canSend4 = 1;
        if ( ($nowTime - $remindTime1) <= 6*3600 ) {
            $canSend1 = 0;
        }
        if ( ($nowTime - $remindTime2) <= 6*3600 ) {
            $canSend2 = 0;
        }        
        if ( ($nowTime - $remindTime3) <= 3600 ) {
            $canSend3 = 0;
        }    
        if ( ($nowTime - $remindTime4) <= 3600 ) {
            $canSend4 = 0;
        }
        $remindStatus = array('1' => $canSend1,'2' => $canSend2,'3' => $canSend3,'4' => $canSend4);
        
        return $remindStatus;
	}
	
    /**
     * add remind
     *
     * @return array
     */
    public static function addRemind($uid, $fid, $content, $type)
    {
    	$result = array('status' => -1);
    	
    	$nowTime = time();
    	$newRemind = array('actor' => $uid,
    	                   'target' => $fid,
    	                   'content' => $content,
    	                   'type' => $type,
    	                   'create_time' => $nowTime);
    	
        $dalRemind = Dal_Mongo_Remind::getDefaultInstance();

        $dalRemind->insertRemind($newRemind);

        //update user remind status
        Hapyfish_Island_Cache_Counter::incNewRemindCount($newRemind['target']);
        
        if ( $type > 0 ) {
        	switch ( $type ) {
        		case 1 :
        			$messageType = 'REMIND_1';
        			break;
                case 2 :
                    $messageType = 'REMIND_2';
                    break;
                case 3 :
                    $messageType = 'REMIND_3';
                    break;
                case 4 :
                    $messageType = 'REMIND_4';
                    break;
        	}
        	
        	Bll_Island_Message::send($messageType, $uid, $fid);
	        //insert minifeed[
	        /*$minifeed = array('uid' => $fid,
	                          'template_id' => $templateId,
	                          'actor' => $uid,
	                          'target' => $fid,
	                          'type' => $type,
	                          'create_time' => $nowTime);
	        Hapyfish_Island_Bll_Feed::insertMiniFeed($minifeed);*/
        }
        
        $result['status'] = 1;
        return $result;
    }
    
   /**
     * get remind
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public static function getRemind($uid, $pageIndex = 1, $pageSize = 50)
    {
        $dalRemind = Dal_Mongo_Remind::getDefaultInstance();
        //get user remind
        $remindList = $dalRemind->getRemind($uid, $pageIndex, $pageSize);
        
        Bll_User::appendPeople($remindList, 'actor');

        $result = array();
        foreach ( $remindList as $remind ) {
        	$value = array('fromUid' => $remind['actor'],
                           'fromUserName' => $remind['name'],
                           'fromUserFace' => $remind['face'],
                           'sendDate' => $remind['create_time'],
                           'content' => $remind['content']);
            $result[] = $value;
        }
        
        //update user feed status
        Hapyfish_Island_Cache_Counter::clearNewRemindCount($uid);
        
        return $result;
    }

}