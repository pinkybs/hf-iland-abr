<?php

require_once 'Dal/Abstract.php';

class Dal_Island_Payment extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $_table = 'island_payment';
    
    protected static $_instance;
    
    /**
     * getDefaultInstance
     *
     * @return Dal_Island_Payment
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    public function insert($info)
    {
        $this->_wdb->insert($this->_table, $info);
    }
    
    public function update($id, $info)
    {
        $where = $this->_wdb->quoteinto('id = ?', $id);
        $this->_wdb->update($this->_table, $info, $where);
    }
    
    public function getByToken($token)
    {
        $sql = "SELECT * FROM $this->_table WHERE token=:token";
        return $this->_rdb->fetchOne($sql, array('token' => $token));
    }
    
    public function getById($id)
    {
        $sql = "SELECT * FROM $this->_table WHERE id=:id";
        return $this->_rdb->fetchOne($sql, array('id' => $id));
    }
    
    public function getTradeNoStatus($no)
    {
        $sql = "SELECT status FROM $this->_table WHERE trade_no=:no";
        $result = $this->_rdb->fetchOne($sql, array('no' => $no));
        
        return $result == 1 ? true : false;
    }
}