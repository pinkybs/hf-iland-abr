<?php
session_start();

import("lib.ext.RExtController");
class IndexController extends RExtController {
	private $_servers;
	private $_server;
	/**
	 * Enter description here...
	 *
	 * @var Mongo
	 */
	private $_mongo;
	private $_admin;//administrator's name
	
	function onBefore() {
		global $MONGO;
		$this->_servers = $MONGO["servers"];
		
		if ($this->action() != "login") {
			//是否已经登录
			if (!isset($_SESSION["login"])) {
				$this->redirect("login");
			}
			$this->_admin = $_SESSION["login"]["username"];
			$server = $this->_servers[$_SESSION["login"]["host"]];
			$this->_server = $server;
			$link = "mongodb://";
			if ($server["username"]) {
				$link .= $server["username"] . ":" . $server["password"] . "@";
			}
			$link .= $server["host"] . ":" . $server["port"];
			$this->_mongo = new Mongo($link);
		}
		
		if ($this->action() != "admin") {
			$this->display("header");
		}
	}
	
	function onAfter() {
		if ($this->action() != "admin") {
			$this->display("footer");
		}
	}
	
	/** home **/
	function doIndex() {
		$this->redirect("admin");
	}
	
	/** login page and post **/
	function doLogin() {
		$this->servers = $this->_servers;
		$this->username = x("username");
		
		if ($this->isPost()) {
			$index = xi("host");
			if (!isset($this->_servers[$index])) {
				$this->message = "invalid server";
				$this->display();
				return;
			}
			$server = $this->_servers[$index];
			if (!isset($server["admins"][$this->username])) {
				$this->message = "Wrong username or password";
				$this->display();
				return;
			}
	
			$password = $server["admins"][$this->username];
			if ($password != x("password")) {
				$this->message = "Wrong username or password";
				$this->display();
				return;
			}
			
			//remember user
			$_SESSION["login"] = array(
				"username" => $this->username,
				"host" => $index
			);
		
			//jump to admin page
			$this->redirect("admin");
		}
		else {
			$this->display();
		}
	}
	
	/** log out from system **/
	function doLogout() {
		session_destroy();
		$this->redirect("login");
	}
	
	/** admin page **/
	function doAdmin() {
		$this->topUrl = $this->path("top");
		$this->leftUrl = $this->path("dbs");
		$this->rightUrl = $this->path("server");
		
		$this->display();
	}
	
	/** top frame **/
	function doTop() {
		$this->logoutUrl = $this->path("logout");
		$this->server = $this->_server;
		$this->admin = $this->_admin; 
		$this->display();
	}
	
	/** about project and us **/
	function doAbout() {
		$this->display();
	}
	
	/** show dbs in left frame **/
	function doDbs() {
		$dbs = $this->_mongo->listDBs();
		$this->dbs = array_values(rock_array_sort($dbs["databases"], "name"));
		$this->baseUrl = $this->path("dbs");
		$this->tableUrl = $this->path("collection");
		
		//add collection count
		foreach ($this->dbs as $index => $db) {
			$collectionCount = count($this->_mongo->selectDB($db["name"])->listCollections());
			$db["collectionCount"] = $collectionCount;
			$db["size"] = round($db["sizeOnDisk"]/1024/1024, 2);//M
			$this->dbs[$index] = $db;
		}

		//current db		
		$db = x("db");
		if (!empty($this->dbs) && !$db) {
			$db = $this->dbs[0]["name"];
			x("db", $db);
		}
		
		$this->tables = array();
		if ($db) {
			$tables = $this->_mongo->selectDB($db)->listCollections();
			foreach ($tables as $table) {
				$this->tables[] = $table->getName();	
			}
			sort($this->tables);
		}
		
		$this->display();
	}
	
