<?php

require_once(CONFIG_DIR . '/language.php');

class Hapyfish2_Island_Event_Bll_Hash
{
	protected static $_pre = 'normal_';
	protected static $_timeout = 864000;	// 十天

	public static function getGift($uid)
	{
		$key = 'collectgift_haveget_' . $uid;
		$switch = self::getswitch($uid);
		if(!$switch){
			$rsp = array ();
			$rsp ['result'] ['status'] = - 1;
			$rsp ['result'] ['content'] = LANG_PLATFORM_EVENT_TXT_15;
			return $rsp;
		}

		$rsp = array ();
		$rsp ['result'] ['status'] = - 1;
		$rsp ['result'] ['content'] = LANG_PLATFORM_EVENT_TXT_16;

		$val = self::getval ( $key );

		if( !$val ) {
			$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
			$dalBuilding = Hapyfish2_Island_Dal_Building::getDefaultInstance();

			$temp1 = $dalPlant->getAllCid($uid);
			$temp2 = $dalBuilding->getAllCid($uid);

			$pids = array ();
			$bids = array ();
			foreach ( $temp1 as $k => $v ) {
				$pids [] = $v ['cid'];
			}
			foreach ( $temp2 as $k => $v ) {
				$bids [] = $v ['cid'];
			}

			$giftkey = 'collectgift';
			$giftval = self::getval ( $giftkey );
			$giftval = unserialize ( $giftval );

			$i = 0;
			foreach ( $giftval as $k => $v ) {
				$subcid = substr ( $v ['cid'], - 2 );

	    		if( $subcid == 31 || $subcid == 32 ) {
	    			$pcids = explode(',', $v['cid']);
	    			foreach($pcids as $pk => $pv) {
    					if( in_array( $pv, $pids ) ) {
    						$i++;
    						break;
    					}
    				}
				}

				if ($subcid == 21) {
					if (in_array ( $v ['cid'], $bids ))
						$i ++;
				}
			}

	    	if( $i == 5 ) {
				$jianglikey = 'jiangliid';
				$jiangliid = self::getval ( $jianglikey );
				$subjiangliid = substr ( $jiangliid, - 2 );
				$item_id = substr ( $jiangliid, 0, strlen ( $jiangliid ) - strlen ( $subjiangliid ) );

	    		if( $subjiangliid == 31 || $subjiangliid == 32 ) {
	    			$plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($jiangliid);
					$p1 = array ('uid' => $uid,
								'cid' => $jiangliid,
								'status' => 0,
								'item_id' => $item_id,
								'level' => $plantInfo['level'],
								'buy_time' => time (),
								'item_type' => $subjiangliid );
					$ok = Hapyfish2_Island_HFC_Plant::addOne($uid, $p1);
					$name = $plantInfo['name'];
				}

	    		if( $subjiangliid == 21 ) {
	    			$buildingInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($jiangliid);
					$b1 = array ('uid' => $uid,
								'cid' => $jiangliid,
								'item_type' => $subjiangliid,
								'status' => 0,
								'buy_time' => time() );
					$ok = Hapyfish2_Island_HFC_Building::addOne($uid, $b1);
					$name = $buildingInfo['name'];
				}

				if($ok) {
					$title = LANG_PLATFORM_EVENT_TXT_17 . '<font color="#FF0000">' . $name . '</font>，' . LANG_PLATFORM_EVENT_TXT_18;

		        	$minifeed = array('uid' => $uid,
		                              'template_id' => 0,
		                              'actor' => $uid,
		                              'target' => $uid,
		                              'title' => array('title' => $title),
		                              'type' => 3,
		                              'create_time' => time());

					Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
				}

				// 用户已经购买
				self::setval ( $key, '1' );

				$rsp ['result'] ['status'] = 1;

				return $rsp;
			}
		}

		return $rsp;
	}

