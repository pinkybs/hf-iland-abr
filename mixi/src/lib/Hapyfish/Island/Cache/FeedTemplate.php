<?php

class Hapyfish_Island_Cache_FeedTemplate
{
	
    /**
     * get feed template info
     *
     * @param integer $app_id application id
     * @param integer $template_id template id
     * @return array
     */
    public static function getInfo($app_id, $template_id)
	{
		$key = 'FeedTemplateInfo_' . $app_id . '_' . $template_id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$info = $cache->get($key);
		if ($info === false) {
			//load from database
			$db = Hapyfish_Island_Dal_FeedTemplate::getDefaultInstance();
			$info = $db->get($app_id, $template_id);
			if ($info) {
				$cache->add($key, $info, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $info;
	}
	
    public static function cleanInfo($app_id, $template_id)
    {
 		$key = 'FeedTemplateInfo_' . $app_id . '_' . $template_id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return $cache->delete($key);   	
    }

}