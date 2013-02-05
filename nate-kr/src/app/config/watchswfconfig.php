<?php

if ($highLoading) {
	$swfList = array(
	    STATIC_HOST . '/swf/swc.swf?v=2011051301',
	    STATIC_HOST . '/swf/swc2.swf?v=2011030201',
	    STATIC_HOST . '/swf/swc3.swf?v=2011030201',
	    STATIC_HOST . '/swf/levelUp.swf?v=2011030201',
	    STATIC_HOST . '/swf/building1.swf?v=2011030201',
	    STATIC_HOST . '/swf/building2.swf?v=2011030201',
	    STATIC_HOST . '/swf/building3.swf?v=2011030201',
	    STATIC_HOST . '/swf/building4.swf?v=2011030201',
	    STATIC_HOST . '/swf/building5.swf?v=2011030201',
	    STATIC_HOST . '/swf/building6.swf?v=2011030201',
	    STATIC_HOST . '/swf/building7.swf?v=2011030201',
	    STATIC_HOST . '/swf/building8.swf?v=2011030201',
	    STATIC_HOST . '/swf/building9.swf?v=2011030201',
	    STATIC_HOST . '/swf/building10.swf?v=2011030201',
	    STATIC_HOST . '/swf/building11.swf?v=2011030201',
	    STATIC_HOST . '/swf/island1.swf?v=2011030201',
	    STATIC_HOST . '/swf/sky1.swf?v=2011030201',
	    STATIC_HOST . '/swf/sea1.swf?v=2011030201',
	    STATIC_HOST . '/swf/dock1.swf?v=2011030201',
	    STATIC_HOST . '/swf/boat1.swf?v=2011030201',
	    STATIC_HOST . '/swf/itemcard1.swf?v=2011030201',
	    STATIC_HOST . '/swf/player1.swf?v=2011030201',
	    STATIC_HOST . '/swf/sound1.swf?v=2011030201',
	    STATIC_HOST . '/swf/chongzhiIcon.swf?v=2011030201',
	    STATIC_HOST . '/swf/actIconSwc.swf?v=2011050402',
	    STATIC_HOST . '/swf/signWinUi.swf?v=2011030201',
	);

	$otherSwfs = array(
	    'localeTxt'         => '/',
	    'help'              => 'swf/helpV2View.swf?v=2011030201',
	    'news'				=> 'swf/news.swf?v=2011030201',
		'exmall'			=> 'swf/externalMallPanel.swf?v=2011030201'
	);
} else {
	$swfList = array(
	    STATIC_HOST . '/swf/swc.swf?v=2011051301',
	    STATIC_HOST . '/swf/swc2.swf?v=2011030201',
	    STATIC_HOST . '/swf/swc3.swf?v=2011030201',
	    STATIC_HOST . '/swf/levelUp.swf?v=2011030201',
	    STATIC_HOST . '/swf/building1.swf?v=2011030201',
		STATIC_HOST . '/swf/building2.swf?v=2011030201',
		STATIC_HOST . '/swf/building3.swf?v=2011030201',
		STATIC_HOST . '/swf/building4.swf?v=2011030201',
	    STATIC_HOST . '/swf/island1.swf?v=2011030201',
	    STATIC_HOST . '/swf/sky1.swf?v=2011030201',
	    STATIC_HOST . '/swf/sea1.swf?v=2011030201',
	    STATIC_HOST . '/swf/dock1.swf?v=2011030201',
	    STATIC_HOST . '/swf/boat1.swf?v=2011030201',
	    STATIC_HOST . '/swf/itemcard1.swf?v=2011030201',
	    STATIC_HOST . '/swf/player1.swf?v=2011030201',
	    STATIC_HOST . '/swf/sound1.swf?v=2011030201',
	    STATIC_HOST . '/swf/chongzhiIcon.swf?v=2011030201',
	    STATIC_HOST . '/swf/actIconSwc.swf?v=2011030202',
	    STATIC_HOST . '/swf/signWinUi.swf?v=2011030201',
	);

	$otherSwfs = array(
	    'localeTxt'         => '/',
	    'help'              => 'swf/helpV2View.swf?v=2011030201',
	    'news'				=> 'swf/news.swf?v=2011030201',
		'exmall'			=> 'swf/externalMallPanel.swf?v=2011030201',
		'building5'			=> STATIC_HOST . '/swf/building5.swf?v=2011030201',
		'building6'			=> STATIC_HOST . '/swf/building6.swf?v=2011030201',
		'building7'			=> STATIC_HOST . '/swf/building7.swf?v=2011030201',
		'building8'			=> STATIC_HOST . '/swf/building8.swf?v=2011030201',
		'building9'			=> STATIC_HOST . '/swf/building9.swf?v=2011030201',
		'building10'		=>STATIC_HOST . '/swf/building10.swf?v=2011030201',
	 	'building11'		=>STATIC_HOST . '/swf/building11.swf?v=2011030201',
	);
}

