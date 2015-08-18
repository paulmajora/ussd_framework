<?php

	
	$db = "CREATE DATABASE IF NOT EXISTS ".DB;
	$table = "CREATE TABLE IF NOT EXISTS session ( 
				    session_id VARCHAR(32) NOT NULL, 
				    session_data TEXT NOT NULL, 
				    session_lastaccesstime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
				    PRIMARY KEY (session_id)
				);";
	
	createDB($db);
	createTable($table);
	define('DSN', 'mysql:host=localhost;dbname='.DB.';charset=utf8');
	function createDB($sql){
		$db = new PDO("mysql:host=localhost;", USER, PASS);
		$stmt = $db->query($sql);
		@File_IO::saveData('logs/session_db_log.txt','Database Created Successfully');
		return $stmt;
	}
	
	function createTable($sql){
		$db = new PDO("mysql:host=localhost;dbname=".DB, USER, PASS);
		$stmt = $db->query($sql);
		@File_IO::saveData('logs/session_db_log.txt','Table Created Successfully');
		return $stmt;
	}
	
	class PersistentSession {
	protected $sessionId;
	protected $sessionData;
	function __construct($sessionId){
		$this->sessionData = array();
		$this->sessionId = $sessionId;
		$this->openOrCreate();
	}

	function getId(){
		return $this->sessionId;
	}
	function getData(){
		return $this->sessionData;
	}
	function __debug($msg){
		@File_IO::appendData('logs/session_log.txt',$msg);
	}
	function __debug_error($msg){
		@File_IO::saveData('logs/db_error_log.txt',$msg);
	}
	function query($sql){
		try {
			$this->__debug($sql);
			$db = database::get_instance(DSN,USER,PASS);
			$stmt = $db->prepare($sql);
			$ret = $stmt->execute();
			return $stmt->fetch();
		} catch (PDOException $e) {
			echo $e->getMessage()."<br>query sql : $sql";
			$this->__debug_error($e->getMessage()."<br>query sql : $sql");
		}	
	}
	function execute($sql){
		try {
			$this->__debug($sql);
			$db = database::get_instance();
			$stmt = $db->prepare($sql);
			$ret = $stmt->execute();
			return $ret;
		} catch (PDOException $e) {
			$this->__debug_error($e->getMessage()."<br>execute sql : $sql");
		}	
	}
	function encode($what){
		return json_encode($what);
		//return serialize($what);
	}
	function decode($what){
		return json_decode($what,true);
		//return unserialize($what);
	}
	function openOrCreate() {
		if(!$this->idExists()){
	    	return $this->open();  
		}
		return $this->read();
	     
	}
	function open() {
			$sql = "INSERT INTO session SET session_id =" . $this->quote($this->getId()) . ", session_data = '' ON DUPLICATE KEY UPDATE session_lastaccesstime = NOW()";
			//$sql = "INSERT INTO session (session_id ,session_data) VALUES (".$this->quote($this->getId()).",'')";
	    	return $this->execute($sql);  
	     
	}
	function idExists(){
		$sql = "SELECT session_data FROM session where session_id =" . $this->quote($this->getId());
	    return count($this->query($sql));
	}
	function read() { 
	    $sql = "SELECT session_data FROM session where session_id =" . $this->quote($this->getId());
	    $data = $this->query($sql);
	   
	    // echo "<pre>";
	   	// print_r($data['session_data']);
	    // echo "<pre>";
	    if($data){
	    	 $this->sessionData =  $this->decode($data['session_data']);
	    }

	    if(!is_array($this->sessionData)){
	    	 $this->sessionData =  array();
	    }

	    // echo "<pre>";
	   	// print_r($this->$sessionData);
	    // echo "<pre>";
	    return $this->sessionData;
	   	
	}
	function quote($what){
		$db = database::get_instance();
		return $db->quote($what);
	}
	function write($sessionId,$data) { 
		$data = $this->encode($data);
	    $sql = "INSERT INTO session SET session_id =" . $this->quote($sessionId) . ", session_data =" . $this->quote($data) . " ON DUPLICATE KEY UPDATE session_data =" . $this->quote($data);
	    //$sql = "UPDATE session SET session_data =" . $this->quote($data)." WHERE session_id=".$this->quote($this->getId());
	    return $this->execute($sql);
	}
	function destroy() {
	    $sql = "DELETE FROM session WHERE session_id =" .$this->quote($this->getId()); 
	   return $this->execute($sql);
	}
	}
?>