<?php
session_start();
$_SESSION = array();
session_destroy();
require_once 'includes/_header.php';
Functions::setFlash("Vous avez bien été déconnecté.");
header('Location:index.php');exit;
?>