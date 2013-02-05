<?php


class Hapyfish_Island_Dal_Card extends Hapyfish_Island_Dal_Abstract
{
    protected $table_card = 'island_card';
    
    protected $table_user_card = 'island_user_card';

    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish_Island_Dal_Card
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

	/**
	 * read Card by uid
	 *
	 * @param integer $uid
	 * @return array
	 */
	public function getUserCards($uid)
	{
		$sql = "SELECT * FROM $this->table_user_card WHERE uid=:uid AND count > 0";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
	}
	
	/**
	 * get Card info by id
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getCardById($id)
	{
		$sql = "SELECT * FROM $this->table_card WHERE cid = :id";
		return $this->_rdb->fetchRow($sql, array('id' => $id));
	}
	
    /**
     * update user card info
     *
     * @param string $uid
     * @param integer $cid
     * @param integer $change
     * @param array $info
     * @return void
     */
	public function updateCardById($uid, $cid, $change)
	{
		$sql = "UPDATE $this->table_user_card SET count=count+:change WHERE uid=:uid AND cid=:cid";
        $this->_wdb->query($sql,array('uid'=>$uid, 'cid' => $cid, 'change'=>$change));
	}

}