<?php

class Hapyfish_Island_Dal_FeedTemplate extends Hapyfish_Island_Dal_Abstract
{
	protected $table_feed_template = 'feed_template';

    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish_Island_Dal_FeedTemplate
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function get($app_id, $template_id)
    {
        $sql = "SELECT * FROM $this->table_feed_template WHERE app_id=:app_id AND template_id=:template_id";
        $params = array(
            'app_id' => $app_id,
            'template_id' => $template_id
        );
        
        return $this->_rdb->fetchRow($sql, $params);
    }
    
}