<?php

class Dal_Mongo_Test2 extends Dal_Mongo_Abstract
{        
    protected static $_instance;
        
    /**
     * single instance of Dal_Mongo_Test
     *
     * @return Dal_Mongo_Test
     */
    public static function getDefaultInstance()
    {
        /*if (self::$_instance == null) {
            self::$_instance = new self();
        }*/

        //if (self::$_instance == null) {
            define('MONGODB_2', 'mongodb://10.194.78.96:28117');
        	$mongo = new Mongo(MONGODB_2, array('persist' => 'MONGODB_2', 'timeout' => 2000));
            self::$_instance = new self($mongo);
        //}
        

        return self::$_instance;
    }

    public function addIndexMooch()
    {
        $this->_mg->mixi_island->mooch_dock->ensureIndex( array( "owner_uid" => 1,"pid" => 1 ) , array('unique' => true, 'dropDups' => true) );
        $this->_mg->mixi_island->mooch_plant->ensureIndex( array( "id" => 1 ) , array('unique' => true, 'dropDups' => true) );
    }
}