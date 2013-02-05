<?php

class CacheController extends Zend_Controller_Action
{
    //protected $uid;

    /**
     * initialize basic data
     * @return void
     */
    public function init()
    {
        $controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
    }
    
    function clearbasicinfoAction()
    {
		Hapyfish_Island_Cache_Shop::refreshShopBackgroundList();
		Hapyfish_Island_Cache_Shop::refreshShopBuildingList();
		Hapyfish_Island_Cache_Shop::refreshShopCardList();
		Hapyfish_Island_Cache_Shop::refreshShopPlantList();
		
		Hapyfish_Island_Cache_Plant::refreshPlantPayTimeList();
		Hapyfish_Island_Cache_Plant::refreshPlantPayTicketList();
		
		echo 'OK';
		exit;
    }
    
    function clearonebuildingAction()
    {
    	$id = '49721';
		$key = '1BuildingById_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$building = $cache->get($key);
		print_r($building);
		echo '<br/>';
		
		$cache->delete($key);
		
		echo 'OK';
		exit;
    }

 }
