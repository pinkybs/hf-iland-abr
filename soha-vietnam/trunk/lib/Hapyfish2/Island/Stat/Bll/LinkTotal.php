<?php

class Hapyfish2_Island_Stat_Bll_LinkTotal
{
	public static function addLinkTotal($dt, $dir)
	{		
        $strDate = $dt;
        $fileName = $dir . $strDate . '/all-campPvStat-' . $strDate . '.log';

        try {
            //file not exists
            if (!file_exists($fileName)) {
                info_log($fileName . ' not exists!', 'stat_LinkTotal');
                return false;
            }
            $content = file_get_contents($fileName);
            if (!$content) {
                info_log($fileName . ' has no content!', 'stat_LinkTotal');
                return false;
            }
         
            $lines = explode("\n", $content);

   			$todayLink = array();
            foreach ($lines as $line) {
            	if (empty($line) || $line == '-100') {
            		continue;
            	}
        	
            	$linkNum = $linkIP = 0;
            	$aryLine = explode("\t", $line); 
            	$linkNum = $aryLine[2];
            	$linkIP = $aryLine[3];

            	if (empty($linkNum) || empty($linkIP)) {
            		info_log($fileName . ' has no content!', 'stat_LinkTotal');
            		continue;
            	}
            	
            	
            	if (isset($todayLink[$linkNum][$linkIP])) {
            		$todayLink[$linkNum][$linkIP] += 1;
            	} else {
            		$todayLink[$linkNum][$linkIP] = 1;
            	}
            }
            
            foreach ($todayLink as $key => $dataVo) {
				$start = 0;
            	$uniqueLink = 0;
            	
            	foreach ($dataVo as $linkNumValue) {
            		$start += $linkNumValue;
            	}
            	
            	$uniqueLink = count($dataVo);
            	$newData[$key] = $uniqueLink . '|' . $start;
            }
            
            $jsonData = json_encode($newData);
            
            $db = Hapyfish2_Island_Stat_Dal_LinkTotal::getDefaultInstance();
            try {
            	$db->addLinkTotal($strDate, $jsonData);
            } catch (Exception $e) {
				info_log($jsonData, 'stat_LinkTotal_add_Fatal');
            	return false;
            }
		} catch (Exception $e) {
            info_log($e->getMessage(), 'stat_LinkTotal');
            return false;
		}
		
        return true;
	}
	
}