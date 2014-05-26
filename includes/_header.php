<?php
	define('DS', DIRECTORY_SEPARATOR);
	define("ROOT_PATH", preg_replace('/includes$/', '', dirname(__FILE__)));
	define("HOME_URL", dirname($_SERVER['PHP_SELF']));

	require_once ROOT_PATH.'class/Config.php';
	require "config.php";
	Config::initFromArray($_CONFIG);

	require_once ROOT_PATH.'class/db.class.php';
	$DB = new DB(Config::get('sql_host'),Config::get('sql_user'),Config::get('sql_pass'),Config::get('sql_db'));

	require_once ROOT_PATH.'includes/functions.php' ;
	define('WEBSITE_TITLE', Functions::getConfig('websitename'));
	Functions::maintenance();
	if(!isset ($_SESSION)){session_start();} //si aucun session active

	header('Content-Type: text/html; charset=utf-8');
	if(!Functions::islog() && basename($_SERVER['SCRIPT_FILENAME']) != 'connection.php'){	// sécuriser l'accès
	    Functions::setFlash('<strong>Identification requise</strong> Vous ne pouvez accéder à cette page.','danger');
	    header('Location:connection.php');exit;
	}
?>