	/** server infomation **/
	function doServer() {
		//web server
		$this->webServers = array();
		if (isset($_SERVER["SERVER_SOFTWARE"])) {
			list($webServer) = explode(" ", $_SERVER["SERVER_SOFTWARE"]);
			$this->webServers["Web server"] = $webServer;
		}
		$this->webServers["PHP version"] = "PHP " . PHP_VERSION;
		$this->webServers["PHP extension"] = "mongo/" . Mongo::VERSION;
			
		$this->directives = ini_get_all("mongo");
		
		//build info
		$db = $this->_mongo->selectDB("admin");
		$ret = $db->command(array("buildinfo" => 1));
		$this->buildInfos = array();
		if ($ret["ok"]) {
			unset($ret["ok"]);
			$this->buildInfos = $ret;
		}
		
		//connection
		$this->connections = array(
			"Host" => $this->_server["host"],
			"Port" => $this->_server["port"],
			"Username" => "******",
			"Password" => "******"
		);
		
		$this->display();
	}
	
	/** Server Status **/
	function doStatus() {
		//status
		$db = $this->_mongo->selectDB("admin");
		$ret = $db->command(array("serverStatus" => 1));
		$this->status = array();
		if ($ret["ok"]) {
			unset($ret["ok"]);
			$this->status = $ret;
			foreach ($this->status as $index => $_status) {
				$json = $this->_highlight($_status, "json");
				if ($index == "uptime") {//we convert it to days
					$json .= " (" . ceil($_status/86400) . "days)";
				}
				$this->status[$index] =  $json;
			}
		}
		
		$this->display();
	}
	
	/** show databases **/
	function doDatabases() {
		$ret = $this->_mongo->listDBs();
		$this->dbs = $ret["databases"];
		foreach ($this->dbs as $index => $db) {
			$mongodb = $this->_mongo->selectDB($db["name"]);
			$ret = $mongodb->command(array("dbstats" => 1));
			$ret["collections"] = count($mongodb->listCollections());//do not contain indexes
			$ret["diskSize"] = $this->_formatBytes($db["sizeOnDisk"]);
			$ret["dataSize"] = $this->_formatBytes($ret["dataSize"]);
			$ret["storageSize"] = $this->_formatBytes($ret["storageSize"]);
			$ret["indexSize"] = $this->_formatBytes($ret["indexSize"]);
			$this->dbs[$index] = array_merge($this->dbs[$index], $ret);
		}
		$this->dbs = rock_array_sort($this->dbs, "name");
		$this->display();
	}
	
	/** execute command **/
	function doCommand() {
		$ret = $this->_mongo->listDBs();
		$this->dbs = $ret["databases"]; 
		
		if (!$this->isPost()) {
			x("command", json_format("{assertinfo:1}"));
			if (!x("db")) {
				x("db", "admin");
			}
		}
		
		if ($this->isPost()) {
			$command = xn("command");
			$format = x("format");
			if ($format == "json") {
				$command = 	$this->_decodeJson($command);
			}
			else {
				eval("\$command=" . $command . ";");
			}
			if (!is_array($command)) {
				$this->message = "You should send a valid command";
				$this->display();
				return;
			}
			$this->ret = $this->_highlight($this->_mongo->selectDB(xn("db"))->command($command), $format);
		}
		$this->display();
	}
	
	/** execute code **/
	function doExecute() {
		$ret = $this->_mongo->listDBs();
		$this->dbs = $ret["databases"]; 
		if (!$this->isPost()) {
			if (!x("db")) {
				x("db", "admin");
			}
			x("code", 'function () {
   return "Hello,World";
}');
		}
		if ($this->isPost()) {
			$code = trim(xn("code"));
			$arguments = xn("argument");
			if (!is_array($arguments)) {
				$arguments = array();
			}
			else {
				$this->arguments = $arguments;
				foreach ($arguments as $index => $argument) {
					$argument = trim($argument);
					$array = $this->_decodeJson($argument);
					$arguments[$index] = $array;
				}
			}
			$ret = $this->_mongo->selectDB(xn("db"))->execute($code, $arguments);
			$this->ret = $this->_highlight($ret, "json");
		}
 		$this->display();
	}
	
	/** create databse **/
	function doCreateDatabase() {
		if ($this->isPost()) {
			$name = trim(xn("name"));
			if (empty($name)) {
				$this->error = "Please input a valid database name.";
				$this->display();
				return;
			}
			$this->message = "New database created.";
			$this->_mongo->selectDb($name)->execute("function(){}");
		}
		$this->display();
	}
	
