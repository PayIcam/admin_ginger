<?php 
require_once 'includes/_header.php';
$Auth->allow('admin');

require_once 'class/DropboxUploader.php';
if ($_POST) {
    try {
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK)
            throw new Exception('File was not successfully uploaded from your computer.');

        if ($_FILES['file']['name'] === "")
            throw new Exception('File name not supplied by the browser.');

        // Upload
        $uploader = new DropboxUploader($_POST['email'], $_POST['password']);
        $uploader->setCaCertificateFile(__DIR__.'/includes/certificate.cer');
        $uploader->upload($_FILES['file']['tmp_name'], $_POST['destination'], $_FILES['file']['name']);

        Functions::setFlash('File successfully uploaded to your Dropbox!');
    } catch (Exception $e) {
        // Handle Upload Exceptions
        $label = ($e->getCode() & $uploader::FLAG_DROPBOX_GENERIC) ? 'DropboxUploader' : 'Exception';
        $error = sprintf("[%s] #%d %s", $label, $e->getCode(), $e->getMessage());

        Functions::setFlash('Error: ' . htmlspecialchars($error) . '','danger');
    }
}

require_once 'includes/Forms.class.php'; if(!isset ($form)){$form = new form();}

$title_for_layout = 'Uploader un fichier sur une dropbox';
include 'includes/header.php';

?>

<h1 class="page-header"><span class="glyphicon glyphicon-cloud-upload"></span> Uploader un fichier sur une dropbox</h1>
<form action="admin_upload_file_dropbox.php" method="POST" enctype="multipart/form-data" class="form-horizontal">
	<fieldset>
    	<legend>Infos Compte Dropbox :</legend>
    	<?= $form->input('token', 'hidden', array('value'=>Auth::generateToken())); ?>
		<?= $form->input('email', 'Mail :'); ?>
		<?= $form->input('password', 'Password :', array('type'=>'password')); ?>
	</fieldset>
	<fieldset>
    	<legend>Fichier à uploader :</legend>
		<?= $form->input('destination', 'Destination :'); ?>
		<?= $form->input('file', 'File :', array('type'=>'file')); ?>
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