<?php

require_once 'Dal/Abstract.php';

class Dal_Island_DailyInvite extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $_table = 'island_daily_invite';
    
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
    
    public function update($uid, $inviteUid, $info)
    {
        $where = array($this->_wdb->quoteinto('uid=?', $uid),
        			   $this->_wdb->quoteinto('invite_uid=?', $inviteUid));
        $this->_wdb->update($this->_table, $info, $where);
    }
    
	public function getByKey($uid, $inviteUid)
    {
        $sql = "SELECT * FROM $this->_table WHERE uid=:uid AND invite_uid=:invite_uid ";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'invite_uid' => $inviteUid));
    }
    
}