	/** database **/
	function doDb() {
		$this->db = trim(xn("db"));
		
		$dbs = $this->_mongo->listDBs();
		$ret = array();
		foreach ($dbs["databases"] as $db) {
			if ($db["name"] == $this->db) {
				$ret = $db;
			}
		}
		
		//collections
		$db = $this->_mongo->selectDB($this->db);
		$collections = $db->listCollections();
		
		$ret = array_merge($ret, $db->command(array("dbstats" => 1)));
		$ret["diskSize"] = $this->_formatBytes($ret["sizeOnDisk"]);
		$ret["dataSize"] = $this->_formatBytes($ret["dataSize"]);
		$ret["storageSize"] = $this->_formatBytes($ret["storageSize"]);
		$ret["indexSize"] = $this->_formatBytes($ret["indexSize"]);
		
		$this->stats = array();
		$this->stats["Size"] = $ret["diskSize"];
		$this->stats["Is Empty?"] = $ret["empty"] ? "Yes" : "No";
		$this->stats["Collections"] = count($collections) . " collections:";
		if (empty($collections)) {
			$this->stats["Collections"] .= "<br/>No collections yet";
		}
		else {
			foreach ($collections as $collection) {
				$this->stats["Collections"] .= "<br/><a href=\"" 
					. $this->path("collection", array( "db" => $this->db, "collection" => $collection->getName())) . "\">" . $collection->getName() . "</a>";
			}
		}
		$this->stats["Objects"] = $ret["objects"];
		$this->stats["Data Size"] = $ret["dataSize"];
		$this->stats["Storage Size"] = $ret["storageSize"];
		$this->stats["Extents"] = $ret["numExtents"];
		$this->stats["Indexes"] = $ret["indexes"];
		$this->stats["Index Size"] = $ret["indexSize"];
		
		
		
		
		$this->display();
	}
	
	/** drop database **/
	function doDropDatabase() {
		$this->db = xn("db");
		
		if (!x("confirm")) {
			$this->display();
			return;
		}
		
		$ret = $this->_mongo->dropDB($this->db);
		$this->ret = $this->_highlight($ret, "json");
		$this->display("dropDatabaseResult");
	}
	
	/** show one collection **/
	function doCollection() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		//field and sort
		$fields = xn("field");
		$orders = xn("order");
		if (empty($fields)) {
			$fields = array(
				"_id", "", "", ""
			);
			$orders = array(
				"desc", "asc", "asc", "asc"
			);
			x("field", $fields);
			x("order", $orders);
		}
		
		
		//conditions
		$criteria = xn("criteria");
		if (empty($criteria)) {
			$criteria = array();
		}
		else {
			$row = null;
			eval("\$row={$criteria};");
			if (!is_array($row)) {
				$this->message = "Criteria must be an array.";
				$this->display();
				return;
			}
			$criteria = $row;
		}
		x("criteria", var_export($criteria, true));
		
		
		import("lib.mongo.RQuery");
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$query->cond($criteria);
		
		//sort
		foreach ($fields as $index => $field) {
			if (!empty($field)) {
				if ($orders[$index] == "asc") {
					$query->asc($field);
				}
				else {
					$query->desc($field);
				}
			}
		}
		
		//command
		$command = x("command");
		if (!$command) {
			$command = "findAll";
			x("command", $command);
		}
		$limit = xi("limit");
		if ($limit > 0) {
			$query->limit($limit);
		}
		$count = ($limit > 0 && $command == "findAll") ? $query->count(true) : $query->count();
		
		switch ($command) {
			case "findAll":
				break;
			case "remove":
				$microtime = microtime(true);	
				$query->delete();
				$this->cost = microtime(true) - $microtime;
				break;
			case "modify":
				$microtime = microtime(true);	
				$row = null;
				$newobj = xn("newobj");
				eval("\$row={$newobj};");
				if (is_array($row)) {
					$query->upsert($row);
				}
				$this->cost = microtime(true) - $microtime;
				break;
		}
		
		if ($command != "findAll") {
			$this->count = $count;
			$this->display();
			return;
		}
		
		//pagination
		$pagesize = xi("pagesize");
		if ($pagesize < 1) {
			$pagesize = 10;
		}
		import("lib.page.RPageStyle1");
		$page = new RPageStyle1();
		$page->setTotal($count);
		$page->setSize($pagesize);
		$page->setAutoQuery();

