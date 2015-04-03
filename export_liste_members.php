<?php 
	require_once 'includes/_header.php';
	$Auth->allow('member');
	
	require_once 'class/ListMembers.class.php';

	$dataForm = array();
	if (isset($_GET['action'],$_GET['recherche1']))
		$dataForm = $_GET;
	else
		$dataForm = $_POST;
	$dataForm['perPages'] = 3000;
	$dataForm['export'] = true;
  	$ListMembers = new ListMembers($dataForm);

  	$ListMembers->exportMemberList();
?>