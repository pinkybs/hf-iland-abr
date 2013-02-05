<?php

interface Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid);
    
    public function addGift($uid, $param);
    
    public function updateAppFriendship($uid, array $fids);
    
    public function postRun(Bll_Application_Abstract $application);
}