		$this->page = $page;
		$query->offset($page->offset());
		if ($limit > 0) {
			$query->limit(min($limit, $page->size()));
		}
		else {
			$query->limit($page->size());
		}
		
		//format
		$format = x("format");
		if (!$format) {
			$format = "json";
		}
		$params = xn();
		$params["format"] = "array";
		$this->arrayLink = $this->path($this->action(), $params);
		$params["format"] = "json";
		$this->jsonLink = $this->path($this->action(), $params);
		
		$microtime = microtime(true);	
		$this->rows = $query->findAll();
		$this->cost = microtime(true) - $microtime;
		foreach ($this->rows as $index => $row) {
			$native = $row;
			$row["data"] = $this->_highlight($native, $format);
			$row["text"] = ($format == "array") ? var_export($native, true) : json_format($this->_encodeJson($native));
			$this->rows[$index] = $row;
		}
		
		$this->display();
	}
	
	function doDeleteRow() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		import("lib.mongo.RQuery");
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$query->id(x("id"))->delete();
		
		$this->redirectUrl(xn("uri"));
	}
	
	function doCreateRow() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		$id = x("id");
		
		import("lib.mongo.RQuery");		
		
		//if is duplicating ...
		if (!$this->isPost() && $id) {
			$query = new RQuery($this->_mongo, $this->db, $this->collection);
			$row = $query->id($id)->findOne();
			if (!empty($row)) {
				unset($row["_id"]);
				x("data", var_export($row, true));
			}
		}
		if (!$this->isPost() && !x("data")) {
			x("data", "array(\n\n)");
		}
		
		if ($this->isPost()) {
			$data = xn("data") . ";";
			$data = str_replace(array(
				"%{created_at}"
			), time(), $data);
			$row = null;
			if (@eval("\$row=" . $data . ";") === false || !is_array($row)) {
				$this->message = "Only array is accepted.";
				$this->display();
				return;
			}
			
			$query = new RQuery($this->_mongo, $this->db, $this->collection);
			$ret = $query->insert($row);
			$this->redirect("collection", array( 
				"db" => $this->db,
				"collection" => $this->collection 
			));
		}
		
		$this->display();
	}
	
	function doModifyRow() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		import("lib.mongo.RQuery");	
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$this->row = $query->id(x("id"))->findOne();
		if (empty($this->row)) {
			$this->message = "Record is not found.";
			$this->display();
			return;
		}
		$this->data = $this->row;
		unset($this->data["_id"]);
		$this->data = var_export($this->data, true);
	
		if ($this->isPost()) {
			$this->data = xn("data");
			$row = null;
			if (@eval("\$row=" . $this->data . ";") === false || !is_array($row)) {
				$this->message = "Only array is accepted.";
				$this->display();
				return;
			}
			
			$query = new RQuery($this->_mongo, $this->db, $this->collection);
			$obj = $query->id($this->row["_id"])->find();
			$oldAttrs = $obj->attrs();
			$obj->setAttrs($row);
			foreach ($oldAttrs as $oldAttr => $oldValue) {
				if ($oldAttr == "_id") {
					continue;
				}
				if (!isset($row[$oldAttr])) {
					$obj->remove($oldAttr);
				}
			}
			$obj->save();
			
			$this->message = "Updated successfully.";
		}
		
		$this->display();
	}
	
	function doClearRows() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		import("lib.mongo.RQuery");	
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$query->delete();
		
		$this->redirect("collection", array(
			"db" => $this->db,
			"collection" => $this->collection
		));
	}
	
	function doRemoveCollection() {
		$this->db = x("db");
		$this->collection = x("collection");
		$db = new MongoDB($this->_mongo, $this->db);
		$db->dropCollection($this->collection);
		$this->display();
	}
	
	function doNewCollection() {
		$this->db = xn("db");
		$this->name = x("name");
		$this->isCapped = xi("is_capped");
		$this->size = xi("size");
		$this->max = xi("max");
		
		if ($this->isPost()) {
			$db = new MongoDB($this->_mongo, $this->db);
			$db->createCollection($this->name, $this->isCapped, $this->size, $this->max);
			$this->message = "New collection is created.";
		}
		
		$this->display();
	}
	
	function doCollectionIndexes() {
		$this->db = x("db");
		$this->collection = x("collection");
		$collection = $this->_mongo->selectCollection(new MongoDB($this->_mongo, $this->db), $this->collection);
		$this->indexes = $collection->getIndexInfo();
		foreach ($this->indexes as $_index => $index) {
			$index["data"] = $this->_highlight($index["key"]);
			$this->indexes[$_index] = $index;
		}
		$this->display();
	}
	
	function doDeleteIndex() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		$db = new MongoDB($this->_mongo, $this->db);
		$collection = $this->_mongo->selectCollection($db, $this->collection);
		$indexes = $collection->getIndexInfo();
		foreach ($indexes as $index) {
			if ($index["name"] == trim(xn("index"))) {
				$ret = $db->command(array("deleteIndexes" => $collection->getName(), "index" => $index["name"]));
				break;
			}
		}
		
		$this->redirect("collectionIndexes", array(
			"db" => $this->db,
			"collection" => $this->collection
		));
	}
	
	function doCreateIndex() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		if ($this->isPost()) {
			$db = new MongoDB($this->_mongo, $this->db);
			$collection = $this->_mongo->selectCollection($db, $this->collection);
			
			$fields = xn("field");
			if (!is_array($fields)) {
				$this->message = "Index contains one field at least.";
				$this->display();
				return;
			}
			$orders = xn("order");
			$attrs = array();
			foreach ($fields as $index => $field) {
				$field = trim($field);
				if (!empty($field)) {
					$attrs[$field] = ($orders[$index] == "asc") ? 1 : -1;
				}
			}
			if (empty($attrs)) {
				$this->message = "Index contains one field at least.";
				$this->display();
				return;
			}
			
			//if is unique
			$options = array();
			if (x("is_unique")) {
				$options["unique"] = 1;
				if (x("drop_duplicate")) {
					$options["dropDups"] = 1;
				}
			}
			$options["background"] = 1;
			$options["safe"] = 1;
			
			//name
			$name = trim(xn("name"));
			if (!empty($name)) {
				$options["name"] = $name;
			}
			
			$collection->ensureIndex($attrs, $options);
			
			$this->redirect("collectionIndexes", array(
				"db" => $this->db,
				"collection" => $this->collection
			));
		}
		
		$this->display();
	}
	
	function doCollectionStats() {
		$this->db = x("db");
		$this->collection = x("collection");
		$this->stats = array();
		
		$db = new MongoDB($this->_mongo, $this->db);
		$ret = $db->execute("db.{$this->collection}.stats()");
		if ($ret["ok"]) {
			$this->stats = $ret["retval"];
		}
		
		$this->display();
	}
	
	private function _encodeJson($var) {
		if (function_exists("json_encode")) {
			return json_encode($var);
		}
		import("classes.Services_JSON");
		$service = new Services_JSON();
		return $service->encode($var);
	}
	
	private function _decodeJson($var) {
		import("classes.Services_JSON");
		$service = new Services_JSON();
		$ret = array();
		$decode = $service->decode($var);
		if (!is_object($decode)) {
			return $decode;
		}
		foreach ($decode as $key => $value) {
			$ret[$key] = $value;
		}
		return $ret;
	}	
	
	private function _highlight($var, $format = "array") {
		$string = null;
		if ($format == "array") {
			$string = highlight_string("<?php " . var_export($var, true), true);
			$string = preg_replace("/" . preg_quote('<span style="color: #0000BB">&lt;?php&nbsp;</span>', "/") . "/", '', $string, 1);
		}
		else {
			$string =  json_format_html($this->_encodeJson($var));
		}
		return $string;
	}
	
	/** 
	 * format bytes to human size 
	 * 
	 * @param integer $bytes size in byte
	 * @return string size in k, m, g..
	 **/
	private function _formatBytes($bytes) {
		if ($bytes < 1024) {
			return $bytes;
		}
		if ($bytes < 1024 * 1024) {
			return round($bytes/1024, 2) . "k";
		}
		if ($bytes < 1024 * 1024 * 1024) {
			return round($bytes/1024/1024, 2) . "m";
		}
		if ($bytes < 1024 * 1024 * 1024 * 1024) {
			return round($bytes/1024/1024/1024, 2) . "g";
		}
		return $bytes;
	}
}


