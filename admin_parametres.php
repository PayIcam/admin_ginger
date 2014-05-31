<?php 
require_once 'includes/_header.php';
$Auth->allow('admin');

define('IMG_PATH', 'files/');
require 'vendor/autoload.php';

$params = array(
	'maintenance'  => Config::getDbConfig('maintenance'),
	'websitename'  => Config::getDbConfig('websitename'),
	'contact'      => Config::getDbConfig('contact')
);

if (isset($_POST['edition'])) {
	foreach ($params as $k => $v) {
		if ($k == 'plaquette' || $k == 'pres_img') break;
		if (isset($_POST[$k]) && $_POST[$k] != $params[$k]) {
			Config::setDbConfig($k,$_POST[$k]);
			$params[$k] = $_POST[$k];
		}
	}
	header('Location:admin_parametres.php');exit;
}

$title_for_layout = 'Paramètres';
require_once 'includes/Forms.class.php'; if(!isset ($form)){$form = new form();}
$form->set($params);
include 'includes/header.php';

?>

<h1 class="page-header"><img src="img/icons/gear_48.png" alt="Paramètres"> Paramétrer le site</h1>
<form action="admin_parametres.php" method="POST" enctype="multipart/form-data" class="form-horizontal">
	<fieldset>
    	<legend>En vrac :</legend>
    	<?= $form->input('token', 'hidden', array('value'=>Auth::generateToken())); ?>
		<?= $form->input('edition', 'hidden', array('value'=>'true')); ?>
		<?= $form->input('maintenance','Maintenance :',array('type'=>'checkbox')); ?>
		<?= $form->input('websitename','Nom du site : ', array('maxlength'=>"255")); ?>
		<?= $form->input('contact','Email de contact : ', array('maxlength'=>"255")); ?>
	</fieldset>
	<div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
	        <button class="btn btn-primary" type="submit">Save changes</button>
	        &nbsp;
	        <button class="btn btn-default" type="reset">Cancel</button>
        </div>
    </div>            
</form>
<?php include 'includes/footer.php'; ?>