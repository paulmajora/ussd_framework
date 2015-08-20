<?php
	define('DOMAIN','http://example.com');
	define("DB_HOST", 'localhost');
	define('USER','root');
	define('PASS','passwd');
	define('DB','ussd_session');
	// var_dump($_REQUEST);die;
	//echo '/ussd_framework/autoload.php';echo realpath('/ussd_framework/autoload.php');
	require_once('autoload.php');


	$data = @$_REQUEST['msg'];
	$msisdn = @$_REQUEST['msisdn'];
	$network = @$_REQUEST['network'];


	$session = new ussd($msisdn);

	$conf = array(
		'short_code' => array('*717*30','*524*30','*718*30'),
		'root_menu_chars' => array('0#'),
		'back_chars' => array('*','0'),
		'clear_session_chars' => array('0#', '*#'),
		'remain_on_current_if_no_choice' => true,

		//user db settings
		//user db settings // may be unsed 
		'db_host' => DB_HOST,
		'db_name' => DB,
		'db_user' => USER,
		'db_password' => PASS,
		//serialize and deserialize session to/from db
		'persist_sessions' => true,


		//debugging
		'debug' => !true,
		'debug_session' => false,
		'debug_menu' => false
		);
	$helper = new nav($session, get_pages(),$conf);
	// $m = array("msisdn = $msisdn, data = $data");
	// send_response_to_gateway('showMenu',$m,'');
	// exit();
	
	error_reporting(E_ALL);

	nav::init($msisdn);
	
	function &session(){
		global $session;
		return $session;
	}
	function &ussd(){
		global $helper;
		return $helper;
	}
	function getData(){
		return @$_REQUEST['msg'];
	}
	function network(){
		if(!isset($_REQUEST['network'])){
			return 'network';
		}
		return @strtoupper($_REQUEST['network']);
	}

	function msisdn(){
		if(!isset($_REQUEST['msisdn'])){
			return 'msisdn';
		}
		return @$_REQUEST['msisdn'];
	}

	function clear_session(){
		ussd::destroy();
	}
	function get_pages(){
		return get_grouping_pages();
	}
	function get_grouping_pages(){

	//landing area
	$actions = array(
			'0' => // root page
				array(
						'action' => array(),
						'init' => function(){
								// user dialed the short code
								nav::show_page(array('page'=>'welcome_page'));
								
						},
						'inputComplete' => function(){
							
					}),
			'welcome_page' =>
				array(
						'action' => array(
							'1' => function(){
								nav::show_page(array('page'=>'enter_name'));
							},
							'2' => function(){
								nav::show_page(array('page'=>'about'));
							},
							'3' => function(){
								$menus = array(
								'Good Bye , You Can Access Groupings By Dialing *718*30# On Any Network',
									);
								nav::display_prompt($menus);
							}),
						'init' => function(){
								$menus = array(
									'Welcome To GroupIns'.PHP_EOL,
									'1. Enter Name',
									'2. My Details',
									'3. Exit',
								);
							nav::display($menus);
						},
						'inputComplete' => function(){
							
					}),
			'enter_name' => 
				array(
						'action' => array(),
						'init' => function($d){
								$title = array(
									'Enter your Name:'
									);
									if( $d &&is_string($d)){
										$title = $d;
									}
									nav::await_input('name','',$title);
						},
						'inputComplete' => function(){
								nav::show_page(array('page'=> 'enter_age'));
					}),
			'enter_age' => 
				array(
						'action' => array(),
						'init' => function($d){
									$title = 'Enter Your Age ';
									if( $d &&is_string($d)){
										$title = $d;
									}
									nav::await_input('age','',$title);
						},
						'inputComplete' => function(){
								nav::show_page(array('page'=>'about'));
					}),
			'about' => 
				array(
						'action' => array(
							'1' => function(){
								nav::show_page(array('page'=>'enter_name'));
							},
							'2' => function(){
								nav::show_page(array('page'=>'enter_age'));
							}),
						'init' => function(){
									$name = ussd::getDataItem('name') ? ussd::getDataItem('name') : "Mr Beans";
									$age = ussd::getDataItem('age') ? ussd::getDataItem('age') : "Just 365";

									$menus = array(
										"About Me",
										"Your Name Is " . $name,
										"You Are " .$age." Years Old",

										"Press 1 to Change Your Name , 2 To Change Your Age",
									);
									nav::display($menus);
						},
						'inputComplete' => function(){
							
					}),
	);

	return $actions;
}

?>
