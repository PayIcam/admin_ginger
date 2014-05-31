<?php 
require_once 'includes/_header.php';
$Auth->allow('admin');

$title_for_layout = 'Test Websale';

include 'includes/header.php';

?>

<h1 class="page-header"><span class="glyphicon glyphicon-cog"></span> Test Websale</h1>

<a href="#" class="btn btn-primary">Try Websale</a>

<?php include 'includes/footer.php'; ?>