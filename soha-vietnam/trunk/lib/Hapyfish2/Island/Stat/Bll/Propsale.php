<?php

class Hapyfish2_Island_Stat_Bll_Propsale
{

    public static function getData($start, $end, $cid)
	{
		$data = null;
		$total = null;
		try {
			$dal = Hapyfish2_Island_Stat_Dal_Propsale::getDefaultInstance();
			$data = $dal->getData($start, $end, $cid);
			$total = $dal->getTotal($start, $end, $cid);
		} catch (Exception $e) {

		}

		return array($data, $total);
	}
	
	public static function getOther($file)
	{
		$content = file_get_contents($file);
		if (empty($content)) {
			info_log('no data', 'stat.log.shop.background.err');
			return;
		}
		
		$temp = explode("\n", $content);
		
		$data = array();
		foreach($temp as $line) {
			if (empty($line)) {
				continue;
			}
			
			$r = explode("\t", $line);
			//$uid = $r[2];
			$cid = $r[4];
			$type = $r[5];
			$price = $r[6];
			if (isset($data[$cid]['num'])) {
				$data[$cid]['num'] += 1;
			}else {
				$data[$cid]['num'] = 1;
			}
			if($type == 1){
				if(isset($data[$cid]['coin']))
				{
					$data[$cid]['coin'] += $price;
				}else{
					$data[$cid]['coin'] = $price;
				}
			}else{
				if(isset($data[$cid]['gold']))
				{
					$data[$cid]['gold'] += $price;
				}else{
					$data[$cid]['gold'] = $price;
				}
			}
		}
		
		return $data;
	}
	
	public static function getCard($file)
	{
		$content = file_get_contents($file);
		if (empty($content)) {
			info_log('no data', 'stat.log.shop.background.err');
			return;
		}
		
		$temp = explode("\n", $content);
		
		$data = array();
		foreach($temp as $line) {
			if (empty($line)) {
				continue;
			}
			
			$r = explode("\t", $line);
			//$uid = $r[2];
			$cid = $r[3];
			$type = $r[5];
			$price = $r[6];
			$count = $r[4];
			if (isset($data[$cid]['num'])) {
				$data[$cid]['num'] += $count;
			}else {
				$data[$cid]['num'] = $count;
			}
			if($type == 1){
				if(isset($data[$cid]['coin']))
				{
					$data[$cid]['coin'] += $price;
				}else{
					$data[$cid]['coin'] = $price;
				}
			}else{
				if(isset($data[$cid]['gold']))
				{
					$data[$cid]['gold'] += $price;
				}else{
					$data[$cid]['gold'] = $price;
				}
			}
		}
		
		return $data;
	}

	public static function updateToDB($prefix)
	{
		$dir = '/home/admin/data/stat-data/';
		$dtYesterday = strtotime("-1 day");
		$dt = date('Ymd', $dtYesterday);
		$file = $dir.$prefix.'/'.$dt.'/all-'.$prefix.'-'.$dt.'.log';
		if($prefix == 204){
			$data = self::getCard($file);
		} else {
			$data = self::getOther($file);
		}
		if($data){
			$dal = Hapyfish2_Island_Stat_Dal_Propsale::getDefaultInstance();
			foreach($data as $k => $v){
				$info = array(
					'cid' => $k,
					'date'=> $dt,
					'num' => $v['num'],
					'gold' => $v['gold'],
					'coin' => $v['coin']
				);
				$dal -> insertDb($info);
			}
		}
	}
}