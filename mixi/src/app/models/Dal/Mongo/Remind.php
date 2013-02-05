<?php

class Dal_Mongo_Remind extends Dal_Mongo_Abstract
{        
    protected static $_instance;
        
    /**
     * single instance of Dal_Mongo_Remind
     *
     * @return Dal_Mongo_Remind
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert remind
     *
     * @param array $remind
     * @return boolean
     */
    public function insertRemind($remind)
    {
        return $this->_mg->mixi_island->remind->insert($remind);
    }
        
    public function updateRemindStatus($uid, $clear = false, $count = 1)
    {
        if (!$clear) {
            return $this->_mg->mixi_island->remindstatus->update(array('uid' => (string)$uid), array('$inc' => array('count' => (int)$count)), array('upsert' => true));
        } else {
            return $this->_mg->mixi_island->remindstatus->update(array('uid' => (string)$uid), array('$set' => array('count' => 0)), array('upsert' => true));
        }
    }
    
    public function getNewRemindCount($uid)
    {
        $result = $this->_mg->mixi_island->remindstatus->findOne(array('uid' => $uid));
        if ($result) {
            return $result['count'];
        }
        return 0;
    }
    
    /**
     * get remind
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getRemind($uid, $pageIndex=1, $pageSize=50)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $cursor = $this->_mg->mixi_island->remind
                    ->find(array('target' => $uid))
                    ->sort(array('create_time' => -1))
                    ->skip($start)
                    ->limit($pageSize);
        
        $result = array();
        while($cursor->hasNext()) {
            $v = $cursor->getNext();
            unset($v['_id']);
            $result[] = $v;
        }
        
        return $result;
    }

    /**
     * get remind by type
     *
     * @param integer $uid
     * @param integer $type
     * @return array
     */
    public function getRemindByType($uid, $type)
    {
        $cursor = $this->_mg->mixi_island->remind
                    ->find(array('target' => $uid, 'type' => $type))
                    ->sort(array('create_time' => -1))
                    ->skip(0)
                    ->limit(1);
        
        $result = array();
        while($cursor->hasNext()) {
            $v = $cursor->getNext();
            unset($v['_id']);
            $result = $v;
        }
        
        return $result;
    }
    
    /**
     * delete user remind info
     *
     * @param integer $uid
     * @return void
     */
    public function deleteRemind($uid)
    {        
        $this->_mg->mixi_island->remind->remove(array('uid' => $uid));
    }
}