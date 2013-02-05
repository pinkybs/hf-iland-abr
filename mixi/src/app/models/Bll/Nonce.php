<?php

class Bll_Nonce
{
    /**
     * check nonce is valid
     *
     * @param string $nonce
     * @return bool
     */
    public static function isValid($nonce, &$data)
    {        
        $data = Bll_Cache_Nonce::getNonce($nonce);
        
        $valid = false;
        
        if ($data) {
            $valid = true;
        }
        
        return $valid;
    }
    
    /**
     * create an new access nonce
     *
     * @return string
     */
    public static function createNonce($app_id, $owner_id, $viewer_id, $app_name = '')
    {
        $nonce = Bll_Secret::getUUID();
        
        $data = array(
            'nonce' => $nonce,
            'app_id' => $app_id,
        	'owner_id' => $owner_id,
        	'viewer_id' => $viewer_id,
        	'app_name' => $app_name
        );
        
        Bll_Cache_Nonce::createNonce($nonce, $data);
        
        return $nonce;
    }

}