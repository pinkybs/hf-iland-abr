<?php

require_once 'Dal/Abstract.php';

class Dal_Island_RewardPlus extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $_table = 'island_rewardplus';
    
    protected static $_instance;
    
    /**
     * getDefaultInstance
     *
     * @return Dal_Island_RewardPlus
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
    
    public function update($transactionId, $info)
    {
        $where = $this->_wdb->quoteinto('transaction_id=?', $transactionId);
        $this->_wdb->update($this->_table, $info, $where);
    }
    
	public function getById($transactionId)
    {
        $sql = "SELECT * FROM $this->_table WHERE transaction_id=:transaction_id ";
        return $this->_rdb->fetchRow($sql, array('transaction_id' => $transactionId));
    }
    
}