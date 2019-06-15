<?php

#https://tech.yandex.ru/dialogs/alice/doc/auth/account-linking-docpage/

function debug($text,$var=false,$prefix="")
{
	$file='debug.log';
	if (!$var)
		$message=$text;
	else
		$message=var_export($text,true);
	$message=date("Y-m-d H:i:s:v").": ".$message;
	if (strlen($prefix)>0)
		$message=$prefix.$message;

	file_put_contents($file, $message."
", FILE_APPEND);
}

$dataRow = file_get_contents('php://input');
$data = json_decode($dataRow);
$orig="";

$access_token="acceess123456789";
$refresh_token="refresh123456789";
$token_type="bearer";
$expires_in=2592000;
$code="test123";
if (isset($_SERVER['REQUEST_URI']))
	$orig=$_SERVER['REQUEST_URI'];

debug($_SERVER['REQUEST_URI'],1,"REQUEST_URI:");
debug($_GET,1,"GET:");
debug($_POST,1,"POST:");
debug ($dataRow,1,"dataRow:");
debug ($data,1,"data:");
debug($orig,1,"ORIG: ");

switch ($orig){
	#'/v1.0/user/devices/action'
	#'/v1.0/user/devices/query'
	case (strpos($orig,'/v1.0/user/devices') ? true : false): 
	
		include "device.php";
		break;
	case (strpos($orig,'/v1.0/user/devices/query') ? true : false): 
		#debug();
		break;
	case (strpos($orig,'/v1.0/user/unlink') ? true : false): 
		$message="Unlink.";
		break;
	
	# Блок авторизации - на данный момент чисто номинально, чтобы нас приняли.
	case strpos($orig,'/auth/auth')!==false: 
	case strpos($orig,'/auth/token')!==false: 
		$message="/auth/token.";
		if (isset($_GET['redirect_uri'])) {
			$redirect_uri=$_GET['redirect_uri'];
			$state=$_GET['state'];
			
			$message='<meta name="viewport" content="width=device-width, height=device-height initial-scale=1 user-scalable=no">
		    <body style="background:#310d80;">
		    <footer style="position:fixed; bottom:0; left:0; right:0; padding:24px 16px;">
		    <a style="display:block; text-align:center; background:#7b3dcc; color:#fff; cursor:pointer; font-family:Arial,sans-serif; font-size:20px; padding:13px 16px; border-radius:6px; text-decoration:none;" 
		    href="'.$redirect_uri.'?code='.$code.'&state='.$state.'">Подключить умный дом</a>';;
		}
		if (isset($_POST['code'])&&isset($_POST['grant_type']))
			$message='{ "access_token": "'.$access_token.'", "token_type": "'.$token_type.'", "expires_in": "'.$expires_in.'", "refresh_token": "'.$refresh_token.'" }';
		break;

	default:
		$message="This page SmartHome for Yandex.";
}

echo $message;
