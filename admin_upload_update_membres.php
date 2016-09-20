<?php
require_once 'includes/_header.php';
$Auth->allow('admin');

if (!empty($_FILES['newStudents'])) {
    if (substr($_FILES['newStudents']['name'], -4) != '.csv') {
        Functions::setFlash("merci de transmettre un .csv séparé avec des ;",'warning');
        // header('Location:admin_upload_update_membres.php');exit;
    }

    $uploadfile = 'uploads/listeElevesUpload'.date("Ymd_Hi").'.csv';
    if (!@move_uploaded_file($_FILES['newStudents']['tmp_name'], $uploadfile)) {
        Functions::setFlash("Vous avez envoyé un drôle de fichier ou alors ce dernier est trop lourd.",'warning');
        // header('Location:admin_upload_update_membres.php');exit;
    }

    $StudentImportCtrl = new \AdminGinger\StudentImportController($uploadfile);

    $msg = ''.$StudentImportCtrl->counts['global'].' élèves en base de données:<ul>';
    $msg .= '<li>'.$StudentImportCtrl->counts['pasMaj'].' non mis à jours</li>';
    $msg .= '<li>'.$StudentImportCtrl->counts['update'].' mis à jours</li>';
    $msg .= '<li>'.$StudentImportCtrl->counts['updateRedoublants'].' redoublants</li>';
    $msg .= '<li>'.$StudentImportCtrl->counts['nouveau'].' nouveaux</li></ul>';
    $msg .= '<h3>mails à erreur</h3>';
    $msg .= '<ul>';
        $msg .= '<li>'.implode('</li><li>', array_keys($StudentImportCtrl->usersPerGroup['problemes'])).'<li>';
    $msg .= '</ul>';
    Functions::setFlash($msg,'warning');
    // header('Location:admin_upload_update_membres.php');exit;
}

$title_for_layout = 'MAJ membres BDD';

include 'includes/header.php';

?>

<h1 class="page-header"><span class="glyphicon glyphicon-cloud-upload"></span> Charger de nouveaux membre <small>et ou mettre à jour les existants</small></h1>
<form action="admin_upload_update_membres.php" method="POST" enctype="multipart/form-data" class="form-horizontal">
    <fieldset>
        <legend>Chargement nouveau fichier :</legend>
        <div class="alert alert-warning">Format du fichier:</div>
        <div class="form-group">
            <label for="newStudents">File input</label>
            <input type="file" name="newStudents" id="newStudents">
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button class="btn btn-primary" type="submit">Envoyer le fichier</button>
        </div>
    </div>
</form>
<?php include 'includes/footer.php'; ?>