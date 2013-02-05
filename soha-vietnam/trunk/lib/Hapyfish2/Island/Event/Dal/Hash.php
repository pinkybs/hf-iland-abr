<?php
/**
 * lei.wu,
 * lei.wu@hapyfish.com
 * */
class Hapyfish2_Island_Event_Dal_Hash
{
	protected static $_instance;
	protected $tbname = 'island_hash_collect';

	public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getval( $key )
    {
    	$sql = "SELECT * FROM $this->tbname WHERE `key`=:key";

		$db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];

    	return $rdb->fetchRow($sql, array('key' => $key));
    }

    public function insert( $key, $val )
    {
    	$info = array('key'=> $key, 'val' => $val);

        $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $wdb = $db['w'];

    	return $wdb->insert($this->tbname, $info);
    }

    public function update( $key, $val )
    {
    	$info = array('val' => $val);

    	$db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $wdb = $db['w'];

    	$where = $wdb->quoteInto(" `key`=? ", $key);
    	return $wdb->update($this->tbname, $info, $where);
    }

    public function delete( $key )
    {
    	$db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $wdb = $db['w'];

    	$where = $wdb->quoteInto(" `key`=? ", $key);

    	return $wdb->delete($this->tbname, $where);
    }
    public function getallhaveget()
    {
		$sql = "select `key` from {$this->tbname} WHERE `val`=1";

		$db = Hapyfish2_Db_Factory::getEventDB('db_0');
		$wdb = $db['w'];

       return $wdb->fetchCol( $sql );
    }

}