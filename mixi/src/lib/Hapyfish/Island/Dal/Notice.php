<?php

/**
 * notice datebase's Operation
 *
 *
 * @package    Dal
 * @create      2010/08/18    Hwq
 */
class Hapyfish_Island_Dal_Notice extends Hapyfish_Island_Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_name = 'island_public_notice';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get main notice 
     *
     * @return array
     */
    public function getNoticeList()
    {
        $sql = "SELECT * FROM $this->table_name WHERE isClose=1 ORDER BY position ASC,priority ASC";
        return $this->_rdb->fetchAll($sql);
    }
}