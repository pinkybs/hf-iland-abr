<?php

###########CONFIGRATION BEGIN######################

//configure servers
$MONGO = array();
$MONGO["servers"] = array(
	array(
		"host" => "10.245.227.111",//replace your MongoDB host ip or domain name here
		"port" => "27017",//MongoDB port
		"username" => null,//MongoDB username
		"password" => null,//MongoDB password
		"admins" => array( 
			"admin" => "zhangxin", //Administrator's USERNAME => PASSWORD
			//"iwind" => "123456",
		)
	),

	/**array(
		"host" => "192.168.1.5",
		"port" => "27017",
		"username" => null,
		"password" => null,
		"admins" => array( 
			"admin" => "admin"
		)
	),**/
);

###########CONFIGRATION END######################

//default settings, you need not change them in current version
define("__LANG__", "en_us");
define("ROCK_MONGO_VERSION", "1.0.2");
error_reporting(E_ALL | E_STRICT);

session_save_path("tcp://10.194.11.15:11211");

//detect environment
if (!version_compare(PHP_VERSION, "5.0")) {
	exit("To make things right, you must install PHP5");
}
if (!class_exists("Mongo")) {
	exit("To make things right, you must install php_mongo module");
}

//rock roll
require "rock.php";
Rock::start();

?>