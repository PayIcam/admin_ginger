<?php

session_start();
$_SESSION = array();
session_destroy();

session_start();

require_once 'includes/_header.php';

// if (!empty($_GET['log_cas_out'])) {
// 	$Auth->logCasOut();
// 	Functions::setFlash("Vous avez bien été déconnecté de l'admin ginger & du CAS Icam.");
// }else{
	Functions::setFlash("Vous avez bien été déconnecté de l'admin ginger.");
// }

header('Location:connection.php');exit;