	public static function collectionTask($uid)
	{
		$key = 'collectgift';
		$timekey = 'time';
		$xiaoxikey = 'xiaoxi';

		$rsp = array ();
		$rsp['result']['status'] = 1;
		$rsp['result']['content'] = '';
		$switch = self::getswitch($uid);

		if(!$switch){
			$rsp['result']['status'] = - 1;
			$rsp['result']['content'] = LANG_PLATFORM_EVENT_TXT_15;
			return $rsp;
		}

		$time =  self::getval ($timekey);
		$time = unserialize ( $time );
		$message =  self::getval ($xiaoxikey);
		$message = unserialize ( $message );

		$rsp ['startTime'] = time();
		$rsp ['endTime'] = $time['end'];
		$rsp ['giftName'] = $message['tishi'];
		$rsp ['action'] = $message['zhu'];
		$rsp ['data'] [] = array ('window' => 'window1', 'cid' => '', 'name' => '', 'tip' => '', 'haveGet' => false );
		$rsp ['data'] [] = array ('window' => 'window2', 'cid' => '', 'name' => '', 'tip' => '', 'haveGet' => false );
		$rsp ['data'] [] = array ('window' => 'window3', 'cid' => '', 'name' => '', 'tip' => '', 'haveGet' => false );
		$rsp ['data'] [] = array ('window' => 'window4', 'cid' => '', 'name' => '', 'tip' => '', 'haveGet' => false );
		$rsp ['data'] [] = array ('window' => 'window5', 'cid' => '', 'name' => '', 'tip' => '', 'haveGet' => false );
		$rsp ['data'] [] = array ('window' => 'window6', 'cid' => '' );

		$dalb = Hapyfish2_Island_Dal_Building::getDefaultInstance();
		$dalp = Hapyfish2_Island_Dal_Plant::getDefaultInstance();

		$val = self::getval ( $key );
		$val = unserialize ( $val );

		$jianglikey = 'jiangliid';
		$jiangliid = self::getval ( $jianglikey );

    	if( $val && $jiangliid ) {
			$temp1 = $dalp->getAllCid($uid);
			$temp2 = $dalb->getAllCid($uid);

			$pids = array ();
			$bids = array ();
			foreach ( $temp1 as $k => $v ) {
				$pids [] = $v ['cid'];
			}
			foreach ( $temp2 as $k => $v ) {
				$bids [] = $v ['cid'];
			}

			foreach ( $val as $k => $v ) {
				$rsp ['data'] [$k] ['cid'] = $v ['cid'];
				$rsp ['data'] [$k] ['name'] = $v ['name'];
				$rsp ['data'] [$k] ['tip'] = $v ['tip'];

				$subcid = substr ( $v ['cid'], - 2 );

    			if( $subcid == 31 || $subcid == 32 ) {
    				$pcids = explode(',', $v['cid']);
    				$rsp['data'][$k]['cid'] = $pcids[0];
    				foreach($pcids as $pk => $pv) {
    					if( in_array( $pv, $pids ) ) {
    						$rsp['data'][$k]['haveGet'] = true;
    						$rsp['data'][$k]['cid'] = $pv;
    						break;
    					}
    				}
    			}

    			if( $subcid == 21 ) {
    				if( in_array( $v['cid'], $bids ) )
    					$rsp['data'][$k]['haveGet'] = true;
    			}

			}

			$rsp ['data'] [5] ['cid'] = $jiangliid;
		}

		return $rsp;
	}

	public static function getval( $key )
	{
		$key = self::$_pre . $key;
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
        $data = $cache->get($key);

        if( empty($data)) {
        	$hash = Hapyfish2_Island_Event_Dal_Hash::getDefaultInstance();
        	$val = $hash->getval( $key );
        	if( $val ) {
        		$cache->set( $key, $val['val']);
        	}

        	return $val['val'];
        }

        return $data;
	}

	public static function getmemval( $key )
	{
		$key = self::$_pre . $key;
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
        $data = $cache->get($key);

        return $data;
	}

	public static function getdbval( $key )
	{
		$key = self::$_pre . $key;
		$hash = Hapyfish2_Island_Event_Dal_Hash::getDefaultInstance();
        $val = $hash->getval( $key );

        if( $val ) {
        	return $val['val'];
        }

        return $val;
	}

	public static function setval( $key, $val )
	{
		$key = self::$_pre . $key;
        $hash = Hapyfish2_Island_Event_Dal_Hash::getDefaultInstance();
        $data = $hash->getval( $key );
        $data = $data ? $hash->update( $key, $val ) : $hash->insert( $key, $val );

        if( $data ) {
          	$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
	       	$cache->set( $key, $val);
        }
	}



	public static function clearval( $key )
	{
		self::clearmemval($key);
		self::cleardbval($key);
	}

	public static function clearmemval( $key )
	{
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
        $cache->delete( $key );
	}

	public static function cleardbval($key)
	{
		$hash = Hapyfish2_Island_Event_Dal_Hash::getDefaultInstance();
        $hash->delete( $key );
	}

	public static function getallcollent()
	{
		$hash = Hapyfish2_Island_Event_Dal_Hash::getDefaultInstance();
	    $uid_list = $hash->getallhaveget();

	    return $uid_list;
	}

	public static function getswitch($uid)
	{
		$key = "collectcontrolswitch";
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$swith = $cache->get($key);

		if($swith) {
			if($swith['type'] == "open") {
				return true;
			} else {
				if($swith['uid']) {
					$uidlist = explode(',',$swith['uid']);
					if(in_array($uid, $uidlist)) {
						return true;
					} else {
						return false;
					}
				} else {
					return false;
				}
			}
		} else {
			return true;
		}
	}

	public static function clearall()
	{
	    $id_list = self::getallcollent();
	    if($id_list){
	        foreach($id_list as $data=>$value){
	            self::clearval( $value );
	        }
	    }
	}

}