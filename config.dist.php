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

// URL publique http(s) vers admin_ginger (avec le / final)
$_CONFIG['admin_ginger_url'] = "http://xxx/admin_ginger/";

// Paramêtres de sauvegarde de la base de donnée
$_CONFIG['dropbox_save'] = array(
	'mail_dropbox' => 'xxx',
	'pwsd_dropbox' => 'xxx',
	'save_folder' => 'Psaves_db',
	'path_to_mysqldump' => 'C:\wamp\bin\mysql\mysql5.6.12\bin\\',
	'db_to_save' => array(
		'payicam_ginger' => array(
			'sql_host' => $_CONFIG['sql_host'],
			'sql_user' => $_CONFIG['sql_user'],
			'sql_pass' => $_CONFIG['sql_pass'],
			'sql_db'   => $_CONFIG['sql_db']
		),
		'payicam_prod' => array(
			'sql_host' => 'localhost',
			'sql_user' => 'root',
			'sql_pass' => '',
			'sql_db'   => 'payicam_prod'
		),
		'payicam_dev' => array(
			'sql_host' => 'localhost',
			'sql_user' => 'root',
			'sql_pass' => '',
			'sql_db'   => 'payicam_dev'
		)
	)
);