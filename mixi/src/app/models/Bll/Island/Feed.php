<?php


class Bll_Island_Feed
{
    /**
     * get notice
     *
     * @return array
     */
    public static function loadNotice()
    {
        //get notice list
        $noticeList = Bll_Cache_Island::getNoticeList();
        
        return $noticeList;
    }
    
    public static function readFeed($uid)
	{
		return self::getFeed($uid, 1, 10);
	}
	
    public static function insertIslandMinifeed($feed)
    {
        $dalFeed = Dal_Mongo_Feed::getDefaultInstance();

        $dalFeed->insertIslandMinifeed($feed);

        //update user feed status
        $dalFeed->updateFeedStatus($feed['uid']);
    }

    public static function insertPlantManageMinifeed($feed)
    {
        $dalFeed = Dal_Mongo_Feed::getDefaultInstance();

        $dalFeed->insertPlantManageMinifeed($feed);

        //update user feed status
        $dalFeed->updateFeedStatus($feed['uid']);
    }
    
	public static function insertMiniFeed($feed)
	{
	    $dalFeed = Dal_Mongo_Feed::getDefaultInstance();

	    $dalFeed->insertMinifeed($feed);

		//update user feed status
        $dalFeed->updateFeedStatus($feed['uid']);
	}
	
    public static function batchInsertMinifeed($feeds)
    {
        $dalFeed = Dal_Mongo_Feed::getDefaultInstance();
        $dalFeed->batchInsertMinifeed($feeds);
		
		//update user feed status
        $dalFeed->updateFeedStatus($feeds[0]['uid'], false, count($feeds));
    }	
	
	public static function getNewMiniFeedCount($uid)
	{
	    $dalFeed = Dal_Mongo_Feed::getDefaultInstance();
	    return $dalFeed->getNewMiniFeedCount($uid);
	}

   /**
     * get feed
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public static function getFeed($uid, $pageIndex, $pageSize)
    {
        $pageSize = 50;
		$dalFeed = Dal_Mongo_Feed::getDefaultInstance();

		//get user mini feed
        $feed = $dalFeed->getMinifeed($uid, $pageIndex, $pageSize);
        
        //update user feed status
        $dalFeed->updateFeedStatus($uid, true);
        
		$appId = 1;
        return self::buildFeed($feed, $appId);
    }

    /**
     * build feed
     *
     * @param array $feed
     * @param integer $appId
     * @return array
     */
    protected static function buildFeed($feed, $appId)
    {
        for ($i = 0; $i < count($feed); $i++) {
            $feed_title_template = self::getFeedTemplateTitle($appId, $feed[$i]['template_id']);
            
            $feedTitle = isset($feed[$i]['title']) ? $feed[$i]['title'] : array();
            $title = self::buildTemplate($feed[$i]['actor'], $feed[$i]['target'], $feed_title_template, $feedTitle, $feed[$i]['template_id']);

            if ($title) {
                $feed[$i]['title'] = $title;
            }
            else {
                $feed[$i]['title'] = '';
            }
            if ( $feed[$i]['template_id'] == 12 || $feed[$i]['template_id'] == 13 ) {
                unset($feed[$i]['time_type']);
            }
            unset($feed[$i]['uid']);
            unset($feed[$i]['template_id']);
            $feed[$i]['createTime'] = $feed[$i]['create_time'];
            unset($feed[$i]['create_time']);
            
        }

        return $feed;
    }

    /**
     * get feed title by template
     *
     * @param integer $app_id
     * @param integer $template_id
     * @return array
     */
    protected static function getFeedTemplateTitle($app_id, $template_id)
    {
        $template_info = self::getFeedTemplateInfo($app_id, $template_id);

        if ($template_info) {
			return $template_info['title'];
        }

        return null;
    }

    /**
     * Get feed template whole information
     *
     * @param int $app_id
     * @param int $template_id
     * @return array
     */
    protected static function getFeedTemplateInfo($app_id, $template_id)
    {
        $key = $app_id . ',' . $template_id;

        if (Zend_Registry::isRegistered('FEED_TEMPLATE_INFO')) {
            $FEED_TEMPLATE_INFO = Zend_Registry::get('FEED_TEMPLATE_INFO');

            if (isset($FEED_TEMPLATE_INFO[$key])) {
                return $FEED_TEMPLATE_INFO[$key];
            }
        }
        else {
            $FEED_TEMPLATE_INFO = array();
        }

        //Bll_Cache_FeedTemplate::clearInfo($app_id, $template_id);
        $template_info = Bll_Cache_FeedTemplate::getInfo($app_id, $template_id);

        if ($template_info) {
            $FEED_TEMPLATE_INFO[$key] = $template_info;

            Zend_Registry::set('FEED_TEMPLATE_INFO', $FEED_TEMPLATE_INFO);

            return $template_info;
        }

        return null;
    }

    /**
     * build template
     *
     * @param integer $user
     * @param integer $target
     * @param string $template
     * @param array $json_array
     * @return string
     */
    protected static function buildTemplate($user, $target, $template, $json_array, $template_id)
    {
        if ($json_array == null) {
            $json_array = array();
        }

        if (!is_array($json_array)) {
            return false;
        }

        $actor = Bll_User::getPerson($user);

        if (empty($actor)) {
            $actor_name = "____";
        }
        else {
            $actor_name = '<a href="event:' . $user . '"><font color="#00CC99">' . $actor['name'] . '</font></a>';
        }

        $json_array['actor'] = $actor_name;

        if ($target) {
            $targ = Bll_User::getPerson($target);

            if (empty($targ)) {
                $target_name = "____";
            }
            else {
            	$target_name = '<a href="event:' . $target . '"><font color="#00CC99">' .  $targ['name'] . '</font></a>';
            }

            $json_array['target'] = $target_name;

        }

        $keys = array();
        $values = array();

        if ( $template_id != 12 ) {
            foreach ($json_array as $k => $v) {
                $keys[] = '{*' . $k . '*}';
                $values[] = $v;
            }
        }
        else {
            foreach ($json_array as $k => $v) {
                $keys[] = '{*' . $k . '*}';
                if ( $k == 'money' ) {
                    if ( $v > 0 ) {
                        //$v = '偷取了<font color="#FF0000">' . $v . '金币</font>';
                        $v = '<font color="#FF0000">' . $v . 'コイン</font>を拾いました';
                    }
                    else {
                        $v = '';
                    }
                }
                else if ( $k == 'visitor_num' ) {
                    if ( $v > 0 ) {
                        //$v = '拉走了<font color="#FF0000">' . $v . '个游客</font>';
                        $v = '<font color="#FF0000">客さんを' . $v . '名</font>勧誘しました';
                    }
                    else {
                        $v = '';
                    }
                }
                $values[] = $v;
            }
        }
        return str_replace($keys, $values, $template);
    }
}