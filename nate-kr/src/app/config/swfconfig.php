<?php

if ($highLoading) {
	$swfList = array(
	    STATIC_HOST . '/swf/swc.swf?v=2011051901',
	    STATIC_HOST . '/swf/swc2.swf?v=2011042201',
	    STATIC_HOST . '/swf/swc3.swf?v=2011041901',
	    STATIC_HOST . '/swf/levelUp.swf?v=2011041901',
	    STATIC_HOST . '/swf/building1.swf?v=2011041901',
	    STATIC_HOST . '/swf/building2.swf?v=2011061901',
	    STATIC_HOST . '/swf/building3.swf?v=2011041901',
	    STATIC_HOST . '/swf/building4.swf?v=2011041901',
	    STATIC_HOST . '/swf/building5.swf?v=2011041901',
	    STATIC_HOST . '/swf/building6.swf?v=2011061901',
	    STATIC_HOST . '/swf/building7.swf?v=2011061901',
	    STATIC_HOST . '/swf/building8.swf?v=2011041902',
	    STATIC_HOST . '/swf/building9.swf?v=2011041901',
	    STATIC_HOST . '/swf/building10.swf?v=2011041901',
	    STATIC_HOST . '/swf/building11.swf?v=2011061901',
	    STATIC_HOST . '/swf/island1.swf?v=2011041901',
	    STATIC_HOST . '/swf/sky1.swf?v=2011041901',
	    STATIC_HOST . '/swf/sea1.swf?v=2011041901',
	    STATIC_HOST . '/swf/dock1.swf?v=2011041901',
	    STATIC_HOST . '/swf/boat1.swf?v=2011041901',
	    STATIC_HOST . '/swf/itemcard1.swf?v=2011041901',
	    STATIC_HOST . '/swf/player1.swf?v=2011041901',
	    STATIC_HOST . '/swf/sound1.swf?v=2011041901',
	    STATIC_HOST . '/swf/chongzhiIcon.swf?v=2011041901',
	    STATIC_HOST . '/swf/actIconSwc.swf?v=2011041902',
	    STATIC_HOST . '/swf/signWinUi.swf?v=2011041901',
	);

	$otherSwfs = array(
	    'localeTxt'         => '/',
	    'help'              => 'swf/helpV2View.swf?v=2011041901',
	    'news'				=> 'swf/news.swf?v=2012042001',
		'exmall'			=> 'swf/externalMallPanel.swf?v=2011041901'
	);
} else {
	$swfList = array(
	    STATIC_HOST . '/swf/swc.swf?v=2011051901',
	    STATIC_HOST . '/swf/swc2.swf?v=2011042201',
	    STATIC_HOST . '/swf/swc3.swf?v=2011041901',
	    STATIC_HOST . '/swf/levelUp.swf?v=2011041901',
	    STATIC_HOST . '/swf/building1.swf?v=2011041901',
		STATIC_HOST . '/swf/building2.swf?v=2011061901',
		STATIC_HOST . '/swf/building3.swf?v=2011041901',
		STATIC_HOST . '/swf/building4.swf?v=2011041901',
	    STATIC_HOST . '/swf/island1.swf?v=2011041901',
	    STATIC_HOST . '/swf/sky1.swf?v=2011041901',
	    STATIC_HOST . '/swf/sea1.swf?v=2011041901',
	    STATIC_HOST . '/swf/dock1.swf?v=2011041901',
	    STATIC_HOST . '/swf/boat1.swf?v=2011041901',
	    STATIC_HOST . '/swf/itemcard1.swf?v=2011041901',
	    STATIC_HOST . '/swf/player1.swf?v=2011041901',
	    STATIC_HOST . '/swf/sound1.swf?v=2011041901',
	    STATIC_HOST . '/swf/chongzhiIcon.swf?v=2011041901',
	    STATIC_HOST . '/swf/actIconSwc.swf?v=2011050402',
	    STATIC_HOST . '/swf/signWinUi.swf?v=2011041901',
	);

	$otherSwfs = array(
	    'localeTxt'         => '/',
	    'help'              => 'swf/helpV2View.swf?v=2011041901',
	    'news'				=> 'swf/news.swf?v=2011041901',
		'exmall'			=> 'swf/externalMallPanel.swf?v=2011041901',
		'building5'			=> STATIC_HOST . '/swf/building5.swf?v=2011041901',
		'building6'			=> STATIC_HOST . '/swf/building6.swf?v=2011061901',
		'building7'			=> STATIC_HOST . '/swf/building7.swf?v=2011061901',
		'building8'			=> STATIC_HOST . '/swf/building8.swf?v=2011041901',
		'building9'			=> STATIC_HOST . '/swf/building9.swf?v=2011041901',
		'building10'			=> STATIC_HOST . '/swf/building10.swf?v=2011041901',
		'building11'			=> STATIC_HOST . '/swf/building11.swf?v=2011061901',
	);
}

$mainswf = STATIC_HOST . '/swf/piao6Sns.swf?v=2011051801';

$bgMusic = STATIC_HOST . '/swf/sound1.mp3?v=2011041901';

// interface list
$interface = array(
    'swfHostURL'        => STATIC_HOST . '/swf/',
    'jpgHostURL'        => STATIC_HOST . '/jpg/',
    'interfaceHostURL'  => HOST . '/',
    'loadFriends'       => 'api/getfriends',
    'loadInit'          => 'api/inituser?v=2011071401',
    'loadIsland'        => 'api/initisland',
    'loadDock'          => 'api/initdock',
    'recive'            => 'api/receiveboat',
    'steal'             => 'api/moochvisitor',
    'dockUpgrade'       => 'api/addboat',
    'loadShop'          => 'api/loadshop',
    'loadItems'         => 'api/loaditems',
    'saleItems'         => 'api/saleitem',
    'useItem'           => 'api/usecard',
    'buyItem'           => 'api/buyitem',
    'saveDiy'           => 'api/diyisland',
    'loadDiary'         => 'api/readfeed',
    'loadUserInfo'      => 'api/inituserinfo',
    'changeHelp'        => 'api/changehelp',
    'buildingPay'       => 'api/harvestplant',
    'takeBuildingEvent' => 'api/manageplant',
    'buildingUpgrade'   => 'api/upgradeplant',
    'buildingSteal'     => 'api/moochplant',
    'readTask'          => 'api/readtask',
    'finishTask'        => 'api/finishtask',
    'loadTitles'        => 'api/readtitle',
    'selectTitle'       => 'api/changetitle',
    'loadBoatClassState'=> 'api/readship',
    'selectBoat'        => 'api/changeship',
    'unLockBoat'        => 'api/unlockship',
    'loadRemind'        => 'api/readremind',
    'sendRemind'        => 'api/addremind',
    'getGemNum'			=> 'api/getgold',
	'getGiftList'		=> 'api/getgiftpackagelist',
	'openPack'			=> 'api/opengiftpackage',
	'updateGiftNum'		=> 'api/getgiftpackagenum',
	'useAllPay'			=> 'api/harvestallplant',
	'gainDailyAward'	=> 'api/gaindailyawards',
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

