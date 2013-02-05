<?php

require_once 'Dal/Abstract.php';

class Dal_PayOrder extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $_table = 'renren_pay_order';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    public function getOrder($order_id)
    {        
        $sql = "SELECT * FROM $this->_table WHERE order_id=:order_id";
        
        return $this->_rdb->fetchRow($sql, array('order_id' => $order_id));
    }
    
    public function regOrder($order)
    {
        $this->_wdb->insert($this->_table, $order);
    }
    
    public function completeOrder($order_id)
    {
        $info['completed'] = 1;
        $info['completed_time'] = time();
        $where = $this->_wdb->quoteinto('order_id = ?', $order_id);
        $this->_wdb->update($this->_table, $info, $where);       
    }
}