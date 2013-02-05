<?php

/**
 * milk Cache
 *
 * @package    Bll/Cache/Island
 * @create      2010/07/20    Hwq
*/
class Hapyfish_Island_Cache_Notice
{
    /**
     * get milk bottle type
     * 
     * @return array
     */
    public static function getNoticeList()
    {
    	$key = 'getNoticeList';
        $cache = Hapyfish_Cache_Memcached::getInstance();
        $info = $cache->get($key);
        if ($info === false) {
            $db = Hapyfish_Island_Dal_Notice::getDefaultInstance();
            $aryList = $db->getNoticeList();
            $aryTmp1 = array();
            $aryTmp2 = array();
            $aryTmp3 = array();
            foreach ($aryList as $value){
            	if($value['position'] == 1){
            	   $aryTmp1[] = $value;
            	} else if($value['position'] == 2){
            	   $aryTmp2[] = $value;
            	} else if($value['position'] == 3){
                   $aryTmp3[] = $value;
                }
            }
            $info = array('main'=>$aryTmp1,'sub'=>$aryTmp2,'pic'=>$aryTmp3);
            $cache->add($key, $info, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
        }

        return $info;
    }
    
    /**
     * clear notice list
     * 
     * @return null
     */
    public static function clearNoticeList()
    {
        $key = 'getNoticeList';
        $cache = Hapyfish_Cache_Memcached::getInstance();
        $cache->delete($key);
    }
}