<?php
class Hapyfish2_Island_Bll_Permissiongift
{

	public static function getval( $uid )
	{
		$key = 'permission:gift:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);

		$data = $cache->get( $key );
		if( $data === false ) {

			$pg = Hapyfish2_Island_Dal_Permissiongift::getDefaultInstance();
			$data = $pg->gettf( $uid );
			$data = $data ? $data['tf'] : 0;
			$cache->set( $key, $data );
			// echo 'db';
			return $data;
		}
		// echo 'memcache';
		return $data;

	}

	public static function setval( $uid )
	{
		$key = 'permission:gift:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);

		try {
			$tf = $cache->set( $key, 1 );
			if( $tf === false ) return false;
		} catch ( Exception $e ) {
			info_log( 'dal_setval_mc:uid:'. $uid.':message:' .$e->getMessage(), 'permissiongift');
			return false;
		}

		try {
			$pg = Hapyfish2_Island_Dal_Permissiongift::getDefaultInstance();
			$data = $pg->gettf( $uid );
			if( empty( $data ) ) {
				$num = $pg->insert($uid, 1);
			} else {
				$num = $pg->update($uid, 1);
			}

			return ( $num == 1 ) ? true : false;
		} catch( Exception $e ) {
			info_log( 'dal_setval_db:uid:'. $uid.':message:' .$e->getMessage(), 'permissiongift');
			return false;
		}

	}

}