<?php

/**
 * Bll BatchWork
 * DB Auto Statistic Batch Work Logic Layer
 *
 * @package    Bll/Island
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/04/28    Liz
 */
class Bll_Island_BatchWork extends Bll_Abstract
{
    /**
     * do batch compute
     *
     * @param integer $runingDate
     * @return boolean
     */
    public function doComputeByDay($runingDate, $terraceType = 'renren')
    {
        info_log('doComputeOneDay start - ' . $runingDate, "batch_compute");
        info_log('/**************doComputeOneDay start - runingDate:' . $runingDate, "batch_compute_day");
        info_log('nowTime' . time(), "batch_compute_day");

        $todayUnixTime = strtotime(date('Y-m-d', $runingDate));
        $startTime = $todayUnixTime - 86400;
        $endTime = $todayUnixTime;
        $createTime = $endTime - 3600;
        
        info_log('$todayUnixTime' . $todayUnixTime, "batch_compute_day");
        info_log('$startTime' . $startTime, "batch_compute_day");
        info_log('$endTime' . $endTime, "batch_compute_day");
        info_log('$createTime' . $createTime, "batch_compute_day");

        $dalCompute = Dal_Island_Compute::getDefaultInstance();
        $bllCompute = new Bll_Island_Compute();
        
        //get level count list
        $levelCountList = $bllCompute->getLevelCountList();
        //get pay user level count list
        $payLevelCountList = $dalCompute->getPayLevelCountList($startTime);
        
        //add user count
        $addUserCount = $dalCompute->getAddUserCountByTime($startTime, $endTime);
        //active count
        $activeCount = $dalCompute->getActiveCountByTime($startTime);
        //no login list
        $noLoginList = $bllCompute->getNoLoginList($startTime);
        
        //gold count
        $goldCount = $dalCompute->getGoldCountByTime($startTime, $endTime);
        //pay count
        $payCount = $dalCompute->getPayCountByTime($startTime, $endTime);
        
        //gold count
        $goldCountWebMoney = $dalCompute->getGoldCountByTimeWebMoney($startTime, $endTime);
        //pay count
        $payCountWebMoney = $dalCompute->getPayCountByTimeWebMoney($startTime, $endTime);
        
        //pay type list
        $payTypeList = $dalCompute->getPayCountByAmount($startTime, $endTime);
        //get pay list
        $payList = $dalCompute->getGoldSumByTime(1, 70);
        
        //all user count
        $allUserCount = $dalCompute->getAllUserCount();
        //active percent
        $activePercent = sprintf("%01.2f",($activeCount/$allUserCount)*100);

        $result = array('levelCountList' => $levelCountList,
                        'payLevelCountList' => $payLevelCountList,
                        'payList' => $payList,
                        'addUserCount' => $addUserCount,
                        'activePercent' => $activePercent,
                        'activeCount' => $activeCount,
                        'goldCount' => $goldCount,
                        'payCount' => $payCount,
                        'goldCountWebMoney' => $goldCountWebMoney,
                        'payCountWebMoney' => $payCountWebMoney,
                        'payTypeList' => $payTypeList,
                        'allUserCount' => $allUserCount,
                        'noLoginList' => $noLoginList,
                        'createTime' => $createTime,
                        'computeType' => 'day',
                        'terraceType' => $terraceType);
        
        try {
            $bllCompute->postData($result);
        }
        catch (Exception $e) {
            info_log("doComputeOneDay error happend!", "batch_compute");
            info_log($e->getMessage(), "batch_compute");
        }
        info_log('end-1' . time(), "batch_compute_day");
        info_log('doComputeOneDay ************ end ************** ', "batch_compute");
    }

    /**
     * do batch compute
     *
     * @param integer $runingDate
     * @return boolean
     */
    public function doComputeByHour($runingDate, $terraceType = 'renren')
    {
        info_log('doComputeByHour start - ' . $runingDate, "batch_compute");

        
        $nowUnixTime = strtotime(date('Y-m-d H:00:00'));
        $startTime = $nowUnixTime - 3600;
        $endTime = $nowUnixTime;
        $createTime = $endTime - 600;
        $todayUnixTime = strtotime(date('Y-m-d'));
        
        /*$startTime = $runingDate - 3600;
        $endTime = $runingDate;
        $createTime = $runingDate - 600;*/

        $dalCompute = Dal_Island_Compute::getDefaultInstance();
        $bllCompute = new Bll_Island_Compute();
                
        //active count
        $activeCount = $dalCompute->getActiveCountByTime($startTime);
        //today all active count
        $todayAllActiveCount = $dalCompute->getActiveCountByTime($todayUnixTime);
        //all user count
        $allUserCount = $dalCompute->getAllUserCount();
        //add user count
        $addUserCount = $dalCompute->getAddUserCountByTime($startTime, $endTime);
        
        //gold count
        $goldCount = $dalCompute->getGoldCountByTime($startTime, $endTime);
        //all gold count
        $allGoldCount = $dalCompute->getGoldCountAll();
        
        $result = array('activeCount' => $activeCount,
                        'todayAllActiveCount' => $todayAllActiveCount,
                        'allUserCount' => $allUserCount,
                        'addUserCount' => $addUserCount,
                        'goldCount' => $goldCount,
                        'allGoldCount' => $allGoldCount,
                        'createTime' => $createTime,
                        'computeType' => 'hour',
                        'terraceType' => $terraceType);

        try {
            $bllCompute->postData($result);
        }
        catch (Exception $e) {
            info_log("doComputeByHour error happend!", "batch_compute");
            info_log($e->getMessage(), "batch_compute");
        }

        info_log('doComputeByHour ************ end ************** ', "batch_compute");
    }
    
}