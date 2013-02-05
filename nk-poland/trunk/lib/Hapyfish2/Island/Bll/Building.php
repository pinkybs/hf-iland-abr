<?php

class Hapyfish2_Island_Bll_Building
{
    public static function getAllOnIsland($uid, $islandId)
    {
        $data = Hapyfish2_Island_HFC_Building::getOnIsland($uid, $islandId);
        $buildings = array();
        
        if ($data) {
        	foreach ($data as $item) {
        		$buildings[] = self::handlerBuilding($item);
        	}
        }
    	
		return $buildings;
    }
    
    public static function handlerBuilding(&$item)
    {
    	$building = array(
    		'id' => $item['id'] . $item['item_type'],
    		'cid' => $item['cid'],
			'x' => $item['x'],
			'y' => $item['y'],
			'z' => $item['z'],
			'mirro' => $item['mirro']
    	);
    	
    	return $building;
    }

}