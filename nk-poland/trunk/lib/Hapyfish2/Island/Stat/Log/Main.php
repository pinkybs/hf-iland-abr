<?php

class Hapyfish2_Island_Stat_Log_Main
{
	public static function getActiveTwoDay($day, $file)
	{
        info_log('/*****activeTwoday:start-'.$day.'******/'.time(), 'statActiveTwoDay');
        $content = file_get_contents($file);
        if (empty($content)) {
            info_log('no data', 'stat.log.activetwoday.err');
            return;
        }
        $temp = explode("\n", $content);
        
        $uidArray = array();
        $uidSecondArray = array();
        $allActiveTwoDayCount = 0;
        $allActiveSecondDayCount = 0;
        
        foreach ($temp as $line) {
            if (empty($line)) {
                continue;
            }
            $r = explode("\t", $line);
            $uid= $r[2];
            $activeCount = $r[6];
            $activeSecondCount = $r[7];
            
            if ( !isset($uidArray[$uid]) ) {
            	$uidArray[$uid] = 1;
            	if ( $activeCount > 1 ) {
            		$allActiveTwoDayCount++;
            	}
            }
        
            if ( !isset($uidSecondArray[$uid]) ) {
                $uidSecondArray[$uid] = 1;
                if ( $activeSecondCount > 1 ) {
                    $allActiveSecondDayCount++;
                }
            }
        }
        
        $data = array('active_twoday' => $allActiveTwoDayCount, 'active_secondday' => $allActiveSecondDayCount);
        
        $dal = Hapyfish2_Island_Stat_Dal_Main::getDefaultInstance();
        $dal->update($day, $data);
        
        info_log('/*****activeTwoday:end******/'.$allActiveTwoDayCount . ':' . $allActiveSecondDayCount, 'statActiveTwoDay');
        return $data;
	}
	
    //统计每天活跃用户（连续登录2天的玩家，计算为活跃）
    public static function getActiveTwoDayFromDb($day)
    {
        info_log('/*****activeTwoday:start-'.$day.'******/'.time(), 'statActiveTwoDay');
        $begin = strtotime($day);
        $end = $begin + 86400;
        $allActive = 0;
        
        try {
            $dal = Hapyfish2_Ipanda_Stat_Dal_Main::getDefaultInstance();
            for ($i = 0; $i < DATABASE_NODE_NUM; $i++) {
                for ($j = 0; $j < 10; $j++) {
                    //活跃用户数
                    $data = $dal->getActiveTwoDay($i, $j, $begin, $end);
                    if ( $data > 0 ) {
                        $allActive += $data;
                    }
                }
                info_log('activeTwoday:dbId:'.$i, 'statActiveTwoDay');
            }
        } catch (Exception $e) {

        }
        $data = array('active_twoday' => $allActive);
        $dal->update($day, $data);
        
        info_log('/*****activeTwoday:end******/'.$allActive, 'statActiveTwoDay');
        return $data;
    }
    
    public static function getUserLevelList($day)
    {
        info_log('/*****getUserLevelList:start-'.$day.'******/'.time(), 'statMain');
        
        $list = array();
        try {
            $dal = Hapyfish2_Ipanda_Stat_Dal_Main::getDefaultInstance();
            for ($i = 0; $i < DATABASE_NODE_NUM; $i++) {
                for ($j = 0; $j < 10; $j++) {
                    //用户等级分布列表
                    $data = $dal->getUserLevelList($i, $j);
                    foreach ( $data as $val ) {
                    	$level = $val['level'];
                    	$count = $val['count'];
                    	if ( isset($list[$level]) ) {
                    		$list[$level] += $count;
                    	}
                    	else {
                    		$list[$level] = $count;
                    	}
                    }
                }
                info_log('userLevelList:dbId:'.$i, 'statMain');
            }
        } catch (Exception $e) {
        }
        
        $newInfo = array('log_time' => $day, 'level' => json_encode($list));
        $dal->insertUserLevelList($newInfo);
        
        info_log('/*****getUserLevelList:end******/'.$allActive, 'statMain');
        return $data;
    }
    
    
}