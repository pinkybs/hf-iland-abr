<?php

require_once 'Dal/Abstract.php';

class Dal_User extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'taobao_user';
    
    protected static $_instance;
    
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
        
        $sql = "SELECT uid,name,sex,tinyurl,headurl FROM $tname WHERE uid=:uid";
        
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
        
    }
        
    public function addPerson($user)
    {
        $uid = $this->_wdb->quote($user['uid']);
        $name = $this->_wdb->quote($user['name']);
        $sex = $user['sex'] == 0 ? '0' : '1';
        $icon_40 = $this->_wdb->quote($user['icon_40']);
        $icon_120 = $this->_wdb->quote($user['icon_120']);
        $shop_id = isset($user['shop_id']) ? $user['shop_id'] : '0';
        $updated = time();

        $tname = $this->getTableName($user['uid']);
        
        $sql = "INSERT INTO $tname (uid, name, sex, tinyurl, headurl, shop_id, updated) VALUES"
              . '(' . $uid . ',' . $name . ',' . $sex . ',' . $icon_40 . ',' . $icon_120 . ','
              . $shop_id . ',' . $updated .')'
              . ' ON DUPLICATE KEY UPDATE '
              . 'name=' . $name
              . ',sex=' . $sex
              . ',tinyurl=' . $icon_40
              . ',headurl=' . $icon_120
              . ',shop_id=' . $shop_id
              . ',updated=' . $updated;
        
        return $this->_wdb->query($sql);
    }
    
    public function updatePerson($uid, $info)
    {
        $info['updated'] = time();
        $tname = $this->getTableName($uid);
        $where = $this->_wdb->quoteinto('uid = ?', $uid);
        $this->_wdb->update($tname, $info, $where);       
    }

    public function deletePerson($uid)
    {
        $tname = $this->getTableName($uid);
        
        $sql = "DELETE FROM $tname WHERE uid=:uid";
        
        return $this->_wdb->query($sql, array('uid' => $uid));
    }
}