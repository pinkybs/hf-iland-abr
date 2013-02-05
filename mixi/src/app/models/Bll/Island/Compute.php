<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/01    Liz
 */
class Bll_Island_Compute extends Bll_Abstract
{
    /**
     * get level user count
     *
     * @return array
     */
    public function getLevelCount($startTime, $endTime)
    {
    	$levelArray = array();
    	$dalCompute = Dal_Island_Compute::getDefaultInstance();

    	if ( !empty($startTime) ) {
            for ( $i = 1; $i < 36; $i ++ ) {
                $levelArray[$i] = $dalCompute->getLevelCountByTime($i, $startTime, $endTime);
            }
    	}
    	else {
	    	for ( $i = 1; $i < 36; $i ++ ) {
	    		$levelArray[$i] = $dalCompute->getLevelCount($i);
	    	}
    	}

    	return $levelArray;
    }

    public function getLevelCountList()
    {
    	$dalCompute = Dal_Island_Compute::getDefaultInstance();
    	$levelCountList = $dalCompute->getLevelCountList();

    	return $levelCountList;
    }

    /*public function getLevelCountByLevel($level = 1, $startTime, $endTime)
    {
        $dalCompute = Dal_Island_Compute::getDefaultInstance();
    	$levelCountList = $dalCompute->getLevelCountByTime($i, $startTime, $endTime);

    	return $levelCountList;
    }*/

    public function computeAll($startTime, $endTime)
    {
        $dalCompute = Dal_Island_Compute::getDefaultInstance();

        $allUserCount = $dalCompute->getAllUserCount();
        $addUserCount = $dalCompute->getAddUserCountByTime($startTime, $endTime);

        $todayTime = date('Y-m-d', $startTime);
        $todayTime = strtotime($todayTime);
        $activeCount = $dalCompute->getActiveCountByTime($todayTime);

        $payCount = $dalCompute->getPayCountByTime($startTime, $endTime);
        $goldCount = $dalCompute->getGoldCountByTime($startTime, $endTime);

        $result = array('allUserCount' => $allUserCount,
                        'addUserCount' => $addUserCount,
                        'activeCount' => $activeCount,
                        'payCount' => $payCount,
                        'goldCount' => $goldCount);

        return $result;
    }

    public function getPayList($pageIndex = 1, $pageSize = 50)
    {
        $dalCompute = Dal_Island_Compute::getDefaultInstance();
        return $dalCompute->getGoldSumByTime($pageIndex, $pageSize);
    }
    
    public function getNoLoginList($nowTime)
    {
    	$dalCompute = Dal_Island_Compute::getDefaultInstance();
    	
    	$dayTime_3 = $nowTime - 3*86400;
    	$dayTime_7 = $nowTime - 7*86400;
        $dayTime_15 = $nowTime - 15*86400;
        $dayTime_30 = $nowTime - 30*86400;
        $dayTime_60 = $nowTime - 60*86400;
        
        $noLoginCount_3 = $dalCompute->getNoLoginCount($dayTime_7, $dayTime_3);
        $noLoginCount_7 = $dalCompute->getNoLoginCount($dayTime_15, $dayTime_7);
        $noLoginCount_15 = $dalCompute->getNoLoginCount($dayTime_30, $dayTime_15);
        $noLoginCount_30 = $dalCompute->getNoLoginCount($dayTime_60, $dayTime_30);
        $noLoginCount_60 = $dalCompute->getNoLoginCount(1, $dayTime_60);
        
        $result = array('noLoginCount_3' => $noLoginCount_3,
                        'noLoginCount_7' => $noLoginCount_7,
                        'noLoginCount_15' => $noLoginCount_15,
                        'noLoginCount_30' => $noLoginCount_30,
                        'noLoginCount_60' => $noLoginCount_60);
        return $result;
    }
    
    public function postData($params)
    {
        //$url = "http://island.liz.cn/compute/setcompute/p/496700";
        $url = "http://tt1.hapyfish.com/compute/setcompute/p/496700";
        
        //$vars = "uid=35";
        $params  = array('params' => Zend_Json::encode($params));
        
        /*$post_params = array();
        foreach ($params as $key => &$val) {
            $post_params[] = $key.'='.urlencode($val);
        }
        $vars = implode('&', $post_params);*/
        
        $ch = curl_init();
        //设置要采集的URL
        curl_setopt($ch, CURLOPT_URL,$url);    
        //设置形式为POST
        curl_setopt($ch, CURLOPT_POST, 1);
        //设置Post参数
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        //用字符串打印出来
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
        
        $data = curl_exec($ch);
        //$data = curl_exec($ch);
        curl_close($ch);
        
        if ($data) {
            return $data;
        }
        else{
           //echo 'Post null';
           return false;
        }
    }
}