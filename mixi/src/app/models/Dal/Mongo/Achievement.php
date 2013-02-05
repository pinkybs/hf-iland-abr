<?php

require_once 'Dal/Abstract.php';

class Dal_Mongo_Achievement extends Dal_Mongo_Abstract
{        
    protected static $_instance;
        
    /**
     * single instance of Dal_Mongo_Achievement
     *
     * @return Dal_Mongo_Achievement
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert achievement
     *
     * @param array $info
     * @return void
     */
    public function insertAchievement($info)
    {
        return $this->_mg->mixi_island->user_achievement->insert($info);
    }

    /**
     * update user achievement by field
     *
     * @param integer $uid
     * @param string $field
     * @param integer $change
     * @return void
     */
    public function updateUserAchievementByField($uid, $field, $change)
    {
        return $this->_mg->mixi_island->user_achievement->update(array('uid' => (string)$uid), array('$inc' => array($field => (int)$change)), array('upsert' => true));
    }
    
    /**
     * update user achievement
     *
     * @param integer $uid
     * @param array $info
     * @return void
     */
    public function updateUserAchievement($uid, $info)
    {
        return $this->_mg->mixi_island->user_achievement->update(array('uid' => (string)$uid), array('$set' => $info), array('upsert' => true));
    }

    /**
     * get user achievement info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserAchievement($uid)
    {
        $result = $this->_mg->mixi_island->user_achievement->findOne(array('uid' => (string)$uid));
        
        if ($result) {
            return $result;
        }
        
        return false;
    }

    /**
     * get user achievement info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserAchievementByField($uid, $field)
    {
        $result = $this->_mg->mixi_island->user_achievement->findOne(array('uid' => (string)$uid));
        
        if ( isset($result[$field])) {
            return $result[$field];
        }
        
        return false;
    }
    
/************************************** user_achievement_today ********************************************************/
    
    /**
     * insert today achievement
     *
     * @param array $info
     * @return void
     */
    public function insertTodayAchievement($info)
    {
        //$info['uid'] = (string)$info['uid'];
        $dateTime = date('Ymd');
        $name = 'user_achievement_today_' . $dateTime;
        
        return $this->_mg->mixi_island->$name->insert($info);
    }

    /**
     * update user today achievement by field
     *
     * @param integer $uid
     * @param string $field
     * @param integer $change
     * @return void
     */
    public function updateUserTodayAchievementByField($uid, $field, $change)
    {
        $dateTime = date('Ymd');
        $name = 'user_achievement_today_' . $dateTime;

        return $this->_mg->mixi_island->$name->update(array('uid' => (string)$uid), array('$inc' => array($field => (int)$change)), array('upsert' => true));
    }

    /**
     * update user today achievement
     *
     * @param integer $uid
     * @param array $info
     * @return void
     */
    public function updateTodayAchievement($uid, $info)
    {
        $dateTime = date('Ymd');
        $name = 'user_achievement_today_' . $dateTime;
        
        return $this->_mg->mixi_island->$name->update(array('uid' => (string)$uid), array('$set' => $info), array('upsert' => true));
    }
    
    /**
     * get user today achievement info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserTodayAchievement($uid)
    {
        $dateTime = date('Ymd');
        $name = 'user_achievement_today_' . $dateTime;
        
        $result = $this->_mg->mixi_island->$name->findOne(array('uid' => (string)$uid));
        
        if ($result) {
            return $result;
        }
        
        return false;
    }

    /**
     * get user today achievement info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserTodayAchievementByField($uid, $field)
    {
        $dateTime = date('Ymd');
        $name = 'user_achievement_today_' . $dateTime;
        
        $result = $this->_mg->mixi_island->$name->findOne(array('uid' => (string)$uid));
        
        if ($result) {
            return $result[$field];
        }
        
        return false;
    }

    /**
     * delete user achievement info
     *
     * @param integer $uid
     * @return array
     */
    public function deleteUserAchievement($uid)
    {        
        $this->_mg->mixi_island->user_achievement->remove(array('uid' => (string)$uid));
    }
    
    /**
     * delete user today achievement info
     *
     * @param integer $uid
     * @return array
     */
    public function deleteUserTodayAchievement($uid)
    {
        $dateTime = date('Ymd');
        $name = 'user_achievement_today_' . $dateTime;
        
        $this->_mg->mixi_island->$name->remove(array('uid' => (string)$uid));
    }
}