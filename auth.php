<?php
	
	require("weixin.php");
	require("xiaodu.php");
	require("mysql.php");
	$weixin = new Weixin();
	$signature = $_GET["signature"];
	$timestamp = $_GET["timestamp"];
	$nonce = $_GET["nonce"];	
			
	$token = 'cuiqingcai';
	$tmpArr = array($timestamp, $nonce, $token);
	sort($tmpArr, SORT_STRING);
	$tmpStr = implode($tmpArr);
	$tmpStr = sha1($tmpStr);

	if($tmpStr == $signature) {
		$weixin->response();
	}
		

