<?php

require_once 'Dal/User.php';
require_once 'Bll/Cache/User.php';

class Bll_User
{
    public static function isAppUser($uid)
    {
        return Bll_Cache_User::isAppUser($uid);
    }
    
    public static function isFibbden($uid)
    {
        return Bll_Cache_User::isFibbden($uid);
    }
    
    public static function changeStatus($uid, $status)
    {
        try {
            $dalUser = Dal_Island_User::getDefaultInstance();
            $dalUser->updateUserStatus($uid, $status);
            Bll_Cache_User::clearFibbden($uid);
        }catch(Exception $e) {
            
        }
    }
    
    public static function getPerson($uid)
    {
        return Bll_Cache_User::getPerson($uid);
    }
        
    public static function getPeople($ids)
    {
        $items = array();
        foreach ($ids as $id) {
            $items[$id] = self::getPerson($id);
        }
        
        return $items;
    }
    
    protected static function getDalInstance()
    {
        return Dal_Mongo_User::getDefaultInstance();
    }
    
    public static function different($old, $new)
    {
        $diff = array();
        foreach ($old as $k => $v) {
            if (isset($new[$k]) && $new[$k] != $v) {
                $diff[$k] = $new[$k];
            }
        }
        
        return $diff;
    }

    public static function updatePerson($person)
    {
        if ($person == null) {
			return;
		}
		
        $uid = $person['uid'];
        
        $oldPerson = self::getPerson($uid);
        
        if ($oldPerson == null) {
            $dalUser = self::getDalInstance();
            try {
                $dalUser->addPerson($person);
            }
            catch (Exception $e) {
                err_log($e->getMessage());
            }
            
        } else {
            $diff = self::different($oldPerson, $person);
            if (!empty($diff)) {
                $dalUser = self::getDalInstance();
                try {
                    $dalUser->updatePerson($uid, $diff);
                    Bll_Cache_User::cleanPerson($uid);
                }
                catch (Exception $e) {
                    err_log($e->getMessage());
                }                
            }
        }
    }
    
    public static function updatePeople($people)
    {
        foreach ($people as $person) {
            self::updatePerson($person);
        }
    }

    public static function appendPersonData(&$data, $person = null)
    {
        if ($person == null) {
            $data['name'] = '';
            $data['face'] = '';
            $data['smallFace'] = '';
        }
        else {
            $data['name'] = $person['name'];
            $data['face'] = $person['headurl'];
            $data['smallFace'] = $person['tinyurl'];
        }
    }
    
    public static function appendPerson(&$data, $idKey = 'uid')
    {
        $person = self::getPerson($data[$idKey]);

        self::appendPersonData($data, $person);
    }
    
    public static function appendPeople(&$datas, $idKey = 'uid')
    {
        if (empty($datas)) {
            return;
        }
        
        foreach ($datas as &$data) {
            $person = self::getPerson($data[$idKey]);
            self::appendPersonData($data, $person);
        }
    }

    public static function search($people, $uid)
    {
        foreach($people as $person) {
            if ($person['uid'] == $uid) {
                return $person;
            }
        }

        return null;
    }
}