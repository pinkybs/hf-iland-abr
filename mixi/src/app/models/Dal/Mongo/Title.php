<?php

require_once 'Dal/Abstract.php';

class Dal_Mongo_Title extends Dal_Mongo_Abstract
{        
    protected static $_instance;
        
    /**
     * single instance of Dal_Mongo_Title
     *
     * @return Dal_Mongo_Title
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert user title info
     *
     * @param array $info
     * @return integer
     */
    public function insertUserTitle($info)
    {
        return $this->_mg->mixi_island->user_title->insert($info);
    }

    /**
     * get user using title 
     *
     * @param integer $uid
     * @return string
     */
    public function getUserTitleByStatus($uid)
    {
        $result = $this->_mg->mixi_island->user_title->find(array('uid' => $uid, 'status' => 1));
        
        if ($result) {
            return $result['title'];
        }
        
        return false;
    }
    
    /**
     * get user using title 
     *
     * @param integer $uid
     * @return string
     */
    public function getUserTitleList($uid)
    {
        $cursor = $this->_mg->mixi_island->user_title
                    ->find(array('uid' => $uid));
        
        $result = array();
        while($cursor->hasNext()) {
            $v = $cursor->getNext();
            unset($v['_id']);
            unset($v['uid']);
            unset($v['status']);
            $result[] = $v;
        }
        
        return $result;
    }
    
    /**
     * get user title by title id 
     *
     * @param integer $uid
     * @param integer $titleId
     * @return string
     */
    public function getUserTitleByTitle($uid, $titleId)
    {
        $result = $this->_mg->mixi_island->user_title->findOne(array('uid' => $uid, 'title' => $titleId));
        
        if ($result) {
            return $result;
        }
        
        return false;
    }

    /**
     * delete user title info
     *
     * @param integer $uid
     * @return void
     */
    public function deleteTitle($uid)
    {        
        $this->_mg->mixi_island->user_title->remove(array('uid' => $uid));
    }
}