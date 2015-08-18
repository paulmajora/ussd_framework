<?php

	class nav{
		private static $menu;
		private static $pages;
		private static $session;
		private static $conf;
		private static $endpoint;
		function __construct(&$session , $pages,$conf = array()){
			self::$session = $session;
			self::$pages = $pages;
			if(!$conf){
				$conf = self::default_conf();
			}

			//always override this option
			//for smooth navigation
			$conf['strict_navigation'] = 1; //please 
			self::$conf = $conf;
		}
		static function config(&$session , $pages){
			self::$session = $session;
			self::$pages = $pages;

		}
		// static function pause(){
		// 	return self::$session->save();
		// }
		static function default_conf(){
		$conf = array(
			'short_code' => array(),
			'root_menu_chars' => array('0#'),
			'clear_session_chars' => array(''),
			'back_chars' => array('*','#'),
			//
			'strict_navigation' => 1,

			//user db settings
			'db_host' => 'localhost',
			'db_name' => 'ussd_session',
			'db_user' => 'root',
			'db_password' => 'passwd',
			//serialize and deserialize session to/from db
			'persist_sessions' => true,

			//debugging
			'debug' => true,
			'debug_session' => !false,
			'debug_menu' => !false
		);
			return $conf;
		}
	static function display($menus,$options = array()){
		self::set_display_action();
		self::set_last_menu($menus);
		if(isset($options['start']) || isset($options['limit'])){
			$choiceRange = array(
				'start' => @$options['start'],
				'limit' => @$options['limit']
				);
			self::set_choice_range($choiceRange);
		}else{
			self::clear_choice_range();
		}
		
		self::display_menu($menus);
		if(isset(self::$conf['debug_menu']) && self::$conf['debug_menu']){
			self::debug($menus);
		}
		if(!in_array('no-exit', $options)){
			die();
		}
	}

	static function await_input($key,$validate,$title,$options = array()){
		self::set_input_action($key,$validate,$title);
		self::display_input($title);

		if(!in_array('no-exit', $options)){
			die();
		}
		
	}


	static function expect_input($key){
		self::set_input_action($key);
	}
	private static function send_response_to_gateway($action,$menus = array(),$title = ""){
		$res = array(
				"USSDResp" => 
					array(
						"action" => $action,
						"title" => $title,
						"menus" => $menus
						),
					'APPDATA' => array(
						'key0' => 'value0',
						'key1' => 'value1',
						'key2' => 'value2',
						'key3' => 'value3',
						'key4' => 'value4',
						'key5' => 'value5',
						'key6' => 'value6',
						)
					);
		//header('Content-Type: text/html, encoding = utf-8, Cache-Control: no-cache, must-revalidate');
		header('Content-Type: application/json');
		self::debug_session();
		echo json_encode($res);
		self::debug_session();
		self::pause();
	}
	static function display_menu($menus){
		$title = $menus[0];
		unset($menus[0]);

		self::send_response_to_gateway('showMenu',$menus,$title);
	}

	static function display_prompt($msg , $options = array()){
		if(!in_array('not-resumable', $options)){
			self::set_action('prompt',null,null,'I am Just Prompting You');
		}
		
		$prompt = '';
		if(is_array($msg)){
			$prompt =  implode(PHP_EOL, $msg);
		}else{
			$prompt =  $msg;
		}
		self::send_response_to_gateway('prompt',$prompt);

		if(!in_array('no-exit',$options)){
			die();
		}
	}
	static function display_input($menus,$options = array()){
		$title = '';
		if(is_array($menus)){
			$title = implode(PHP_EOL, $menus);
		}else{
			$title = $menus;
		}

		self::send_response_to_gateway('input',array(),$title);

		if(!in_array('no-exit', $options)){
			die();
		}
	}

	static function display_repeat_choice($menus){
		self::set_action('invalid_choice',null,null,'Repeat Selection Please');
		self::display_menu($menus);
	}

	static function display_repeat_input($menus){
		self::set_action('invalid_input',null,null,'Invalid Input ,Please Try Again');
		self::display_input($menus);
	}
	
	static function getData(){
		return trim(@$_REQUEST['msg']);
	}
	static function getMsisdn(){
		return trim(@$_REQUEST['msisdn']);
	}

	static function debug($msg,$override = false){
		File_IO::appendData('log.txt',$msg);
		if(!self::$conf['debug'] && !$override){
			return;
		}
		if(is_array($msg)){
			echo "<pre>";
			print_r($msg);
			echo "</pre>";
		}else{
			//echo $msg.PHP_EOL."<br/>";
		}
	}
	static function &session(){
		global $session;
		return $session;
	}
	static function debug_session(){
		if(@self::$conf['debug_session']){
			self::debug(self::$session->getSession(),true);
		}
		
	}
	static function init($msisdn){
		
		$started = self::$session->hasSession($msisdn);
		

		// if(!preview_data($msisdn,$started)){
		// 	pause() ;
		// }
		self::preview_data2($msisdn,$started);
		// if($started){
		// 	display_prompt('Your session Is Active');
		// }else{
		// 	display_prompt('Your session Was destroyed');
		// }
		// return pause();
		
		if(!$started ){
			self::debug('Starting New Session : '.$msisdn);
			self::start($msisdn);
			
			return ;
		}
		self::debug("Session Already Started : ".$msisdn);
		self::continue_operation();
		
		//debug(session()->getSession());pause
	}
	static function start($msisdn){
		self::$session->destroy();
		self::$session->start($msisdn);
		self::set_action('awaiting_choice',null,null,'Started New Session');
		self::continue_operation();

		
	}

	static function what(){
		self::display_prompt("What Do You Mean?");
	}
	static function next_page(){
		$next = self::current_page() + 1;  
		self::set_page($next);
		self::debug('next_page : '.$next);
		return $next;
	}

	static function init_pages(){
		self::debug('init_pages');
		return self::$session->setItem('page',-1);
	}
	static function get_pages(){
		return self::$pages;
	}
	static function current_page(){
		$page = self::$session->getItem('page');
		self::debug('current_page : '.$page);
		
		if($page < 0 || !$page || is_null($page)){
			$page = 0;
			self::debug('current_page adjusted to : '.$page);
		}
		return  $page;
	}

	static function current_choice(){
		$choice = self::$session->getItem('choice');
		return  $choice;
	}
	static function peep_next(){
		self::debug('peep_next');
		$next = current_page() + 1; 
		return $next;
	}
	static function page_exists($page){
		$pages = self::get_pages();
		return array_key_exists($page, $pages);
	}

	static function record_history($page,$choice,$history_option = 3,$data = ""){
		
		if(self::$session->keyIsset('history')){
			$cur_page = self::$session->getItem('page');
			$cur_choice = self::$session->getItem('choice');
			if($cur_page ){
				if(self::$conf['strict_navigation'] == 1){//store only pages not choices
					if($cur_page == $page){
						$history_option = 0 ;//you repeated the same option
					}
				}else{
					if($cur_page == $page && $cur_choice == $choice){
						$history_option = 0 ;//you repeated the same option
					}
				}
				
			}
			
		}

		switch ($history_option) {
			case 0 :
				# code...
				return ;
			case 1 :
				self::set_page($page,$choice);
				# code...
				break;
			case 2 :
				self::set_page($page,$choice);
				self::debug("history option => $history_option , hidden => $history_option");
				self::debug("Saving Page history page => $page , choice => $choice");
				//save page history
				self::$session->push('history',array('page' => $page , 'choice' => $choice ,'data' => $data));
				# code...
				break;
			
			default:
				# code...
				break;
		}
		
		
	}
	static function choice_exists($choice,$page){
		self::debug("choice_exists : page => $page, choice => $choice ");
		$pages = self::get_pages();
		$page_choices = $pages[$page]['action'];
		$choiceOverrideDefined = self::choiceOverrideDefined($page);
		return array_key_exists($choice,$page_choices) || $choiceOverrideDefined;
	}
	static function choiceOverrideDefined($page){
		$pages = self::get_pages();
		$choiceOverrideDefined = array_key_exists('choiceOverride',$pages[$page]);
		return $choiceOverrideDefined;
	}


	static function set_last_menu($menus){
		self::$session->setItem('last_menu',$menus);
	}

	static function get_last_menu(){
		return self::$session->getItem('last_menu');
	}
	static function set_choice_range($range){
		if(!@$range['start']){
			$range['start'] = 1;
		}

		if(!isset($range['limit']) || @$range['start'] > @$range['limit']){
			return false;
		}

		self::$session->setItem('choiceRange',$range);

	}
	static function clear_choice_range(){
		self::$session->removeItem('choiceRange');
	}
	static function choice_exists_in_last_menu($choice){
		$pages = self::get_pages();
		$page = self::current_page();
		if(self::choiceOverrideDefined($page)){
			$range = self::$session->getItem('choiceRange');
			if(!$range){
				self::clear_choice_range();
				return true;//choice override but no range given
			}

			if(!isset($range['limit'])){
					return true;
			}
			$start = $range['start'];
			$limit = $range['limit'];

			if($choice < $start || $choice > $limit){
				return false;
			}
			self::clear_choice_range();
			return true;
		}
		if(!self::choice_exists($choice,$page)){
			return false;
		}
		self::clear_choice_range();
		return true;
	}
	static function count_options_current_page(){
		$pages = self::get_pages();
		$page = self::current_page();
		if(!self::page_exists($page)){
			return null;
		}
		
		return count($pages[$page]['action']);

	}
	
	static function invalid_selection($options = array()){
			if(isset($options['limit'])){
				$total = $options['limit'];
			}else{
				$total = self::count_options_current_page();
			}

			$menus = array(
				'You made an invalid choice'.PHP_EOL,
				'Please select a valid option , Please Try Again',
				PHP_EOL.'To get back , press 0. '.PHP_EOL.'To get to the main menu press 0#'
				);
			if($total > 1){
				$menus = array(
				'You made an invalid choice'.PHP_EOL,
				'Please select a valid option between 1 and '.$total.', Please Try Again',
				PHP_EOL.'To get back , press 0. '.PHP_EOL.'To get to the main menu press 0#'
				);
			}
			
			self::display_repeat_choice($menus);
			self::pause();
			if(!in_array('no-exit', $options)){
				die();
			}

	}
	static function ensure_page_exists($page){
		
		$pages = self::get_pages();
		self::debug('ensure_page_exists : page => '.$page);
		if(!self::page_exists($page)){
			$menus = array("Page/Menu [$page] Not Found : Press * To Go Back");
			self::display_input($menus);
			return;
		}

	}
	static function ensure_choice_exists($choice,$page){
		
		$pages = self::get_pages();
		self::debug('ensure_choice_exists : page => '.$page.', choice => '.$choice);
		if(!self::page_exists($page)){
			self::invalid_selection();
			return;
		}
		if(!self::choice_exists($choice,$page)){
			self::invalid_selection();
			return;
		}

	}
	//pretend to have moved to next page so we could get back on the right page 
	static function fake_forward(){
		self::next_page();
	}
	static function ensure_input_valid(){
		$action = self::get_action();
		$data  =  self::getData();
		self::debug('validating input key/parameter : '.$action['key']);
		switch ($action['validate']) {
			case 'int':
				# code...
				return is_int(intval($data));
				break;
			case 'numeric':
				return is_numeric($data);
				# code...
				break;
			case 'string':
				# code...
				return is_string(($data));	
				break;
			case 'currency':
				# code...
				return is_float(floatval($data));
				break;
			case 'something':
				# code...
				break;
			default:
				# code...
				return true;
				break;
		}
	}
	static function peep_prev(){
		self::debug('peep_prev : ');
		$prev = current_page() - 1;
		if($prev < 0){
			return false;
		}
		return $prev;
	}
	static function prev_page(){ 
		
		$prev = self::current_page() - 1; 
		if($prev < 0){
			self::reset_pages();
			self::next_page();
			$prev = self::current_page();
		}
		self::set_page($prev);
		self::debug('prev_page : '.$prev);
		return $prev;
	}

	static function reset_pages(){
		return self::init_pages();
	}

	static function restart(){
		self::close_session();
	}
	static function forward(){
		self::debug('forward');
		self::set_opcode('forward');
		$pages = self::get_pages();
		$page = self::current_page();
		$choice = 0;
		self::next_page();
		self::ensure_page_exists($page);
		return $pages[$page]['inputComplete']($choice);

	}

	static function afterChoice(){
		self::debug('unconditional After Choice Override');
		self::set_opcode('unconditionalAfterChoiceOverride');
		$pages = self::get_pages();
		$page = self::current_page();
		$choice = 0;
		self::next_page();
		if(array_key_exists('choiceOverride', $pages[$page])){
			self::debug('Execute unconditional After Choice Override');
			return $pages[$page]['choiceOverride']();
		}
		self::debug('Deffered unconditional After Choice Override');
		return $pages[$page]['inputComplete']($choice);

	}

	static function set_page($page,$choice = 0){
		self::$session->setItem('page',$page);
		self::$session->setItem('choice',$choice);
	}
	static function going_back(){
		return self::$session->getItem('opcode') == 'back';
	}
	static function back(){
		

		//set_last_menu(array());
		$history = self::$session->pop('history');//pop twice to get back
		
		$page = $history['page'];
		$choice = $history['choice'];
		$data = @$history['data'];
		//is it going to be the same page? are we on the same page
		if(!self::going_back()){
			if($page == self::current_page() && $choice == self::current_choice()){
				$history = self::$session->pop('history');
				$page = $history['page'];
				$choice = $history['choice'];
			}
		}
		
		if(!$history){
			$page = $choice =  0;
		}
		//mute the choice and go to the page
		if(self::$conf['strict_navigation'] == 1){
			$choice =  0;
		}
		
		self::debug('back to : page => '.$page.',choice => '.$choice);
		self::run_page(array('page'=>$page,'choice'=>$choice, 'data' => $data,'back'=>true,'private'=>true));//redisplay last valid page
		self::pause();
	}
	static function inputComplete(){
		// debug('Input inputCompleted Successfully : Now Lets Pay Some Fees',true);
		// debug(session()->getSession(),true);
		//session()->destroy();
	}
	
	static function run_page($options){
		//debug("Runing Page : $page");
		$page = @$options['page'];
		$choice = @$options['choice'];
		$history = @$options['private'];
		$data = @$options['data'];
		$back = @$options['back'];
		$pages = self::get_pages();
		self::ensure_page_exists($page);

		$history_option = 2;/// save every history about this page , page becomes navigatable
		if(in_array('hidden', $options)  || isset($options['hidden'])){
			$history_option = 0;
		}
		if(in_array('private', $options) || isset($options['private']) ){
			$history_option = 1; //set current page but don't record page history
		}
		self::record_history($page,$choice,$history_option,$data);
		
		// if($back){
		// 	set_opcode('back');
		// }


		if(!self::choice_exists($choice,$page)){
			self::debug("Runing Page : page => $page , choice => $choice");
			$pages[$page]['init']($data);
			return self::pause();
		}
		//navigate only pages , ignore choices
		return $pages[$page]['init']($data);
		//ensure_choice_exists($choice,$page);
		//debug("Runing Page With Choice : page => $page , choice => $choice");
		// /return $pages[$page]['action'][$choice]($data);
	}
	static function run_page2($page,$choice = 0 ,$save_history = true){
		self::debug("Runing Page : $page");
		$pages = self::get_pages();
		self::ensure_page_exists($page);
		self::record_history($page,$choice,$save_history);
		if(!self::choice_exists($choice,$page)){
			self::debug("Runing Page : page => $page , choice => $choice",true);
			return $pages[$page]['init']($choice);
		}
		//ensure_choice_exists($choice,$page);
		self::debug("Runing Page With Choice : page => $page , choice => $choice",true);
		return $pages[$page]['action'][$choice]();
	}
	
	static function cancel(){
		self::close_session();
	}
	static function receive_input(){
		$action = self::get_action();
		$data = self::getData();
		if(!isset($action['key'])){
			self::display_prompt('Attempting to receive an unexpected input');
		}

		self::debug("Receiving Input [".$action['key']."  = ".$data."]");
		
		
		self::$session->setDataItem($action['key'],$data);
	}

	static function analyze_input(){
		self::debug('analyze_input : '.self::getData());
		
		// if(!preview_data()){
		// 	die ;
		// }
		$data = self::getData();
		
		$valid_input = self::ensure_input_valid();
		if(!$valid_input){
			$menus = array('0' => 'The Input Is Invalid, Please Try Again');
			self::display_repeat_input($menus);
			return $valid_input;
		}
		return $valid_input;
	}

	static function repeat(){

	}

	static function preview_data($msisdn,$started){
		self::debug('preview_data : '.self::getData());
		$data = trim(self::getData());
		
		if(in_array($data, $conf['back_chars'])){
			back();
			return false;
		}

		if($data == '#'){
			self::$session->pop('history');
			self::reset_pages();
			self::show_page(array('page'=>'0'));
			return false;
		}

		if($data == '0#'){
			self::$session->removeItem('history');
			self::reset_pages();
			self::show_page(array('page'=>'0'));
			return false;
		}

		if(in_array($data, self::$conf['short_code']) || $data == 0 || !$data){
			self::$session->removeItem('history');
			self::reset_pages();
			self::show_page(array('page'=>'0'));
			return false;
		}

		return true;
	}

	static function preview_data2($msisdn,$started){
		self::debug('preview_data : '.self::getData());
		$data = trim(self::getData());
		//debug_session();

		if(in_array($data, self::$conf['back_chars'])){
			self::back();
			self::pause(true);
			return false;
		}
		if(in_array($data, self::$conf['root_menu_chars'])){
			self::$session->destroy();
			self::reset_pages();
			self::show_page(array('page'=>'0'));
			self::pause(true);
			return false;
		}
		if(in_array($data, self::$conf['clear_session_chars'])){
			self::$session->destroy();
			self::reset_pages();
			self::show_page(array('page'=>'0'));
			self::pause(true);
			return false;
		}

		if(in_array($data, self::$conf['short_code'])){
			//$started = false;
			//self::$session->removeItem('history');

			$operation = self::get_operation();
			if($operation == 'prompt'){
				self::$session->destroy();
				self::reset_pages();
				self::show_page(array('page'=>'0'));
				self::pause(true);
			}
			self::debug("Data Matched Short Code");
			if(!self::$conf['persist_sessions'] || !$started){
				self::debug("Turn persist_sessions On To enable session resume");
				self::$session->destroy();
				self::reset_pages();
				self::show_page(array('page'=>'0'));
				self::pause(true);
				return false;
			}	


			//now resume previous session
			// $history = self::$session->pop('history');//pop twice to get back
		
			// $page = $history['page'];
			// $choice = $history['choice'];
			
			$page = self::$session->getItem('page');
			$choice = self::$session->getItem('choice');
			if(!is_null($page)){	
				self::debug("Restarting session at page => $page");
				self::show_page(array('page'=>$page));
				self::pause(true);
				return false;
			}
			if(!is_null($choice)){
				self::debug("Restarting session at page => $page, choice => $choice");
				self::show_page(array('page'=>$page,'choice'=>$choice));
				self::pause(true);
				return false;
			}
			
		}

		return true;
	}


	//ensure choice is valid
	static function analyze_choice(){

		// if(!preview_data()){
		// 	die ;
		// }
		$choice = self::getData();
		if(is_null($choice) || empty($choice)){
			if(self::$conf['remain_on_current_if_no_choice']){
				$page = self::$session->getItem('page');
				self::show_page(array('page'=>$page));
				self::pause(true);
				return true;
			}
		}	

		self::debug('analyze_choice : '.$choice);

		return self::choice_exists_in_last_menu($choice);

	}
	static function show_menu($start = false){
		if($start){
			self::reset_pages();
		}
		$choice = self::getData();
		$page = self::next_page();
		self::run_page($page,$choice);
	}

	static function show_page($options){
		self::run_page($options);
		self::pause(true);
	}
	static function get_opcode(){
		return self::$session->getItem('opcode');
	} 
	static function set_opcode($opcode){
		return session()->setItem('opcode',$opcode);
	} 
	static function show_page_if($opcode ,$page,$alt_page,$choice = 0){
		$cur_code = self::get_opcode();
		self::set_opcode(null);//invalidate opcode
		if(($cur_code  && $opcode) && ($cur_code == $opcode) ){
			self::show_page($page,$choice);
		}else{
			self::show_page($alt_page,$choice);
		}
	}
	static function pause($die = false){
		self::$session->save();
		if($die){
			die();
		}
		
	}
	static function choice_action(){
		$pages = self::get_pages();
		$page = self::current_page();
		$choice = self::getData();

		self::debug('choosing action : page = ' .$page.', choice = '.$choice);
		//if choice override
		if(self::choiceOverrideDefined($page)){
			return self::afterChoice();
		}
		self::record_history($page , $choice);
		self::ensure_choice_exists($choice,$page);

		return $pages[$page]['action'][$choice]();
	}
	static function set_action($operation,$key,$validate,$desc){

		$arr['operation'] = $operation;
		$arr['key'] = $key;
		$arr['validate'] = $validate;
		$arr['description'] = $desc;

		self::$session->setItem('action',$arr);
	}
	static function set_display_action(){
		self::set_action('awaiting_choice',null,null,'Displaying Menu, Awaiting Choice');
	}
	static function set_input_action($key = null,$validate = null, $title = null){
		if(!$title){
			$title = 'Waiting For Input Value';
		}
		self::set_action('awaiting_input',$key,$validate,$title);
	}
	static function get_operation(){
		$action = self::$session->getItem('action');
		if(!isset($action['operation'])){
			return 'start';
		}
		return $action['operation'];
	}
	
	static function get_action(){
		$action = self::$session->getItem('action');
		if(!$action){
			return [];
		}
		return $action;
	}

	
	static function ensure_data_suplied(){
		// if(!isset($_REQUEST['msg'])){
		// 	die("No Data Entered");
		// }
		if(!isset($_REQUEST['msisdn'])){
			self::display_prompt("No msisdn Data Entered");
		}
	}
	
 	static function continue_operation(){
 		self::run_operation();
 	}


 	static function run_operation(){
 	self::ensure_data_suplied();
 	$operation = self::get_operation();
	self::debug("Execute Action : ".$operation);
	
	switch ($operation) {
		case 'start':
			self::reset_pages();
			self::show_page(array('page'=>'0'));
			break;
		case 'awaiting_input':
			self::analyze_input();
			self::receive_input();
			self::forward();
			break;
		case 'awaiting_choice':
			self::analyze_choice();
			self::choice_action();
			break;
		case "invalid_input" : 
			self::analyze_input();
			self::receive_input();
			self::forward();
			break;
		case "invalid_choice" : 
			self::analyze_choice();
			self::choice_action();
			break;
		case 'inputComplete':
			# code...
			 self::inputComplete();
			break;
		case 'prompt':
			# code...
			  self::inputComplete();
			break;
		default:
			self::what();
			# code...
			break;
	}
}

	}
?>