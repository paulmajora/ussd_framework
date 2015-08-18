<?php 
	require_once('persistentSession.php');
	
	class ussd{
		private static $SESSION_DATA;
		private static $pSession;
		private static $msisdn;
		function __construct($msisdn){
			self::start($msisdn);
		}
		static function start($msisdn){
			if(!$msisdn){
				return die('Msisdn Is Empty');
			}
			self::$pSession = new PersistentSession($msisdn);
			self::$SESSION_DATA = self::$pSession->getData();
			if(!is_array(self::$SESSION_DATA)){
				self::$SESSION_DATA = array();
			}
			self::setMsisdn($msisdn);
			//var_dump(self::getSession());
		}

		static function getItemSubKey($key,$sub_key = ''){
			$session = self::session();
			if(!isset($session[$key])){
				return null;
			}
			if(!is_array($session[$key])){
				return $session[$key]; 
			}
			if(array_key_exists($sub_key,$session[$key] )){
				return $session[$key][$sub_key];
			}
			
		}

		static function getItem($key){
			$session = self::session();
			if(!isset($session[$key])){
				return null;
			}
			
			return $session[$key]; 
			
		}

		static function getDataItem($key){
			if(!isset(self::session()[self::getMsisdn()][$key])){
				return null;
			}
			return self::session()[self::getMsisdn()][$key]; 
		}

		static function getInputData(){
			return self::getItem(self::getMsisdn());
		}
		static function keyIsset($key){
			return self::getItem($key);
		}
		static function setItem($key , $value){
			self::session()[$key] = $value;
		}
		static function setDataItem($key , $value){
			self::session()[self::getMsisdn()][$key] = $value;
		}
		function lastOf($key){
			if(!is_array($key)){
				return self::getItem($key);
			}
		}
		function hasSession($msisdn){
			return self::keyIsset('msisdn') && (self::getItem('msisdn') == $msisdn) && !empty(self::getItem('msisdn'));
			//&& isset(self::session()['msisdn']['msisdn']);
		}
		static function removeItem($key){
			//var_dump($key);die;
			if(is_array($key)){
				self::removeItems($key);
				return;
			}
			if(!isset(self::session()[$key])){
				return;
			}
			unset(self::session()[$key]);
		}
		static function removeItems($keys){
			if(!is_array($keys)){
				return;
			}
			foreach ($keys as $index => $key) {
				unset(self::session()[$key]);
			}
			
		}
		static  function removeDataItem($key){
			unset(self::session()[self::getMsisdn()][$key]);
		}
		static  function getMsisdn(){
			return self::getItem('msisdn');
		}


		static  function setMsisdn($msisdn){
			self::$msisdn = $msisdn;
			self::setItem('msisdn', $msisdn);
		}
		static function distinct($array){
			return array_unique($array);
		}
		static function push($key,$value){
			if(!self::keyIsset($key)){
				self::setItem($key,array());
			}
			return array_push(self::session()[$key],$value);
		}
		static function pop($key){
			if(!self::keyIsset($key)){
				return null;
			}
			return array_pop(self::session()[$key]);
		}
		static function peek($key){
			if(!self::keyIsset($key)){
				return null;
			}
			return array_pop(self::session()[$key]);
		}
		static function &getSession(){
			return self::$SESSION_DATA;
		}
		static function &session(){
			return self::$SESSION_DATA;
		}

		static function reset(){
			foreach(self::session() as $key => $value){
				unset(self::session()[$key]);
			}
		}
		
		static function clearHistory(){
			self::reset();
			self::save();

		}
		static function clearSession(){
			self::reset();
			self::save();

		}
		static function save(){
			$msisdn = self::getMsisdn();
			self::$pSession->write($msisdn,self::session());
		}
		static function destroy(){
			$msisdn = self::getMsisdn();
			$privateData = self::getItem($msisdn);
			self::$pSession->destroy();
			self::start($msisdn);
			//self::setItem(self::$msisdn,$privateData);//reset
		}
	}

?>