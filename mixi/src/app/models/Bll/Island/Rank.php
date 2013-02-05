<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Liz
 */
class Bll_Island_Rank extends Bll_Abstract
{
    /**
     * load friend info
     *
     * @param integer $uid
     * @return array
     */
	public function loadFriends($uid)
    {
        $fids = Bll_Friend::getFriends($uid);
        $fids[] = $uid;
        
        $gmUid = '28899703';
        $fids[] = $gmUid;
        $gmInApp = Bll_User::isAppUser($gmUid);
        if ( $gmInApp ) {
            $fids[] = $gmUid;
        }
        
        //$dalRank = Dal_Island_Rank::getDefaultInstance();
        //$friendLst = $dalRank->getUserFriends($fids);
        $friendLst = Bll_Cache_Island_User::getUserFriendsAll($uid, $fids);
        
        if ( $friendLst ) {
            Bll_User::appendPeople($friendLst, 'uid');
        }
        
        //return $friendLst;
        return array('friends' => $friendLst,'maxPage' => 1);
    }

    /**
     * load friend info
     *
     * @param integer $uid
     * @return array
     */
    public function loadFriendsPage($uid, $pageIndex, $pageSize)
    {
        $fids = Bll_Friend::getFriends($uid);
        $fids[] = $uid;
        
        $gmUid = '28899703';
        $gmInApp = Bll_User::isAppUser($gmUid);
        if ( $gmInApp ) {
            $fids[] = $gmUid;
        }
        
        $friendLstAll = Bll_Cache_Island_User::getUserFriendsAll($uid, $fids);
            
        $friendCount = count($friendLstAll);
        $maxPage = ceil($friendCount / $pageSize);
        $start = ($pageIndex - 1)*$pageSize;
        $end = $start + $pageSize;
        $friendLst = array();
        for ($i = $start; $i < $end; $i++){
            if ( empty($friendLstAll[$i]) ) {
            	break;
            }
                $friendLst[] = $friendLstAll[$i];
        }

        if ( $friendLst ) {
            Bll_User::appendPeople($friendLst, 'uid');
        }
                    
        return array('friends' => $friendLst,'maxPage' => $maxPage);
    }
    
    /**
     * get user rank
     * @param integer $uid
     * @param integer $type
     * @return array $rankVo
     */
    public function userRank($uid, $type)
    {
        $dalRank = Dal_Island_Rank::getDefaultInstance();

        $friendLst = array();
        if ($uid) {
        	$orderBy = 'exp';
	        if ($type == 2) {
	        	$orderBy = 'coin';
	        }
            $friendLst = $dalRank->getUserRank($orderBy);
        }else {
            return $friendLst;
        }

        $rankVo = array();
        if ( $friendLst ) {
            Bll_User::appendPeople($friendLst, 'uid');

            //get my exp value or coin value
	        $myCol = $dalRank->getMyType($uid, $orderBy);
	        //get my rank
			$myRank = $dalRank->getMyRankAll($orderBy, $myCol);

            $rankVo['friends'] = $friendLst;
			$rankVo['myRank'] = $myRank;
        }

        return $rankVo;
    }
}