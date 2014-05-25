<?php 
require_once '../class/db.class.php';
$DB = new DB();

require_once 'functions.php' ;
Functions::maintenance();

if (isset($_GET['type'],$_GET['term']) && in_array($_GET['type'], array('author','authors[0][name]','collection[name]','collection','editor','tag','nbPost'))) {
	$q = array('term'=>$_GET['term']);
	$json = array();
	if (preg_match('/author/i', $_GET['type'])){
		$sql = "SELECT firstname,name,id FROM authors WHERE firstname LIKE '%".$_GET['term']."%' OR name LIKE '%".$_GET['term']."%'";
		foreach ($DB->query($sql) as $result) {
			$json[] = array('id'=>$result['id'],'label'=>$result['name'].', '.$result['firstname']);
		}
		die(json_encode($json));
	}
	elseif(preg_match('/collection/i', $_GET['type']))
		$sql = 'SELECT name,id FROM collections WHERE name LIKE "%'.$_GET['term'].'%"';
	elseif(preg_match('/editor/i', $_GET['type']))
		$sql = 'SELECT name,id FROM editors WHERE name LIKE "%'.$_GET['term'].'%"';
	elseif(preg_match('/tag/i', $_GET['type']))
		$sql = 'SELECT name,id FROM terms WHERE type = "tag" AND name LIKE "%'.$_GET['term'].'%"';
	elseif(preg_match('/nbPost/i', $_GET['type']))
		$sql = 'SELECT name,id FROM terms WHERE type = "nbPages" AND name LIKE "%'.$_GET['term'].'%"';
	foreach ($DB->query($sql) as $result) {
		$json[] = array('id'=>$result['id'],'label'=>$result['name']);
	}
	die(json_encode($json));
}

?>