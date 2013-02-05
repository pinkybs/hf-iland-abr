<?php
/**
 * bujisky.li
 * bujisky.li@hapyfish.com
 * */
class Hapyfish2_Island_Event_Bll_DreamGardenUserAward
{



	public static function receive($uid)
	{

		$data = self::check($uid);

		if( $data ) {
			// 已经领取完
			$result['state'] = '-1';
			$result['content'] = 'serverWord_201'; // #todo 错误代码未改
			return $result;
		}
		$send = new Hapyfish2_Island_Bll_Compensation();
		$send->setCoin(10000);
		$send->setGold(10);
		$send->setItem('26341', 30);
		$send->setItem('26441', 30);
		$send->setItem('67341', 10);
		$send->sendOne($uid, "恭喜您获得:");
		$coinChange = 10000;
        $goldChange = 10;

		$dreamgardenuser = Hapyfish2_Island_Event_Dal_DreamGardenUserAward::getDefaultInstance();
		$dreamgardenuser->insert($uid);

		$data['uid'] = $uid;
		$key = 'i:u:e:invf_dreamgardenuser:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $cache->set($key, $data);

        $result['feed'] = Hapyfish2_Island_Bll_Activity::send('Dream_Garden_User_Award', $uid);

        $result['status'] = 1;
        $result['goldChange'] = $goldChange;
        $result['coinChange'] = $coinChange;
 		$result['itemBoxChange'] = true;
 		$ret['state'] = '1';
 		$ret['result'] = $result;
		return $ret;

	}
	public function reset($uid)
	{
		$key = 'i:u:e:invf_dreamgardenuser:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);

        $data = $cache->get($key);
        $cache->set($key,"");
        $dreamgardenuser = Hapyfish2_Island_Event_Dal_DreamGardenUserAward::getDefaultInstance();
    	$dreamgardenuser->delete($uid);
    	return 1;
	}
	public static function check( $uid )
	{

		$key = 'i:u:e:invf_dreamgardenuser:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);

        $data = $cache->get($key);
 		$ntime = time();

 		$endtime = "2011-06-30 23:59:59";
 		if($ntime > strtotime($endtime))
 		{
 			return true;
 		}


		if ($data == false) {
			try {
    			$dreamgardenuser = Hapyfish2_Island_Event_Dal_DreamGardenUserAward::getDefaultInstance();
    			$data = $dreamgardenuser->get($uid);

    			$cache->set($key, $data);
				return $data;
			} catch (Exception $e) {
				return -1;
			}
		} else {
			return $data;
		}
	}

}