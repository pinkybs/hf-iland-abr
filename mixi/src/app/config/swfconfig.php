<?php

$staticUrl = Zend_Registry::get('static');

// swf list
// 'swf/v00/some.swf'
// url rewrite ==> 'swf/some.swf'
// v00 is version
$swfList = array(
    $staticUrl . '/swf/v25/swc.swf',
    $staticUrl . '/swf/v33/swc2.swf',
    $staticUrl . '/swf/v01/levelUp.swf',
    $staticUrl . '/swf/v14/building1.swf',
    $staticUrl . '/swf/v25/building2.swf',
    $staticUrl . '/swf/v07/building3.swf',
    $staticUrl . '/swf/v07/building4.swf',
    $staticUrl . '/swf/v24/building5.swf',
    $staticUrl . '/swf/v33/building6.swf',
    $staticUrl . '/swf/v33/building7.swf',
    $staticUrl . '/swf/v28/island1.swf',
    $staticUrl . '/swf/v28/sky1.swf',
    $staticUrl . '/swf/v01/sea1.swf',
    $staticUrl . '/swf/v01/dock1.swf',
    $staticUrl . '/swf/v01/boat1.swf',
    $staticUrl . '/swf/v33/itemcard1.swf',
    $staticUrl . '/swf/v31/player1.swf',
    $staticUrl . '/swf/v07/sound1.swf'
);

$otherSwfs = array(
    'localeTxt'         => 'v06/',
    'help'              => 'swf/v10/helpV2View.swf',
	'news'				=> 'swf/v31/news.swf'
);

$mainswf = $staticUrl . '/swf/v33/piao6Sns.swf';

$bgMusic = $staticUrl . '/swf/v01/sound1.mp3';

// interface list
$interface = array(
    'swfHostURL'        => $staticUrl . '/swf/',
    'jpgHostURL'        => $staticUrl . '/jpg/',
    'interfaceHostURL'  => 'http://mxisland.hapyfish.com/',
    'loadFriends'       => 'api/getfriends',
    'loadInit'          => 'api/inituser',
    'loadIsland'        => 'api/initisland',
    'loadDock'          => 'api/initdock',
    'recive'            => 'api/receiveboat',
    'steal'             => 'api/moochvisitor',
    'dockUpgrade'       => 'api/addboat',
    'loadShop'          => 'api/loadshop',
    'loadItems'         => 'api/readcard',
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
    'online'            => 'api/online',
    'readTask'          => 'api/readtask',
    'finishTask'        => 'api/finishtask',
    'loadTitles'        => 'api/readtitle',
    'selectTitle'       => 'api/changetitle',
    'loadBoatClassState'=> 'api/readship',
    'selectBoat'        => 'api/changeship',
    'unLockBoat'        => 'api/unlockship',
    'loadRemind'        => 'api/readremind',
    'sendRemind'        => 'api/addremind',
    'uploadpicture'     => 'api/uploadpicture'
);

$swfResult = array(
    'swfs'      => $swfList,
    'otherSwfs' => $otherSwfs,
    'mainswf'   => $mainswf,
    'bgMusic'	=> $bgMusic,
    'interface' => $interface
);


