<?php

class Hapyfish2_Island_Dal_Permissiongift
{
	protected static $_instance;
	
	private $tbname = 'island_user_permissiongift';
	
	/**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Permissiongift
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function gettf( $uid ) 
    {
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        
        $sql = "SELECT uid, tf FROM `{$this->tbname}` WHERE uid=:uid";
        
        return $rdb->fetchRow( $sql, array('uid'=>$uid) );
    }
    
    public function insert( $uid, $tf ) 
    {
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

        $info = array('uid'=> $uid, 'tf'=>$tf, 'create_at'=>time());        
    	return $wdb->insert( $this->tbname, $info );	
    }
    
    public function update( $uid, $tf ) 
    {
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
        $info = array('tf'=>$tf);
    	$where = $wdb->quoteInto(" `uid`=? ", $uid);
    	return $wdb->update($this->tbname, $info, $where);
    }
    
    public function delete( $uid) 
    {
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
        $where = $wdb->quoteInto(" `uid`=? ", $uid);
    	return $wdb->delete($this->tbname, $where);
    }
    
    public function deleteHas($uid)
    {
    	$sql = "DELETE FROM $this->tbname WHERE uid=:uid";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
        $wdb->query($sql, array('uid' => $uid));
    }
    
}
