<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Liz
 */
class Dal_Island_Mooch extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_mooch = 'island_mooch';

    /**
     * table name
     *
     * @var string
     */
    protected $table_plant_mooch = 'island_mooch_plant';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert user mooch info
     *
     * @param array $info
     * @return integer
     */
    public function insertMooch($info)
    {
        $this->_wdb->insert($this->table_mooch, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * get mooch info
     *
     * @param integer $uid
     * @param integer $fid
     * @param integer $positionId
     * @return array
     */
    public function getMooch($uid, $fid, $positionId)
    {
        $sql = "SELECT * FROM $this->table_mooch WHERE uid=:uid AND fid=:fid AND position_id=:positionId ";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'fid'=>$fid, 'positionId'=>$positionId));
    }

    /**
     * delete boat mooch info
     *
     * @param integer $fid
     * @param integer $positionId
     * @return void
     */
    public function deleteMooch($fid, $positionId)
    {
		$sql = "DELETE FROM $this->table_mooch WHERE fid = :fid AND position_id = :positionId ";
        return $this->_wdb->query($sql, array('fid' => $fid, 'positionId' => $positionId));
    }

    /**
     * insert user plant mooch info
     *
     * @param array $info
     * @return integer
     */
    public function insertPlantMooch($info)
    {
        $this->_wdb->insert($this->table_plant_mooch, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * get plant mooch info
     *
     * @param integer $uid
     * @param integer $fid
     * @param integer $id
     * @return array
     */
    public function getPlantMooch($uid, $fid, $id)
    {
        $sql = "SELECT * FROM $this->table_plant_mooch WHERE uid=:uid AND fid=:fid AND id=:id ";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'fid'=>$fid, 'id'=>$id));
    }

    /**
     * delete plant mooch info
     *
     * @param integer $fid
     * @param integer $id
     * @return void
     */
    public function deletePlantMooch($fid, $id)
    {
        $sql = "DELETE FROM $this->table_plant_mooch WHERE fid=:fid AND id=:id ";
        return $this->_wdb->query($sql,array('fid' => $fid, 'id' => $id));
    }

}