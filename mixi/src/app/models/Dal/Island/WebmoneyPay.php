<?php

require_once 'Dal/Abstract.php';

class Dal_Island_WebmoneyPay extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $_table = 'island_webmoney_pay';
    
    protected static $_instance;
    
    /**
     * getDefaultInstance
     *
     * @return Dal_Island_WebmoneyPay
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
    
    public function update($orderId, $info)
    {
        $where = $this->_wdb->quoteinto('order_id=?', $orderId);
        $this->_wdb->update($this->_table, $info, $where);
    }
    
	public function getById($orderId)
    {
        $sql = "SELECT * FROM $this->_table WHERE order_id=:order_id ";
        return $this->_rdb->fetchRow($sql, array('order_id' => $orderId));
    }
    
    public function getByPayid($payId)
    {
        $sql = "SELECT * FROM $this->_table WHERE webmoney_payid=:webmoney_payid ";
        return $this->_rdb->fetchRow($sql, array('webmoney_payid' => $payId));
    }
    
	public function getHistoryByUid($uid)
    {
        $sql = "SELECT * FROM $this->_table WHERE uid=:uid AND complete>0 ";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
}