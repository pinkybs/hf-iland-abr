<?php

$swfList = array(
    STATIC_HOST . '/swf/swc.swf?v=2011021801',
    STATIC_HOST . '/swf/swc2.swf?v=2011021801',
    STATIC_HOST . '/swf/swc3.swf?v=2011021801',
    STATIC_HOST . '/swf/swc4.swf?v=2011031401',
    STATIC_HOST . '/swf/levelUp.swf?v=2011021801',
    STATIC_HOST . '/swf/island1.swf?v=2011021801',
    STATIC_HOST . '/swf/sky1.swf?v=2011021801',
    STATIC_HOST . '/swf/sea1.swf?v=2011021801',
    STATIC_HOST . '/swf/dock1.swf?v=2011021801',
    STATIC_HOST . '/swf/boat1.swf?v=2011021801',
    STATIC_HOST . '/swf/itemcard1.swf?v=2011021801',
    STATIC_HOST . '/swf/player1.swf?v=2011021801',
    STATIC_HOST . '/swf/sound1.swf?v=2011021801',
    STATIC_HOST . '/swf/chongzhiIcon.swf?v=2011021801',
    STATIC_HOST . '/swf/actIconSwc.swf?v=2011021801',
    STATIC_HOST . '/swf/signWinUi.swf?v=2011021801',
	STATIC_HOST . '/swf/godCard.swf?v=2011021101',
    STATIC_HOST . '/swf/godCard.swf?v=2011021101',
    STATIC_HOST . '/swf/preloadUi.swf?v=2011031401',
);

$otherSwfs = array(
    'localeTxt'         => '/',
    'help'              => STATIC_HOST . '/swf/helpV2View.swf?v=2011021801',
    'news'				=> STATIC_HOST . '/swf/news.swf?v=2011021801',
	'exmall'			=> STATIC_HOST . '/swf/externalMallPanel.swf?v=2011021801',
	'building1'         => STATIC_HOST . '/swf/building1.swf?v=2011032101',
    'building2'         => STATIC_HOST . '/swf/building2.swf?v=2011032101',
    'building3'         => STATIC_HOST . '/swf/building3.swf?v=2011032101',
    'building4'         => STATIC_HOST . '/swf/building4.swf?v=2011032101',
    'building5'         => STATIC_HOST . '/swf/building5.swf?v=2011032101',
    'building6'         => STATIC_HOST . '/swf/building6.swf?v=2011032101',
    'building7'         => STATIC_HOST . '/swf/building7.swf?v=2011032101',
    'building8'         => STATIC_HOST . '/swf/building8.swf?v=2011032101',
    'building9'         => STATIC_HOST . '/swf/building9.swf?v=2011032101',
    'building10'        => STATIC_HOST . '/swf/building10.swf?v=2011032101'
);

$mainswf = STATIC_HOST . '/swf/piao6Sns.swf?v=2011021801';

$bgMusic = STATIC_HOST . '/swf/sound1.mp3?v=2011021801';

// interface list
$interface = array(
    'swfHostURL'        => STATIC_HOST . '/swf/',
    'jpgHostURL'        => STATIC_HOST . '/jpg/',
    'interfaceHostURL'  => HOST . '/',
    'loadFriends'       => 'api/getfriends',
    'loadInit'          => 'api/inituser?v=2011021801',
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
    'getStepAward'      => 'api/gethelpgift',
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
	'getLevelGift'		=> 'api/getlevelgift',

	'readYaoQingState'		=> 'event/getinviteflowstate',
	'getYaoQingStepAward'	=> 'event/inviteaward',
	'getgifttime'     			=> 'event/getgifttime',
	'receivetimegift'     		=> 'event/receivetimegift',
	
);

$swfResult = array(
    'swfs'      => $swfList,
    'otherSwfs' => $otherSwfs,
    'mainswf'   => $mainswf,
    'bgMusic'	=> $bgMusic,
    'interface' => $interface
);

