<?php


class Hapyfish_Island_Dal_Login extends Hapyfish_Island_Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user = 'island_user_info';

    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish_Island_Dal_Login
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getTableName($uid, $tbname = null)
    {
        $n = $uid % 10;
        return $tbname . '_' . $n;
    }
    
    public function getLastLoginTime($uid)
    {
        $sql = "SELECT last_login_time FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    public function getTodayLoginCount($uid)
    {
        $sql = "SELECT today_login_count FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    public function getActivityLoginCount($uid)
    {
        $sql = "SELECT activity_login_count FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    public function updateLastLoginTime($uid, $time)
    {
        $sql = "UPDATE $this->table_user SET last_login_time=:time WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid, 'time' => $time));    	
    }
    
    public function updateTodayLoginCount($uid, $count)
    {
        $sql = "UPDATE $this->table_user SET today_login_count=:count WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid, 'count' => $count));    	
    }
    
    public function updateActivityLoginCount($uid, $count)
    {
        $sql = "UPDATE $this->table_user SET activity_login_count=:count WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid, 'count' => $count));      	
    }
    
    public function updateLoginInfo($uid, $info)
    {
        $where = $this->_wdb->quoteinto('uid = ?', $uid);
        $this->_wdb->update('island_user_info', $info, $where);
    }

}