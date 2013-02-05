<?php

class Bll_Application_Plugin_Island implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        //if is new user
    	if (!Bll_User::isAppUser($uid)) {
    	    $bllUser = new Bll_Island_User();
			$bllUser->joinUser($uid);
        }
    }

    public function addGift($uid, $param)
    {
        $gid = $param['hf_gift_id'];
        $st = $param['hf_st'];
        $sig = $param['hf_sig'];
        $sender = $param['hf_sender'];
        
        if (md5($gid . $sender . $st . APP_KEY . APP_SECRET) == $sig) {
            $dalMongoGift = Dal_Mongo_Gift::getDefaultInstance();
            $isExists = $dalMongoGift->getSendGift($sig);
            
            //add gift
            if ($isExists) {
                Bll_Island_Gift::insertGift($sender, $uid, $gid);
            }
        }
    }
    
    public function updateAppFriendship($uid, array $fids)
    {
        //TODO:
    }

    public function postRun(Bll_Application_Abstract $application)
    {
        //$request = $application->getRequest();
        // get viewerId
        //$viewerId = $application->getViewerId();
        
        $url = '/flash';
        $application->redirect($url);
    }
}
