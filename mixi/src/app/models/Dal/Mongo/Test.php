<?php

class Dal_Mongo_Test extends Dal_Mongo_Abstract
{        
    protected static $_instance;
        
    /**
     * single instance of Dal_Mongo_Test
     *
     * @return Dal_Mongo_Test
     */
    public static function getDefaultInstance()
    {
        /*if (self::$_instance == null) {
            self::$_instance = new self();
        }*/

        //if (self::$_instance == null) {
            define('MONGODB_2', 'mongodb://10.245.227.111:27017');
        	$mongo = new Mongo(MONGODB_2, array('persist' => 'MONGODB_2', 'timeout' => 2000));
            self::$_instance = new self($mongo);
        //}
        
        return self::$_instance;
    }
    
    /**
     * get plant mooch info by uid
     *
     * @param integer $uid
     * @param integer $fid
     * @return array
     */
    public function getPlantMoochByUid($uid, $fid)
    {
        $cursor = $this->_mg->mixi_island->mooch_plant
                    ->find(array('uid' => (string)$uid, 'fid' => (string)$fid));
                    
        $result = array();
        while($cursor->hasNext()) {
            $v = $cursor->getNext();
            unset($v['_id']);
            unset($v['uid']);
            unset($v['fid']);
            $result[] = $v['id'];
        }
        
        return $result;
    }
    
    /**
     * delete plant mooch info
     *
     * @param integer $fid
     * @param integer $id
     * @return void
     */
    public function deletePlantMooch($fid, $id)
    {
        return $this->_mg->mixi_island->mooch_plant->remove(array('fid' => (string)$fid, 'id' => (string)$id));
    }

    public function dropTable($m = 6, $dayStart = 1, $dayEnd = 24)
    {
    	if ( $m < 10 ) {
    		$m = '0'.$m;
    	}
        for ( $i=$dayStart; $i<=$dayEnd; $i++ ){
            if ( $i < 10 ) {
                $tbName = 'user_achievement_today_2011'.$m.'0'.$i;
            }
            else {
                $tbName = 'user_achievement_today_2011'.$m.$i;
            }
            
            $this->_mg->mixi_island->$tbName->drop();
        }
    
        for ( $j=$dayStart; $j<=$dayEnd; $j++ ){
            if ( $j < 10 ) {
                $tbName = 'user_visit_2011'.$m.'0'.$j;
            }
            else {
                $tbName = 'user_visit_2011'.$m.$j;
            }
            
            $this->_mg->mixi_island->$tbName->drop();
        }
    
        for ( $k=$dayStart; $k<=$dayEnd; $k++ ){
            if ( $k < 10 ) {
                $tbName = 'user_daily_task_2011'.$m.'0'.$k;
            }
            else {
                $tbName = 'user_daily_task_2011'.$m.$k;
            }
            
            $this->_mg->mixi_island->$tbName->drop();
        }
    }
    
    public function addIndex($m = 7,$dayStart = 1, $dayEnd = 31)
    {        
    	if ( $m < 10 ) {
    		$m = '0'.$m;
    	}
        for ( $i=$dayStart; $i<=$dayEnd; $i++ ){
            if ( $i < 10 ) {
                $tbName = 'user_achievement_today_2011'.$m.'0'.$i;
            }
            else {
                $tbName = 'user_achievement_today_2011'.$m.$i;
            }
            
            $this->_mg->mixi_island->$tbName->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        }
    
        for ( $j=$dayStart; $j<=$dayEnd; $j++ ){
            if ( $j < 10 ) {
                $tbName = 'user_visit_2011'.$m.'0'.$j;
            }
            else {
                $tbName = 'user_visit_2011'.$m.$j;
            }
            
            $this->_mg->mixi_island->$tbName->ensureIndex( array( "uid" => 1,"fid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        }
    
        for ( $k=$dayStart; $k<=$dayEnd; $k++ ){
            if ( $k < 10 ) {
                $tbName = 'user_daily_task_2011'.$m.'0'.$k;
            }
            else {
                $tbName = 'user_daily_task_2011'.$m.$k;
            }
            
            $this->_mg->mixi_island->$tbName->ensureIndex( array( "uid" => 1, "tid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        }
    }
    
    public function addIndexAll()
    {
        $this->_mg->mixi_island->feedstatus->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->gift_send->ensureIndex( array( "sig" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->giftstatus->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->invite_log->ensureIndex( array( "sig" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->minifeed->ensureIndex( array( "uid" => 1) , array('unique' => false, 'dropDups' => true) );
        $this->_mg->mixi_island->minifeed->ensureIndex( array( "time_type" => 1 ) , array('unique' => false, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_friend_0->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_friend_1->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_friend_2->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_friend_3->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_friend_4->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_friend_5->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_friend_6->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_friend_7->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_friend_8->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_friend_9->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_user_0->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_user_1->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_user_2->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_user_3->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_user_4->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_user_5->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_user_6->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_user_7->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_user_8->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mixi_user_9->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        
        $this->_mg->mixi_island->remind->ensureIndex( array( "target" => 1 ) , array('unique' => false, 'dropDups' => true) );
        $this->_mg->mixi_island->remindstatus->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        
        $this->_mg->mixi_island->user_achievement->ensureIndex( array( "uid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->user_achievement_task->ensureIndex( array( "uid" => 1 ) , array('false' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->user_achievement_task->ensureIndex( array( "uid" => 1,"tid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->user_build_task->ensureIndex( array( "uid" => 1 ) , array('false' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->user_build_task->ensureIndex( array( "uid" => 1,"tid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->user_title->ensureIndex( array( "uid" => 1 ) , array('unique' => false, 'dropDups' => true) );
        $this->_mg->mixi_island->user_title->ensureIndex( array( "uid" => 1,"title" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->user_visit->ensureIndex( array( "uid" => 1,"fid" => 1 ) , array('unique' => true, 'dropDups' => true) );
    }

    /**
     * get plant mooch info by uid
     *
     * @param integer $uid
     * @param integer $fid
     * @return array
     */
    public function getAbnormalityFeed($uid, $pageIndex=1, $pageSize = 10000)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $cursor = $this->_mg->mixi_island->minifeed
                    ->find(array('uid' => $uid))
                    ->sort(array('create_time' => -1))
                    ->skip($start)
                    ->limit($pageSize);
        
        $result = array();
        
        $inviteCount = 0;
        $buildTaskCount = 0;
        $achievementCount = 0;
        $inviteUidList = array();
        $buildTaskList = array();
        $achievementList = array();
        
        while($cursor->hasNext()) {
            $v = $cursor->getNext();
            if (in_array($v['template_id'],array('7','10','11'))) {
                unset($v['_id']);
                $result[] = $v;
            }
            
            if ( $v['template_id'] == 7 ) {
                if ( !in_array($v['target'], $inviteUidList)) {
                    $inviteUidList[] = $v['target'];
                }
                else {
                    $inviteCount += 1;
                }
            }
            else if ( $v['template_id'] == 10 ) {
                if ( !in_array($v['title']['sendStr'], $buildTaskList)) {
                    $buildTaskList[] = $v['title']['sendStr'];
                }
                else {
                    $buildTaskCount += 1;
                }
            }
            else if ( $v['template_id'] == 11 ) {
                if ( !in_array($v['title']['title'], $achievementList)) {
                    $achievementList[] = $v['title']['title'];
                }
                else {
                    $achievementCount += 1;
                }
            }
        }
        
        $newResult = array('inviteCount' => $inviteCount,
                           'buildCount' => $buildTaskCount,
                           'achievementCount' => $achievementCount);
        
        return $newResult;
    }
}