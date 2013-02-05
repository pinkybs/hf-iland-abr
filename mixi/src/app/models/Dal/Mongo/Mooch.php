<?php

class Dal_Mongo_Mooch extends Dal_Mongo_Abstract
{        
    protected static $_instance;
        
    /**
     * single instance of Dal_Mongo_Mooch
     *
     * @return Dal_Mongo_Mooch
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self(getMongo(1));
        }

        return self::$_instance;
    }
    
    /**
     * insert user boat mooch info
     *
     * @param array $info
     * @return integer
     */
    public function insertMoochDock($info)
    {        
        $this->_mg->mixi_island->mooch_dock->update(array('pid' => $info['pid'],'owner_uid' => $info['owner_uid']), array('$push' => array('uids' => $info['uid'])), array('upsert' => true));
    }

    /**
     * get boat mooch info
     *
     * @param integer $uid
     * @param integer $ownerUid
     * @param integer $positionId
     * @return array
     */
    public function hadMoochDock($uid, $ownerUid, $positionId)
    {      
        $result = $this->_mg->mixi_island->mooch_dock
                    ->findOne(array('owner_uid' => (string)$ownerUid, 'pid' => (string)$positionId));
        
        if ($result) {
            $uids = $result['uids'];
            if ( in_array($uid, $uids) ) {
                return true;
            }
        }
        
        return false;
    }
   
    /**
     * delete boat mooch info
     *
     * @param integer $ownerUid
     * @param integer $positionId
     * @return void
     */
    public function deleteMoochDock($ownerUid, $positionId)
    {
        return $this->_mg->mixi_island->mooch_dock->remove(array('owner_uid' => (string)$ownerUid, 'pid' => (string)$positionId));
    }

    /**
     * insert user plant mooch info
     *
     * @param array $info
     * @return integer
     */
    public function insertPlantMooch($info)
    {        
        $this->_mg->mixi_island->mooch_plant->update(array('id' => (string)$info['id']), array('$push' => array('uids' => (string)$info['uid'])), array('upsert' => true));
    }

    /**
     * check user has mooch 
     *
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public function hasMoochPlant($uid, $id)
    {
        $result = $this->_mg->mixi_island->mooch_plant
                    ->findOne(array('id' => (string)$id));
        
        if ($result) {
            $uids = $result['uids'];
            if ( in_array($uid, $uids) ) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * get plant mooch info by uid
     *
     * @param integer $uid
     * @param integer $fid
     * @return array
     */
    public function getPlantMoochByIdList($idList)
    {
        $cursor = $this->_mg->mixi_island->mooch_plant
                    ->find(array('id' => array('$in' => $idList)));
                    
        $result = array();
        while($cursor->hasNext()) {
            $v = $cursor->getNext();
            unset($v['_id']);
            $result[$v['id']] = $v['uids'];
        }
        
        return $result;
    }
    
    /**
     * delete plant mooch info
     *
     * @param integer $id
     * @return void
     */
    public function deletePlantMooch($id)
    {
        return $this->_mg->mixi_island->mooch_plant->remove(array('id' => (string)$id));
    }

    /**
     * delete plant mooch info
     *
     * @param integer $uid
     * @return void
     */
    public function deletePlantMoochByUid($uid)
    {
        return $this->_mg->mixi_island->mooch_plant->remove(array('uid' => (string)$uid));
    }

    /**
     * delete dock mooch info
     *
     * @param integer $uid
     * @return void
     */
    public function deleteDockMoochByUid($uid)
    {
        return $this->_mg->mixi_island->mooch_dock->remove(array('uid' => (string)$uid));
    }
}