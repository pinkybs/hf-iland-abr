<?php
require_once 'Zend/Service/Amazon/S3.php';

class Hapyfish2_Util_AmazonS3
{
    protected static $_instance;

    const AMAZONS3_PRE_PATH = 'http://s3.amazonaws.com/';

    protected $_s3;

    protected $_bucket;

    public function __construct($awsKey, $awsSecretKey)
    {
        $this->_s3 = new Zend_Service_Amazon_S3($awsKey, $awsSecretKey);
    }

    public function getBucket()
    {
        return $this->_bucket;
    }

    public function setBucket($bucket)
    {
        $this->_bucket = $bucket;
    }

    public function getS3Url()
    {
        //return self::AMAZONS3_PRE_PATH . $this->_bucket;
        return $this->_s3->getEndpoint() . '/' . $this->_bucket . '/';
    }

    public function listObject()
    {
        $this->_valide();
        return $this->_s3->getObjectsByBucket($this->_bucket);
    }

    public function getObjectInfo($path)//object path
    {
        $this->_valide();
        return $this->_s3->getInfo($this->_bucket . $path);
    }

    public function isObjectExist($path)//object path
    {
        $this->_valide();
        return $this->_s3->isObjectAvailable($this->_bucket . $path);
    }

    public function uploadObject($localFile, $path)
    {
        $this->_valide();
        $result = $this->_s3->putFile($localFile, $this->_bucket. $path,
                        array(Zend_Service_Amazon_S3::S3_ACL_HEADER =>Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
                              'Cache-Control' => 'max-age=31104000'));

        return $result;
    }



    private function _valide()
    {
        if ($this->_s3 == null) {
            throw new Exception('instance not create');
        }
    }
}