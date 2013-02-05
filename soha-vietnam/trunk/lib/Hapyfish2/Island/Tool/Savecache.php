<?php

class Hapyfish2_Island_Tool_Savecache
{
    public static function saveAllUserCacheByDB($dbId, $tableId)
    {
        info_log('/**************start*************/'.date('Y-m-d H:i:s'), 'saveUser');

        $nowTime = time();
        $clearTime = $nowTime - 20*24*60*60;
        $start = 0;
        $end = 2;
        
        $userCount = 0;
        try {
	            for ( $i=$start; $i < $end; $i++ ) {
	                info_log('DB:'.$dbId.'-table:'.$tableId.'-p:'.$i.'-t:'.date('Y-m-d H:i:s'), 'saveUser');
	                
	                $uidList = self::getUidListVoDada($i+1, $dbId, $tableId);
	                
	                $count = 0;
	                for ( $j=0,$jCount=count($uidList); $j<$jCount; $j++ ) {
	                    $uid = $uidList[$j]['uid'];
	                    $cache = Hapyfish2_Cache_Factory::getMC($uid);
	                    
                        $loginInfo = Hapyfish2_Island_HFC_User::getUserLoginInfo($uid);
                        $lastLoginTime = $loginInfo['last_login_time'];
	                    if ( $lastLoginTime >= $clearTime ) {
	                    	Hapyfish2_Island_Tool_SaveOldUserCache::saveOne($uid);
	                    	
	                	    //Hapyfish2_Island_Tool_SaveOldUserCache::saveOne(1036);
	                	    //if ( $uid == 1036 ) {
                            //    info_log('/************************************/'.'1036', 'saveUser');
	                	    //}
	                        $userCount = $userCount + 1;
	                        $count = $count + 1;
	                    }
	                }
                    info_log('$userCount-:'.$userCount.'-nowcount:'.$count, 'saveUser');
	            }
        
            info_log('alluserCount:'.$userCount, 'saveUser');
        }
        catch (Exception $e) {
            info_log('error:'.$e->getMessage(), 'saveUser');
        }
        
        info_log('/**************end*************/'.date('Y-m-d H:i:s'), 'saveUser');
    }
    
    public static function getUidListVoDada($pageIndex, $dbId, $tableId)
    {
        $uidListVo = self::restore($pageIndex, $dbId, $tableId);
        return Zend_Json::decode($uidListVo);
    }
    
    public static function restore($pageIndex, $dbId, $tableId)
    {
        $file = '/data/temp/uid/'.$dbId.'/'.$tableId.'/uid.' . $pageIndex . '.cache';
        if (is_file($file)) {
            return file_get_contents($file);
        } else {
            return self::dump($pageIndex, $dbId, $tableId);
        }
    }
    
    public static function dump($pageIndex, $dbId, $tableId)
    {
        $resultUidList = self::getUidListVo($pageIndex, $dbId, $tableId);
        $file = '/data/temp/uid/'.$dbId.'/'.$tableId.'/uid.' . $pageIndex . '.cache';
        $data = json_encode($resultUidList);
        file_put_contents($file, $data);
        return $data;
    }
    
    public static function getUidListVo($pageIndex, $dbId, $tableId)
    {
        $resultUidList = array();
        
        $dalClearcache = Hapyfish2_Island_Dal_Clearcache::getDefaultInstance();
        $resultUidList = $dalClearcache->getUidListByPage($pageIndex, 1000, $dbId, $tableId);
        return $resultUidList;
    }
    
	
}