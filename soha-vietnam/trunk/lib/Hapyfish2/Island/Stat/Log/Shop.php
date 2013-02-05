<?php

class Hapyfish2_Island_Stat_Log_Shop
{
	public static function handleBackground($file)
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
			
			if (isset($data[$cid])) {
				$data[$cid]['n'] += 1;
				$data[$cid]['p'] += $price;
			} else {
				$data[$cid] = array('i' => $cid, 'n' => 1, 't' => $type, 'p' => $price);
			}
		}
		
		return $data;
	}
	
	public static function handleBuilding($file)
	{
		$content = file_get_contents($file);
		if (empty($content)) {
			info_log('no data', 'stat.log.shop.building.err');
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
			
			if (isset($data[$cid])) {
				$data[$cid]['n'] += 1;
				$data[$cid]['p'] += $price;
			} else {
				$data[$cid] = array('i' => $cid, 'n' => 1, 't' => $type, 'p' => $price);
			}
		}
		
		return $data;
	}
	
	public static function handlePlant($day, $time0, $file)
	{
		$content = file_get_contents($file);
		if (empty($content)) {
			info_log('no data', 'stat.log.shop.plant.err');
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
			
			if (isset($data[$cid])) {
				$data[$cid]['n'] += 1;
				$data[$cid]['p'] += $price;
			} else {
				$data[$cid] = array('i' => $cid, 'n' => 1, 't' => $type, 'p' => $price);
			}
		}
		
		$info = array('log_time' => $time0,'data' => $data);
		$dalLogShop = Hapyfish2_Island_Stat_Dal_Shop::getDefaultInstance();
		$dalLogShop->insertPlant($info);
		
		return $data;
	}
	
	public static function handleCard($file)
	{
		$content = file_get_contents($file);
		if (empty($content)) {
			info_log('no data', 'stat.log.shop.card.err');
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
			$num = $r[4];
			$type = $r[5];
			$price = $r[6];
			
			if (isset($data[$cid])) {
				$data[$cid]['n'] += $num;
				$data[$cid]['p'] += $price;
			} else {
				$data[$cid] = array('i' => $cid, 'n' => $num, 't' => $type, 'p' => $price);
			}
		}
		
		return $data;
	}
	
	public static function handlePlantTemp($day, $time0, $file)
	{
		$content = file_get_contents($file);
		if (empty($content)) {
			info_log('no data', 'stat.log.shop.plant.err');
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
			
			if (isset($data[$cid])) {
				$data[$cid]['n'] += 1;
				$data[$cid]['p'] += $price;
			} else {
				$data[$cid] = array('i' => $cid, 'n' => 1, 't' => $type, 'p' => $price);
			}
		}
		
		$dalLogShop = Hapyfish2_Island_Stat_Dal_Shop::getDefaultInstance();
		foreach ( $data as $var ) {
			$info = array('log_time' => $time0, 
						  'cid' => $var['i'],
						  'num' => $var['n'],
						  'type' => $var['t'],
						  'price' => $var['p']);
			
			$dalLogShop->insertPlantTemp($info);
		}
		
		return $data;
	}
	
	public static function getPlant()
	{
		
		$dalLogShop = Hapyfish2_Island_Stat_Dal_Shop::getDefaultInstance();
		$plantList = $dalLogShop->getPlant();
		
		$allIds = array();
		
		foreach ( $plantList as $var ) {
			$plant = json_decode($var['data']);
			foreach ( $plant as $tmp ) {
				if ( !in_array($tmp['i'], $allIds) ) {
					$allIds[$tmp['i']]['n'] = $tmp['n'];
					$allIds[$tmp['i']]['i'] = $tmp['i'];
				}
				else {
					$allIds[$tmp['i']]['n'] = $allIds[$tmp['i']]['n'] + $tmp['n'];
				}
			}
		}
		
		foreach ( $allIds as $ids ) {
			$info = array($ids['i'], $ids['n']);
			$dalLogShop->insertPlantTemp($info);
		}
		
	}

}