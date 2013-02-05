<?php

class Dal_Mongo_Invite extends Dal_Mongo_Abstract
{
    protected static $_instance;

    /**
     * single instance of Dal_Mongo_Invite
     *
     * @return Dal_Mongo_Invite
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert invite info
     *
     * @param array $info
     * @return boolean
     */
    public function insert($info)
    {
        return $this->_mg->mixi_island->invite_log->insert($info);
    }

	public function getInvite($key)
    {
		return $this->_mg->mixi_island->invite_log->findOne(array('sig' => $key, 'status' => 1));
    }

    public function getInviteByUid($uid, $fid)
    {
        return $this->_mg->mixi_island->invite_log->findOne(array('actor' => $uid, 'target' => $fid));
    }
    
    public function deleteInvite($key)
    {
        return $this->_mg->mixi_island->invite_log->update(array('sig' => $key), array('$set' => array('status'=>0)));
    }

}