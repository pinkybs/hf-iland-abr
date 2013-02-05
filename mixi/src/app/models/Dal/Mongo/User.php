<?php

require_once 'Dal/Abstract.php';

class Dal_Mongo_User extends Dal_Mongo_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'mixi_user';
    
    protected static $_instance;
        
    /**
     * single instance of Dal_Mongo_User
     *
     * @return Dal_Mongo_User
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getTableName($uid)
    {
        $n = $uid % 10;
        return $this->table_user . '_' . $n;
    }

    public function getPerson($uid)
    {
        $tname = $this->getTableName($uid);
                
        $result = $this->_mg->mixi_island->$tname->findOne(array('uid' => (string)$uid));
        if ($result) {
            $user = array('uid' => $result['uid'],
                          'name' => $result['name'],
                          'sex' => $result['sex'],
                          'tinyurl' => $result['tinyurl'],
                          'headurl' => $result['headurl']);
            return $user;
        }
        
        return false;
    }

    public function addPerson($user)
    {
        $updated = time();

        $tname = $this->getTableName($user['uid']);
                
        $newPerson = array('name'=> $user['name'],
                           'sex'=> $user['sex'],
                           'tinyurl'=> $user['tinyurl'],
                           'headurl'=> $user['headurl'],
                           'birth'=> $user['birth'],
                           'updated'=> $updated);
        return $this->_mg->mixi_island->$tname->update(array('uid' => $user['uid']), array('$set' => $newPerson), array('upsert' => true));
    }
    
    public function updatePerson($uid, $info)
    {
        $info['updated'] = time();
        $tname = $this->getTableName($uid);
        
        $this->_mg->mixi_island->$tname->update(array('uid' => $uid), array('$set' => $info), array('upsert' => true));
    }

    public function deletePerson($uid)
    {
        $tname = $this->getTableName($uid);
        
        return $this->_mg->mixi_island->$tname->remove(array('uid' => $uid));
    }
    
    public function addPersonAry($array, $table)
    {
        $tname = 'mixi_user_'. $table;
        
        return $this->_mg->mixi_island->$tname->batchInsert($array);
    }

    public function addFriendAry($array, $table)
    {
        $tname = 'mixi_friend_'. $table;
        
        return $this->_mg->mixi_island->$tname->batchInsert($array);        
    }
}