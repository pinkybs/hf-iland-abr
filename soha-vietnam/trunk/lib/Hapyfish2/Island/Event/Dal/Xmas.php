<?php

class Hapyfish2_Island_Event_Dal_Xmas
{
    protected static $_instance;

    protected $table_event_xmas = 'island_event_xmas';

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Plant
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

	public function getXmas()
	{
		$sql = " SELECT * FROM $this->table_event_xmas ";

		$db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];

    	return $rdb->fetchAll($sql);
	}

}
