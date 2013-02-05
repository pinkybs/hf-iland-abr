<?php

/**
 * application Island Task event callback interface
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/10    liz
 */
interface Bll_Island_Task_Interface
{
    /**
     * user add app event callback
     *
     * @param int $uid
     * @param int $taskId
     */
    public function check($uid, $taskId);
    
    
}
