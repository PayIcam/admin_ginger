<?php 

// Paramètres de BDD
$_CONFIG['sql_host'] = "localhost";
$_CONFIG['sql_user'] = "root";
$_CONFIG['sql_pass'] = "";
$_CONFIG['sql_db']   = "payicam_ginger";

// Configuration de ginger (outil association UID carte étudiante étudiant)
// En environnement de dev, utiliser https://github.com/PayIcam/faux-ginger
$_CONFIG['ginger_key'] = "test_ginger";
$_CONFIG['ginger_url'] = "http://localhost/ginger/index.php/v1/";

// Chemin vers le serveur CAS (avec le / final)
$_CONFIG['cas_url'] = "";