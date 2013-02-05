<?php

require_once 'Dal/Abstract.php';

class Dal_Mongo_Friend extends Dal_Mongo_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_friend = 'mixi_friend';
    
    protected static $_instance;
        
    /**
     * single instance of Dal_Mongo_Friend
     *
     * @return Dal_Mongo_Friend
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getTableName($uid)
    {
        $n = $uid % 10;
        return $this->table_friend . '_' . $n;
    }
    
    public function getFriends($uid)
    {
        $tname = $this->getTableName($uid);
                
        $result = $this->_mg->mixi_island->$tname->findOne(array('uid' => $uid));
        
        if ( $result ) {
            return $result['fids'];
        }
        return null;
    }

    public function insertFriend($uid, $fids)
    {
        $tname = $this->getTableName($uid);
        $this->_mg->mixi_island->$tname->update(array('uid' => $uid), array('$set' => array('fids' => $fids)), array('upsert' => true));
    }
        
    public function deleteFriend($uid)
    {
        $tname = $this->getTableName($uid);
                
        $this->_mg->mixi_island->$tname->remove(array('uid' => $uid));
    }
}