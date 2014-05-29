<?php 
	require_once 'includes/_header.php';
	$Auth->allow('member');
	
	require_once 'class/ListAdmins.class.php';
	$dataForm = array();
	if (isset($_GET['options'],$_GET['action'],$_GET['recherche1']))
		$dataForm = $_GET;
	else
		$dataForm = $_POST;
	$dataForm['perPages'] = 3000;
	$dataForm['export'] = true;
  	$ListAdmins = new ListAdmins($dataForm);
  	$ListAdmins->exportAdminList();
?>