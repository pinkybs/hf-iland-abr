<?php

interface Hapyfish_Island_Bll_Task_Interface
{
    /**
     * user add app event callback
     *
     * @param int $uid
     * @param int $taskId
     */
    public function check($uid, $taskId);
}
