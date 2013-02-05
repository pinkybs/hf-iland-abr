<?php

class Dal_Mongo_Feed extends Dal_Mongo_Abstract
{        
    protected static $_instance;
        
    /**
     * single instance of Dal_Mongo_Feed
     *
     * @return Dal_Mongo_Feed
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert minifeed
     *
     * @param array $info
     * @return boolean
     */
    public function insertMinifeed($feed)
    {
        return $this->_mg->mixi_island->minifeed->insert($feed);
    }
    
    public function batchInsertMinifeed($feeds)
    {
        return $this->_mg->mixi_island->minifeed->batchInsert($feeds);
    }

    /**
     * insert minifeed
     *
     * @param array $info
     * @return boolean
     */
    public function insertIslandMinifeed($feed)
    {
        $timeType = $this->getTimeType($feed['create_time']);
        $feed['time_type'] = $feed['actor'] . $feed['target'] . $timeType;
        
        $hasFeed = $this->_mg->mixi_island->minifeed->findOne(array('time_type' => $feed['time_type']));
        if ( $hasFeed ) {
            if ( isset($feed['title']['money']) ) {
                $field = 'title.money';
                $change = $feed['title']['money'];
            }
            else if ( isset($feed['title']['visitor_num']) ) {
                $field = 'title.visitor_num';
                $change = $feed['title']['visitor_num'];
            }
            return $this->_mg->mixi_island->minifeed->update(array('time_type' => $feed['time_type']), array('$inc' => array($field => (int)$change)), array('upsert' => true));
        }
        else {
            $feed['title']['money'] = isset($feed['title']['money']) ? $feed['title']['money'] : 0;
            $feed['title']['visitor_num'] = isset($feed['title']['visitor_num']) ? $feed['title']['visitor_num'] : 0;
            
            return $this->_mg->mixi_island->minifeed->insert($feed);
        }
    }
    
    /**
     * insert plant manage minifeed
     *
     * @param array $info
     * @return boolean
     */
    public function insertPlantManageMinifeed($feed)
    {
        $timeType = $this->getTimeType($feed['create_time']);
        $feed['time_type'] = '2:' . $feed['actor'] . $feed['target'] . $timeType;
        
        $hasFeed = $this->_mg->mixi_island->minifeed->findOne(array('time_type' => $feed['time_type']));
        if ( $hasFeed ) {
            return $this->_mg->mixi_island->minifeed->update(array('time_type' => $feed['time_type']), array('$inc' => array('title.manage_num' => (int)$feed['title']['manage_num'])), array('upsert' => true));
        }
        else {
            return $this->_mg->mixi_island->minifeed->insert($feed);
        }
    }
    
    public function updateFeedStatus($uid, $clear = false, $count = 1)
    {
        if (!$clear) {
            return $this->_mg->mixi_island->feedstatus->update(array('uid' => (string)$uid), array('$inc' => array('count' => (int)$count)), array('upsert' => true));
        } else {
            return $this->_mg->mixi_island->feedstatus->update(array('uid' => (string)$uid), array('$set' => array('count' => 0)), array('upsert' => true));
        }
    }
    
    public function getNewMiniFeedCount($uid)
    {
        $result = $this->_mg->mixi_island->feedstatus->findOne(array('uid' => $uid));
        if ($result) {
            return $result['count'];
        }
        
        return 0;
    }
    
    
    /**
     * get minifeed
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getMinifeed($uid, $pageIndex=1, $pageSize=10)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $cursor = $this->_mg->mixi_island->minifeed
                    ->find(array('uid' => $uid))
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
    
    public function insertTest()
    {
        return $this->_mg->mixi_island->test->update(array('id' => 1), array('$set' => array('data' => 'cccc')), array('upsert' => true));
    }

    /**
     * delete user feed info
     *
     * @param integer $uid
     * @return void
     */
    public function deleteFeed($uid)
    {        
        $this->_mg->mixi_island->minifeed->remove(array('uid' => $uid));
    }
    
    public static function getTimeType($time)
    {
        $hour = date('H', $time);
        switch ($hour) {
            case $hour >= 0 && $hour < 2 :
                $date = '00';
                break;
            case $hour >= 2 && $hour < 4 :
                $date = '01';
                break;
            case $hour >= 4 && $hour < 6 :
                $date = '02';
                break;
            case $hour >= 6 && $hour < 8 :
                $date = '03';
                break;
            case $hour >= 8 && $hour < 10 :
                $date = '04';
                break;
            case $hour >= 10 && $hour < 12 :
                $date = '05';
                break;
            case $hour >= 12 && $hour < 14 :
                $date = '06';
                break;
            case $hour >= 14 && $hour < 16 :
                $date = '07';
                break;
            case $hour >= 16 && $hour < 18 :
                $date = '08';
                break;
            case $hour >= 18 && $hour < 20 :
                $date = '09';
                break;
            case $hour >= 20 && $hour < 22 :
                $date = '10';
                break;
            case $hour >= 22 && $hour < 24 :
                $date = '11';
                break;
        }
        
        return date('Ymd', $time).$date;
    }
}