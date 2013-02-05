<?php

class Hapyfish2_Cache_Feed 
{
    protected static $_instance;

    /**
     * Memcached object
     *
     * @var mixed memcached object
     */
    protected $_memcached = null;
    
    public function __construct($mc)
    {
        $this->_memcached = $mc;
    }

    public function delete($key)
    {
        return $this->_memcached->delete($key);
    }
    
    public function get($key)
    {
    	return $this->_memcached->get($key);
    }
    
    public function set($key, $data, $time = 604800)
    {
    	return $this->_memcached->set($key, $data, $time);
    }
    
    public function increment($key, $value)
    {
    	$this->_memcached->increment($key, $value);
    	if ($this->_memcached->getResultCode() == Memcached::RES_NOTSTORED) {
    		$this->_memcached->add($key, $value);
    	}
    }
	
    public function insertMiniFeed($key, $feed, $time = 604800)
    {
    	$try = 5;
    	$null = null;
    	$maxLen = 50;
    	$ok = false;
    	$first = false;
    	
    	while($try > 0) {
    	    $data = $this->_memcached->get($key, $null, $token);
    	    
    	    if ($data === false) {
    	        if ($this->_memcached->getResultCode() == Memcached::RES_NOTFOUND) {
    				$data = array();
    				$first = true;
    			} else {
    				break;
    			}
    	    }

    		if (count($data) >= $maxLen) {
    			$data = array_splice($data, 0, $maxLen - 1);
    		}
    		
    		array_unshift($data, $feed);
    		
    		if ($first) {
    			$this->_memcached->add($key, $data, $time);
    		} else {
    			$this->_memcached->cas($token, $key, $data, $time);
    		}
			
    		$resultCode = $this->_memcached->getResultCode();
			if ($resultCode == Memcached::RES_SUCCESS || $resultCode == Memcached::RES_END) {
				$ok = true;
				break;
			}
				
			$try--;

    	}
    	
    	return $ok;
    }
    
    public function insertIslandMinifeed($key, $feed, $time = 604800)
    {
    	if ($feed[1] != 12) {
	    	return;
	    }
    	
    	$try = 5;
    	$null = null;
    	$maxLen = 50;
    	$ok = false;
    	$first = false;

    	while($try > 0) {
    	    $data = $this->_memcached->get($key, $null, $token);
    	    
    	    if ($data === false) {
    	        if ($this->_memcached->getResultCode() == Memcached::RES_NOTFOUND) {
    				$data = array();
    				$first = true;
    			} else {
    				break;
    			}
    	    }
    	    
    	    $num = count($data);
    	    
    	    if ($num >= $maxLen) {
    			$data = array_splice($data, 0, $maxLen - 1);
    		}
    	    
    	    if ($num > 0) {
    			$timeType = $this->getTimeType($feed[6]);
    			$data = $this->findIslandMiniFeed($timeType, $data, $feed);
    		}

    		array_unshift($data, $feed);
    		
    	    if ($first) {
    			$this->_memcached->add($key, $data, $time);
    		} else {
    			$this->_memcached->cas($token, $key, $data, $time);
    		}
			
    		$resultCode = $this->_memcached->getResultCode();
			if ($resultCode == Memcached::RES_SUCCESS || $resultCode == Memcached::RES_END) {
				$ok = true;
				break;
			}
				
			$try--;

    	}
    	
    	return $ok;
    }
    
    public function insertPlantManageMinifeed($key, $feed, $time = 604800)
    {
    	if ($feed[1] != 13) {
	    	return;
	    }
    	
    	$try = 5;
    	$null = null;
    	$maxLen = 50;
    	$ok = false;
    	$first = false;

    	while($try > 0) {
    	    $data = $this->_memcached->get($key, $null, $token);
    	    
    	    if ($data === false) {
    	        if ($this->_memcached->getResultCode() == Memcached::RES_NOTFOUND) {
    				$data = array();
    				$first = true;
    			} else {
    				break;
    			}
    	    }
    	    
    	    $num = count($data);
    	    
    	    if ($num >= $maxLen) {
    			$data = array_splice($data, 0, $maxLen - 1);
    		}
    	    
    	    if ($num > 0) {
    			$timeType = $this->getTimeType($feed[6]);
    			$data = $this->findPlantManageMinifeed($timeType, $data, $feed);
    		}

    		array_unshift($data, $feed);
    		
    	    if ($first) {
    			$this->_memcached->add($key, $data, $time);
    		} else {
    			$this->_memcached->cas($token, $key, $data, $time);
    		}
			
    		$resultCode = $this->_memcached->getResultCode();
			if ($resultCode == Memcached::RES_SUCCESS || $resultCode == Memcached::RES_END) {
				$ok = true;
				break;
			}
				
			$try--;

    	}
    	
    	return $ok;
    }
    
    protected function findIslandMiniFeed($timeType, $data, &$feed)
    {
    	$newdata = array();
    	$find = false;
    	
    	foreach ($data as $item) {
    		if (!$find && $item[1] == 12 && $item[3] == $feed[3] && $item[4] == $feed[4]) {
    			if ($this->getTimeType($item[6]) == $timeType) {
    				$find = true;
    				$feed[5]['money'] += $item[5]['money'];
    				continue;
    			}
    		}
    		
    		$newdata[] = $item;
    	}
    	
		return $newdata;
    }
    
    protected function findPlantManageMinifeed($timeType, $data, &$feed)
    {
    	$newdata = array();
    	$find = false;
    	
    	foreach ($data as $item) {
    		if (!$find && $item[1] == 13 && $item[3] == $feed[3] && $item[4] == $feed[4]) {
    			if ($this->getTimeType($item[6]) == $timeType) {
    				$find = true;
    				$feed[5]['manage_num'] += $item[5]['manage_num'];
    				continue;
    			}
    		}
    		
    		$newdata[] = $item;
    	}
    	
		return $newdata;
    }
    
    protected function getTimeType($time)
    {
        $hour = date('H', $time);
        switch ($hour) {
            case $hour >= 0 && $hour < 2 :
                $date = '00';
                break;
            case $hour >= 2 && $hour < 4 :
                $date = '01';
                break;
            case $hour >= 4 && $hour < 6 :
                $date = '02';
                break;
            case $hour >= 6 && $hour < 8 :
                $date = '03';
                break;
            case $hour >= 8 && $hour < 10 :
                $date = '04';
                break;
            case $hour >= 10 && $hour < 12 :
                $date = '05';
                break;
            case $hour >= 12 && $hour < 14 :
                $date = '06';
                break;
            case $hour >= 14 && $hour < 16 :
                $date = '07';
                break;
            case $hour >= 16 && $hour < 18 :
                $date = '08';
                break;
            case $hour >= 18 && $hour < 20 :
                $date = '09';
                break;
            case $hour >= 20 && $hour < 22 :
                $date = '10';
                break;
            case $hour >= 22 && $hour < 24 :
                $date = '11';
                break;
        }
        
        return date('Ymd', $time) . $date;
    }
}