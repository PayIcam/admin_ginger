<?php 
require_once 'includes/_header.php';
$Auth->allow('admin');
$conf = Config::get('dropbox_save');

function uploadFileToDropbox($backup_filename, $mail, $pswd, $save_folder){
    try {
        //on envoie sur Dropbox
        $d = time();
        $uploader = new DropboxUploader($mail, $pswd);
        $uploader->setCaCertificateFile(__DIR__.'/includes/certificate.cer');
        $uploader->upload($backup_filename,$save_folder);
        $deltaT = time() - $d;
        Functions::setFlash('File <em>'.basename($backup_filename).'</em> successfully uploaded to your Dropbox <em>('.$mail.')</em>! <br>
                <small>Saved & uploaded in : '.($deltaT).'s</small>');
        return true;
    } catch (Exception $e) {
        // Handle Upload Exceptions
        $label = ($e->getCode() & $uploader::FLAG_DROPBOX_GENERIC) ? 'DropboxUploader' : 'Exception';
        $error = sprintf("[%s] #%d %s", $label, $e->getCode(), $e->getMessage());

        Functions::setFlash('Error: ' . htmlspecialchars($error) . '','danger');
        return false;
    }
}

require_once 'class/DropboxUploader.php';
if ($_POST) {
    foreach ($conf['db_to_save'] as $db) {
            $d = time();
            //création du fichier backup_filename avec son nom de DB+Date
            $backup_filename = __DIR__.DS.$db['sql_db'].'-'.date('Y-m-d_h-i-s').'.sql';
             
            //mysqldump --host=".$host." --user=".$username." --password=".$password."
            //execute la commande pour récupérer la base de donnée
            $command = $conf['path_to_mysqldump'].'mysqldump --host='.$db['sql_host'].' --user='.$db['sql_user'].' --password='.$db['sql_pass'].' '.$db['sql_db'].' > '.$backup_filename;
            passthru($command);
            
            //on envoie sur Dropbox
            uploadFileToDropbox($backup_filename, $conf['mail_dropbox'], $conf['pwsd_dropbox'], $conf['save_folder']);
            if ($_POST['email'] && $_POST['password']) {
                uploadFileToDropbox($backup_filename, $_POST['email'], $_POST['password'], $conf['save_folder']);
            }
             
            //On supprimme le fichier backup_filename
            unlink($backup_filename);
    }
}

require_once 'includes/Forms.class.php'; if(!isset ($form)){$form = new form();}


$title_for_layout = 'Sauvegarde Bases de données sur Dropbox';
include 'includes/header.php';
?>

<h1 class="page-header"><span class="glyphicon glyphicon-export"></span> Sauvegarder les bases de données de Ginger et PayIcam</h1>
<p>Les sauvegardes seront placées sur le compte dropbox <em><?= $conf['mail_dropbox']; ?></em> dans le répertoire <em><?= $conf['save_folder'] ?></em></p>
<form action="admin_save_db_dropbox.php" method="POST" class="form-horizontal">
    <fieldset>
        <legend>Sauvegarder aussi sur un autre compte Dropbox :</legend>
        <?= $form->input('token', 'hidden', array('value'=>Auth::generateToken())); ?>
        <?= $form->input('email', 'Mail :'); ?>
        <?= $form->input('password', 'Password :', array('type'=>'password')); ?>
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