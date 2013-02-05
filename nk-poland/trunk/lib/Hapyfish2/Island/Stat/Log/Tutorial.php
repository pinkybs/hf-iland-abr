<?php

class Hapyfish2_Island_Stat_Log_Tutorial
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
                $helpInfo = array(
                        'ucount' => 0, 'mcount' => 0, 'fcount' => 0, 'ucount1' => 0, 'mcount1' => 0, 'fcount1' => 0, 
                        'help_1' => array('u' => 0, 'm' => 0, 'f' => 0, 'u1' => 0, 'm1' => 0, 'f1' => 0),
                        'help_2' => array('u' => 0, 'm' => 0, 'f' => 0, 'u1' => 0, 'm1' => 0, 'f1' => 0),
                        'help_3' => array('u' => 0, 'm' => 0, 'f' => 0, 'u1' => 0, 'm1' => 0, 'f1' => 0),
                        'help_4' => array('u' => 0, 'm' => 0, 'f' => 0, 'u1' => 0, 'm1' => 0, 'f1' => 0),
                        'help_5' => array('u' => 0, 'm' => 0, 'f' => 0, 'u1' => 0, 'm1' => 0, 'f1' => 0),
                        'help_6' => array('u' => 0, 'm' => 0, 'f' => 0, 'u1' => 0, 'm1' => 0, 'f1' => 0),
                        'help_7' => array('u' => 0, 'm' => 0, 'f' => 0, 'u1' => 0, 'm1' => 0, 'f1' => 0),
                        'help_8' => array('u' => 0, 'm' => 0, 'f' => 0, 'u1' => 0, 'm1' => 0, 'f1' => 0)
                );
                foreach ($temp as $line) {
                        if (empty($line)) {
                                continue;
                        }

                        $r = explode("\t", $line);
                        //
                        $uid= $r[2];
                        $help= $r[3];
                        $joinTime = $r[4];
                        $gender = $r[5];
                        if (!isset($d1[$uid])) {
                                $d1[$uid] = 1;
                                $helpInfo['ucount']++;
                                if ($gender == 1) {
                                        $helpInfo['mcount'] ++;
                                } else {
                                        $helpInfo['fcount'] ++;
                                }
                                if ($joinTime > $time) {
                                        $helpInfo['ucount1']++;
                                        if ($gender == 1) {
                                                $helpInfo['mcount1'] ++;
                                        } else {
                                                $helpInfo['fcount1'] ++;
                                        }
                                }
                        }

                        $helpInfo['help_' . $help]['u']++;
                        if ($gender == 1) {
                                $helpInfo['help_' . $help]['m']++;
                        } else {
                                $helpInfo['help_' . $help]['f']++;
                        }
                        if ($joinTime > $time) {
                                $helpInfo['help_' . $help]['u1']++;
                                if ($gender == 1) {
                                        $helpInfo['help_' . $help]['m1']++;
                                } else {
                                        $helpInfo['help_' . $help]['f1']++;
                                }
                        }
                }

                $newData = array('log_time' => $day,
                                 'data' => json_encode($helpInfo));
                $dal = Hapyfish2_Island_Stat_Dal_Tutorial::getDefaultInstance();
                $dal->insert($newData);
                
                return $helpInfo;
        }

    public static function getDay($day)
    {
        $data = null;
        try {
            $dal = Hapyfish2_Island_Stat_Dal_Tutorial::getDefaultInstance();
            $data = $dal->getDay($day); 
        } catch (Exception $e) {
            
        }
        
        return $data;
    }
}