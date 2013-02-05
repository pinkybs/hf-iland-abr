<?php
class Hapyfish2_Island_Bll_Casino
{
	public static function randcasino($num=1)
	{
		if ($num) {
			
			$list = Hapyfish2_Island_Cache_CasinoAwardType::getAllType();
			
			$temp = array();
			for ($i=0; $i<$num; $i++) {
				$rand = rand ($list['interval'][0]['a'], $list['interval'][19]['b']);
				foreach ($list['interval'] as $key => $val) {
					if ($val['a'] <= $rand && $rand < $val['b']) {
						$temp[] = $val['id'];
						break;
					}
				}
			}
			
			$indexs = array();
			foreach ($temp as $key => $val) {
				$indexs[] = $list['list'][$val]; 
			}
			
			return $indexs;
		}
		
		return false;
	}
	
	public static function raffle($uid, $betNum=1)
	{
		$result = array('status' => -1);
		$betNum = abs($betNum);
    	
		//小于6级不得赌博
		$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
		if ($userLevelInfo ['level'] < 6) {
			$result ['content'] = 'serverWord_165';
			return $result;
		}
		
		// betNum 参数不可没有
		if (empty($betNum)) {
        	$result['content'] = 'serverWord_110';
	        return array('result' => $result);
        }
		
        // 玩家抽奖卷够用不
		$userCards = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		$freeLotto = isset($userCards['55041']) ? $userCards['55041']['count'] : 0;
		$payLotto = isset($userCards['55141']) ? $userCards['55141']['count'] : 0;
		$lotto = $freeLotto + $payLotto;
		
		if ($lotto < $betNum) {
        	$result['content'] = 'serverWord_166';
	        return array('result' => $result);
        }
        
        // 给玩家发送物品
        $list = self::randcasino($betNum);
        $ok = self::itemstouser($list, $uid);
        
        // 扣除玩家资源
		if ($ok['tf']) {
			
			$temp = ($betNum - $freeLotto);
			if ( $temp <= 0 ) {		// 免费卷
				Hapyfish2_Island_HFC_Card::useUserCard($uid, 55041, $betNum);
			} else if ($temp > 0) {	// 免费卷和付费卷，同时使用
				Hapyfish2_Island_HFC_Card::useUserCard($uid, 55041, $freeLotto);
				Hapyfish2_Island_HFC_Card::useUserCard($uid, 55141, $temp);
			}
			
			
			$point = Hapyfish2_Island_Event_Bll_Casino::getUserPoint($uid);
			
			Hapyfish2_Island_Event_Bll_Casino::updateUserPoint($uid, ($betNum*2), ($point + $betNum*2));
			// 记录到日志
			$sumbuilding = $sumplant = $sumcard = array();
			foreach ($ok['items'] as $key => $val) {
				if (substr($key,-2) == '41') {
					$sumcard[] = $key.'*'.$val;
				}
				if (substr($key,-2) == '31' || substr($key,-2) == '32') {
					$sumplant[] = $key.'*'.$val;
				}
				if (substr($key,-2) == '21') {
					$sumbuilding[] = $key.'*'.$val;
				}
			}
			
			$log = Hapyfish2_Util_Log::getInstance();
			$log->report('casino', array($uid, $betNum, $ok['gold'], $ok['coin'], 
			join(',', $sumcard), join(',', $sumplant), join(',', $sumbuilding) ));
			
			
			$result['status'] = 1;
	        $result['goldChange'] = $ok['gold'];
	        $result['coinChange'] = $ok['coin'];
	        return array('result' => $result, 'awardsId' => $ok['ids'] );
			
		}
		
		$result['content'] = 'serverWord_110';
	    return array('result' => $result);
	}
	
	public static function itemstouser($list, $uid)
	{
		$com = new Hapyfish2_Island_Bll_Compensation();
		$coin = $gold = 0;
		$items = $ids = array();
		foreach ($list as $key => $val) {
			switch ($val['type']) {
				case '10':	// 金币
					$coin = $coin + $val['coin'];
					break;
				case '20':	// 钻石
					$gold = $gold + $val['gold'];
					break;
				case '100':	// 建筑
				case '200':	// 卡牌
					if (isset($items[$val['item_cid']])) {
						$items[$val['item_cid']] += 1;
					} else {
						$items[$val['item_cid']] = 1;
					}
					break;
			}
			
			if ($ids[$val['id']-1]) {
				$ids[$val['id']-1]['number']++;
			} else {
				$ids[$val['id']-1] = array('id'=>($val['id']-1),'type'=>$val['type'], 'coin'=>$val['coin'],
				'gold'=>$val['gold'],'item_cid'=>$val['item_cid'], 'number'=>1);
			}
		}
		
		$com->setCoin($coin);
		$com->setGold($gold);
		if ($items) {
			foreach ($items as $key => $val) {
				$com->setItem($key, $val);	
			}
		}
		
		return array('coin'=>$coin, 'gold'=>$gold, 'ids'=>array_values($ids),'items'=>$items, 'tf'=>$com->sendOne($uid,'恭喜你在大转盘中获得'));
	}
}