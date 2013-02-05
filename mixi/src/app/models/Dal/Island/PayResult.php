<?php

require_once 'Dal/Abstract.php';

class Dal_Island_PayResult extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $_table = 'island_pay_result';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    public function add($result)
    {
        $this->_wdb->insert($this->_table, $result);
    }
    
    /**
     * get user pay list
     *
     * @param integer $uid
     * @return array
     */
    public function getUserPayList($uid)
    {
        $sql = "SELECT order_id,uid,amount,desc,gold,order_time FROM $this->_table WHERE uid=:uid AND completed=1 ORDER BY completed_time ";

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
}