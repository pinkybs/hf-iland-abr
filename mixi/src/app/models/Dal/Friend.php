<?php

require_once 'Dal/Abstract.php';

class Dal_Friend extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_friend = 'mixi_friend';
    
    protected static $_instance;
    
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
        
        $sql = "SELECT fid FROM $tname WHERE uid=:uid";
        
        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));
        
        $fids = array();
        if ($result) {
            foreach ($result as $row) {
                $fids[] = $row['fid'];
            }
        }
        
        return $fids;
    }   
        
    public function deleteFriend($uid, $fid)
    {
        $tname1 = $this->getTableName($uid);
        $tname2 = $this->getTableName($fid);
        
        $sql = "DELETE FROM $tname1 WHERE uid=:uid AND fid=:fid";
        
        $sql2 = "DELETE FROM $tname2 WHERE uid=:uid AND fid=:fid";
        
        $this->_wdb->query($sql, array('uid' => $uid, 'fid' => $fid));
        $this->_wdb->query($sql2, array('uid' => $fid, 'fid' => $uid));
    }
        
    public function insertFriend($uid, $fid)
    {
        $tname1 = $this->getTableName($uid);
        $tname2 = $this->getTableName($fid);
        
        $sql = "INSERT IGNORE INTO $tname1(uid, fid) VALUES(:uid, :fid)";
        
        $sql2 = "INSERT IGNORE INTO $tname2(uid, fid) VALUES(:uid, :fid)";
        
        $this->_wdb->query($sql, array('uid' => $uid, 'fid' => $fid));
        $this->_wdb->query($sql2, array('uid' => $fid, 'fid' => $uid));
    }
}