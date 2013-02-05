<?php

class Hapyfish2_Island_Stat_Log_Help02
{
	public static function handle($day, $time, $file)
	{
		$content = file_get_contents($file);
		if (empty($content)) {
			info_log('no data', 'stat.log.tutorial.err');
			return;
		}
		
		$temp = explode("\n", $content);
		
		$d1 = array();
		$count = 0;
		foreach ($temp as $line) {
			if (empty($line)) {
				continue;
			}
			
			$r = explode("\t", $line);
			//
			$uid= $r[2];
			
			if (!isset($d1[$uid])) {
				$d1[$uid] = 1;
				$count++;
			}
		}
		
		return $count;
	}
	
}