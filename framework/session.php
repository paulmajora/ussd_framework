<?php

	require_once('file_io.php');
	require_once('ussdSession.php');
	$msisdn = "233201870624";
	$sess = new ussdSession($msisdn);

	$sess->setItem('textKey','TestValue');
	$sess->setItem('textKey2','TestValue2');
	$sess->setItem('textKey3','TestValue3');
	var_dump($sess->session());
	$sess->save();
	echo "<br>";
	//welcome back
	$msisdn = "233201870624";
	$sess = new ussdSession($msisdn);
	var_dump($sess->session());
	echo "<br>";
	//welcome back
	$msisdn = "233201870624";
	$sess = new ussdSession($msisdn);
	$sess->setItem('welcome','back');
	$sess->save();
	var_dump($sess->session());
	//$sess->destroy();
?>