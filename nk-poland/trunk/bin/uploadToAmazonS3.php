<?php

define('ROOT_DIR', realpath('../'));

require(ROOT_DIR . '/bin/config.php');

try {
    ///usr/local/php-cgi/bin/php -f /home/admin/website/island_nk_poland/bin/uploadToAmazonS3.php /home/admin/website/teststatic/www/nkpl/
    //sh :execfilepath :execpath :phppath :streamout
    //$srcPath = '/home/admin/website/teststatic/www/nkpl/';
    $srcPath = $argv[1];
    $bucketBasePath = '/test/';
    echo "\nAmazon Upload Starting...";
	echo "\nstatic_src_path:" . $argv[1];

	//init s3 bucket
	$s3 = new Hapyfish2_Util_AmazonS3('AKIAJKGHB2I4SWIO3WZA', 'qFjtn+i2sPWDZ8176O+RV2NRWnndSF8dTTxsLiOV');
    $s3->setBucket('nkisland');
    //get s3 bucket object list
    $list = $s3->listObject();
    /*foreach ($list as $object) {
        echo "\n/$object";
    }*/

    if (!is_dir($srcPath)) {
        echo "\nPath:$srcPath read failed!!! please check your pass argv[1]. \n";
        exit;
    }
    $aryFile = array();
    ergodDir($srcPath, $aryFile);

    $cntUp = 0;
    foreach ($aryFile as $locFile) {
        //echo "\n".str_replace($srcPath, '/', $locFile);
        $objectPath = str_replace($srcPath, '', $locFile);
        if (!in_array($objectPath, $list)) {
            //upload file
            echo "\nUploading $objectPath ==> ";
            $rst = $s3->uploadObject($locFile, '/'.$objectPath);
            if ($rst) {
                $cntUp ++;
                echo 'Done!';
            }
            else {
                echo 'Failed!';
            }
        }
    }

    echo "\nAmazone Upload has been Done!!! $cntUp files upload!!\n\nPress Enter key to continue.";
}
catch (Exception $e) {
	err_log($e->getMessage());
}


function ergodDir($path, &$aryFile)
{
    $handle = opendir($path);
    if ($handle) {
        //ergod static file and dir
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                if (is_dir($path.$file)) {
                    //echo "\ndir:$path$file";
                    ergodDir($path.$file.'/', $aryFile);
                }
                else {
                    //echo "\n$path$file";
                    $aryFile[] = $path.$file;
                }
            }
        }
        closedir($handle);
    }
}