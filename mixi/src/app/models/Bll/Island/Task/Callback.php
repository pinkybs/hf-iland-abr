<?php

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    xial
 */
class Bll_Island_Task_Callback
{
    /**
     * task callback 
     *
     * @param int $context
     */
    public function finish($context)
    {
        
        $task = array('uid' => $context['uid'],
                          'tid' => $taskId,
                          'finish_time' => $nowTime,
                          'status' => 1,
                          'type' => 1);
        $dalMongoTask->insertUserTask($context);
        
        
        
    }
    
    
}
