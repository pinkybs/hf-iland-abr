<?php


class Hapyfish2_Island_Event_Dal_InviteSend
{
    protected static $_instance;

	protected function _getDB()
    {
    	$key = 'db_0';
    	return Hapyfish2_Db_Factory::getEventDB($key);
    }

    protected function _getTableName($puid)
    {
    	$id = $puid % 10;
    	return 'invite_send_' . $id;
    }

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Server
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

	public function lstInviteSend($puid)
    {
    	$db = $this->_getDB();
    	$tbname = $this->_getTableName($puid);
    	$sql = "SELECT uid,create_time FROM $tbname WHERE invite_puid=:invite_puid AND complete=0 ";

        $rdb = $db['r'];
        return $rdb->fetchAll($sql, array('invite_puid' => $puid));
    }

    public function getInviteSend($puid, $uid)
    {
    	$db = $this->_getDB();
    	$tbname = $this->_getTableName($puid);
    	$sql = "SELECT invite_puid,uid,create_time,complete FROM $tbname WHERE invite_puid=:invite_puid AND uid=:uid ";

        $rdb = $db['r'];
        return $rdb->fetchRow($sql, array('invite_puid'=>$puid, 'uid'=>$uid));
    }

	public function insert($puid, $info)
    {
        $db = $this->_getDB();
    	$tbname = $this->_getTableName($puid);
        $wdb = $db['w'];
        return $wdb->insert($tbname, $info);
    }

	public function update($puid)
    {
        $db = $this->_getDB();
    	$tbname = $this->_getTableName($puid);
    	$sql = "UPDATE $tbname SET complete=1 WHERE invite_puid=:invite_puid";

        $wdb = $db['w'];
        return $wdb->query($sql, array('invite_puid' => $puid));
    }

	public function delete($puid)
    {
        $db = $this->_getDB();
    	$tbname = $this->_getTableName($puid);
        $sql = "DELETE FROM $tbname WHERE invite_puid=:invite_puid";

        $wdb = $db['w'];
        return $wdb->query($sql, array('invite_puid' => $puid));
    }

}