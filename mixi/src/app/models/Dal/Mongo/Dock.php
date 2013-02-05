<?php

class Dal_Mongo_Dock extends Dal_Mongo_Abstract
{        
    protected static $_instance;
        
    /**
     * single instance of Dal_Mongo_Dock
     *
     * @return Dal_Mongo_Dock
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert user ship info
     *
     * @param array $info
     * @return integer
     */
    public function insertUserShip($info)
    {        
        return $this->_mg->mixi_island->user_ship->update(array('pid' => $info['pid'],'uid' => $info['uid']), array('$push' => array('shipIds' => $info['ship_id'])), array('upsert' => true));
    }
    
    /**
     * get user boat list
     *
     * @param integer $uid
     * @return string
     */
    public function getUserShipList($uid, $pid)
    {
        $result = $this->_mg->mixi_island->user_ship->findOne(array('uid' => (string)$uid, 'pid' => (string)$pid));
        
        if ( $result ) {
            return $result['shipIds'];
        }
        
        return array();
    }

    /**
     * get user ship by ship id 
     *
     * @param integer $uid
     * @param integer $ShipId
     * @return boolean
     */
    public function hasUserShipById($uid, $pid, $shipId)
    {
        $result = $this->_mg->mixi_island->user_ship->findOne(array('uid' => (string)$uid, 'pid' => (string)$pid));
        
        if ($result) {
            return in_array($shipId, $result['shipIds']);
        }
        
        return false;
    }

    /**
     * insert user ship flag info
     *
     * @param array $uid
     */
    public function insertUserFlag($uid)
    {        
        $this->_mg->mixi_island->user_ship_flag->insert(array('uid' => (string)$uid));
    }

    /**
     * has user flag
     *
     * @param array $uid
     * @return boolean
     */
    public function hasUserFlag($uid)
    {        
        $result = $this->_mg->mixi_island->user_ship_flag->findOne(array('uid' => (string)$uid));
        
        if ($result) {
            return true;
        }
        
        return false;
    }
    
}