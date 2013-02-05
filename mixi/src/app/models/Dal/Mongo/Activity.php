<?php

class Dal_Mongo_Activity extends Dal_Mongo_Abstract
{        
    protected static $_instance;
        
    /**
     * single instance of Dal_Mongo_Activity
     *
     * @return Dal_Mongo_Activity
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert activity
     *
     * @param array $info
     * @return boolean
     */
    public function insertActivity($info)
    {
        return $this->_mg->mixi_island->user_activity->insert($info);
    }
            
    public function checkUserActivity($uid)
    {
        $result = $this->_mg->mixi_island->user_activity->findOne(array('uid' => $uid));
        if ($result) {
            return true;
        }
        return false;
    }
    
    public function updateUserLastTime($uid, $info)
    {
    	$this->_mg->mixi_island->user_login_time->update(array('uid' => $uid), array('$set' => $info), array('upsert' => true));
    }
    
    public function getUserLogin($uid)
    {
    	$result = $this->_mg->mixi_island->user_login_time->findOne(array('uid' => $uid));
    	return $result;
    }
}