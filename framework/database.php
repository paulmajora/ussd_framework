<?php
/*
	Class to handle database connections
*/
class database{
	private static $instance = null;
	private $dbh = null;
	private function __construct($dsn=null,$user = null,$pass = null){
		try {
			if(!$dsn){
				$dsn = 'mysql:host=localhost;dbname=;charset=utf8';
			}
			if(!$user){
				$user = 'root';
			}
			if(!$pass){
				$pass = 'passwd';
			}
			$this->dbh = new PDO($dsn, $user,$pass);	
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo $e->getMessage();
			$this->dbh = NULL;
		}
		return $this->dbh;

	}

	public static function get_instance($dsn = null,$user = null , $pass = null){
		if(null == self::$instance || $dsn != null){
			self::$instance = new database($dsn,$user,$pass);
		}
		//var_dump(self::$instance->dbh);
		return self::$instance->dbh;
	}

	/*
		Connection cannot be cloned
	*/
	private function __clone(){}

	/*
		Connection cannot be serialized
	*/
	private function __wakeup(){}

}

?>