$mainswf = STATIC_HOST . '/swf/piao6Sns.swf?v=2011030201';

$bgMusic = STATIC_HOST . '/swf/sound1.mp3?v=2011030201';

// interface list
$interface = array(
    'swfHostURL'        => STATIC_HOST . '/swf/',
    'jpgHostURL'        => STATIC_HOST . '/jpg/',
    'interfaceHostURL'  => HOST . '/',
    'loadFriends'       => 'apiwatch/getfriends',
    'loadInit'          => 'apiwatch/inituser?v=2011070601',
    'loadIsland'        => 'apiwatch/initisland',
    'loadDock'          => 'apiwatch/initdock',
    'recive'            => 'apiwatch/receiveboat',
    'steal'             => 'apiwatch/moochvisitor',
    'dockUpgrade'       => 'apiwatch/addboat',
    'loadShop'          => 'apiwatch/loadshop',
    'loadItems'         => 'apiwatch/loaditems',
    'saleItems'         => 'apiwatch/saleitem',
    'useItem'           => 'apiwatch/usecard',
    'buyItem'           => 'apiwatch/buyitem',
    'saveDiy'           => 'apiwatch/diyisland',
    'loadDiary'         => 'apiwatch/readfeed',
    'loadUserInfo'      => 'apiwatch/inituserinfo',
    'changeHelp'        => 'apiwatch/changehelp',
    'buildingPay'       => 'apiwatch/harvestplant',
    'takeBuildingEvent' => 'apiwatch/manageplant',
    'buildingUpgrade'   => 'apiwatch/upgradeplant',
    'buildingSteal'     => 'apiwatch/moochplant',
    'readTask'          => 'apiwatch/readtask',
    'finishTask'        => 'apiwatch/finishtask',
    'loadTitles'        => 'apiwatch/readtitle',
    'selectTitle'       => 'apiwatch/changetitle',
    'loadBoatClassState'=> 'apiwatch/readship',
    'selectBoat'        => 'apiwatch/changeship',
    'unLockBoat'        => 'apiwatch/unlockship',
    'loadRemind'        => 'apiwatch/readremind',
    'sendRemind'        => 'apiwatch/addremind',
    'getGemNum'			=> 'apiwatch/getgold',
	'getGiftList'		=> 'apiwatch/getgiftpackagelist',
	'openPack'			=> 'apiwatch/opengiftpackage',
	'updateGiftNum'		=> 'apiwatch/getgiftpackagenum',
	'useAllPay'			=> 'apiwatch/harvestallplant',
	'gainDailyAward'	=> 'apiwatch/gaindailyawards',
	'getStarGift'     	=> 'api/getstargift',
	'readStarGift'     	=> 'api/readstargift',
	'readYaoQingState'		=> 'event/getinviteflowstate',
	'getYaoQingStepAward'	=> 'event/inviteaward'

);

$swfResult = array(
    'swfs'      => $swfList,
    'otherSwfs' => $otherSwfs,
    'mainswf'   => $mainswf,
    'bgMusic'	=> $bgMusic,
    'interface' => $interface
);