/**
 * 将一个多维数组按照一个键的值排序
 *
 * @param array $array 数组
 * @param mixed $key string|array
 * @param boolean $asc 是否正排序
 * @return array
 */
function rock_array_sort(array $array, $key = null, $asc = true) {
	if (empty($array)) {
		return $array;
	}
	if (empty($key)) {
		$asc ? asort($array) : arsort($array);
	}
	else {
		$GLOBALS["ROCK_ARRAY_SORT_KEY_" . nil] = $key;
		uasort($array, 
			$asc ? create_function('$p1,$p2', '$key=$GLOBALS["ROCK_ARRAY_SORT_KEY_" . nil];$p1=rock_array_get($p1,$key);$p2=rock_array_get($p2,$key);if ($p1>$p2){return 1;}elseif($p1==$p2){return 0;}else{return -1;}')
			:
			create_function('$p1,$p2', '$key=$GLOBALS["rock_ARRAY_SORT_KEY_" . nil];$p1=rock_array_get($p1,$key);$p2=rock_array_get($p2,$key);if ($p1<$p2){return 1;}elseif($p1==$p2){return 0;}else{return -1;}')
		);
		unset($GLOBALS["ROCK_ARRAY_SORT_KEY_" . nil]);
	}	
	return $array;
}


/**
 * PHP Integration of Open Flash Chart
 * Copyright (C) 2008 John Glazebrook <open-flash-chart@teethgrinder.co.uk>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Pretty print some JSON
//modified by iwind
function json_format_html($json)
{
    $tab = "&nbsp;&nbsp;";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;

/*
 commented out by monk.e.boy 22nd May '08
 because my web server is PHP4, and
 json_* are PHP5 functions...

    $json_obj = json_decode($json);

    if($json_obj === false)
        return false;

    $json = json_encode($json_obj);
*/
    $len = strlen($json);

    for($c = 0; $c < $len; $c++)
    {
        $char = $json[$c];
        switch($char)
        {
            case '{':
            case '[':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string)
                {
                    $new_json .= $char . "<br/>" . str_repeat($tab, $indent_level+1);
                    $indent_level++;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '}':
            case ']':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string)
                {
                    $indent_level--;
                    $new_json .= "<br/>" . str_repeat($tab, $indent_level) . $char;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ',':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string)
                {
                    $new_json .= ",<br/>" . str_repeat($tab, $indent_level);
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ':':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string)
                {
                    $new_json .= ": ";
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '"':
                if($c > 0 && $json[$c-1] != '\\')
                {
                    $in_string = !$in_string;
                    $new_json .= $in_string ? "<font color=\"red\">" . $char: $char . "</font>"; //iwind
                    break;//iwind
                }
            default:
            	if (!$in_string) {
            		$char = "<font color=\"blue\">" . $char . "</font>";
            	}
                $new_json .= $char;
                break;
        }
    }

    return $new_json;
}


function json_format($json)
{
    $tab = "  ";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;

/*
 commented out by monk.e.boy 22nd May '08
 because my web server is PHP4, and
 json_* are PHP5 functions...

    $json_obj = json_decode($json);

    if($json_obj === false)
        return false;

    $json = json_encode($json_obj);
*/
    $len = strlen($json);

    for($c = 0; $c < $len; $c++)
    {
        $char = $json[$c];
        switch($char)
        {
            case '{':
            case '[':
                if(!$in_string)
                {
                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
                    $indent_level++;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '}':
            case ']':
                if(!$in_string)
                {
                    $indent_level--;
                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ',':
                if(!$in_string)
                {
                    $new_json .= ",\n" . str_repeat($tab, $indent_level);
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ':':
                if(!$in_string)
                {
                    $new_json .= ": ";
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '"':
                if($c > 0 && $json[$c-1] != '\\')
                {
                    $in_string = !$in_string;
                }
            default:
                $new_json .= $char;
                break;
        }
    }

    return $new_json;
}

?>