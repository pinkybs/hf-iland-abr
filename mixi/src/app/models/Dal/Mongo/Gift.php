<?php

class Dal_Mongo_Gift extends Dal_Mongo_Abstract
{
    protected static $_instance;

    /**
     * single instance of Dal_Mongo_Gift
     *
     * @return Dal_Mongo_Gift
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function insert($gift)
    {
        return $this->_mg->mixi_island->gift_send->insert($gift);
    }

    public function getSendGift($key)
    {
		return $this->_mg->mixi_island->gift_send->findOne(array('sig' => $key));
    }

    public function deleteSendGift($key)
    {
        return $this->_mg->mixi_island->gift_send->update(array('sig' => $key), array('$set' => array('status'=>0)));
    }

    public function batchInsert($gifts)
    {
        return $this->_mg->mixi_island->gift_send->batchInsert($gifts);
    }

    public function getTodayTime()
    {
        $date = getdate();

        return mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
    }

    public function getGiftStatus($uid)
    {
        $result = $this->_mg->mixi_island->giftstatus->findOne(array('uid' => $uid));
        $default_count = 5;
        if ($result) {
            $todaytime = $this->getTodayTime();
            $count = $result['count'];
            $maxCount = 5;
            if ($result['time'] < $todaytime && $count < $maxCount) {
                $days = floor($todaytime - $result['time']/86400);
                $count = min($count + $default_count * $days, $maxCount);
                $this->updateGiftStatus($uid, $count);
            }
            return $count;
        } else {
            $this->updateGiftStatus($uid, $default_count);
            return $default_count;
        }
    }

    public function updateGiftStatus($uid, $count)
    {
        return $this->_mg->mixi_island->giftstatus->update(array('uid' => $uid), array('$set' => array('count' => $count, 'time' => time())), array('upsert' => true));
    }

}