<?php 
require_once 'includes/_header.php';
$Auth->allow('admin');
require 'vendor/autoload.php';
require_once 'class/Member.class.php';

if (!empty($_POST['import'])) {
  $_POST['import'] = explode("\n", $_POST['import']);
  ///////////////////////////
  // Préparation des label //
  ///////////////////////////
  $labels = explode(';', array_shift($_POST['import']));
  $gingerAvialableLabels = array('nom', 'prenom', 'mail', 'promo', 'filiere', 'badge_uid', 'expiration_badge', 'sexe');
  $error = array();
  $mailPresent = false;
  foreach ($labels as $k => $label) {
    $label = trim($label); $labels[$k] = $label; 
    if (!in_array($label, $gingerAvialableLabels))
      $error[] = $label;
    elseif ($label == 'mail')
      $mailPresent = true;
  }
  if (!empty($error) || !$mailPresent){
    if (!empty($error))
      Functions::setFlash('<strong>Attention</strong>, dans la première ligne, il y a des champs inconus : '.implode(', ', $error), 'warning');
    if (!$mailPresent)
      Functions::setFlash("<strong>Attention</strong>, le champ mail n'est pas disponible...<br>Il est nécessaire de le mettre pour pouvoir réaliser le match et la mise à jour des données...", 'danger');
    if (empty(implode("\n", $_POST['import'])))
      Functions::setFlash("Et puis vous n'avez même pas mit de lignes à importer", 'info');
    $textareaValue = implode(';', $labels) . "\n" . implode("\n", $_POST['import']);
  }else{
    /////////////////////////////
    // Préparation des données //
    /////////////////////////////
    $error = array();
    $errorMail = array();
    $imports = array();
    foreach ($_POST['import'] as $i => $line) {
      $line = trim($line);
      if (empty($line)) continue; // pas grave on saute
      $line = explode(';', trim($line) );
      if (count($labels) != count($line)) {
        $error[] = $i+1;
        continue;
      }
      foreach ($line as $k => $v) {
        $label = $labels[$k];
        $newValue = trim($v);
        if ($label == 'nom' || $label == 'nom') $newValue = str_replace('É', 'é', ucfirst(strtolower($newValue)));
        if ($label == 'mail' && !preg_match('/^[a-z-]+[.]+[a-z-]+([.0-9a-z-]+)?@(mgf\.)?([0-9]{4}[.])?icam[.]fr$/', $newValue)){$errorMail[] = $i+1;continue(2);} ;
        $lineArray[$label] = $newValue;
      }
      $imports[] = $lineArray;
    }
    $imported = 0;
    $updated = 0;

    if (!empty($errorMail)){ $count = count($errorMail);
      Functions::setFlash("<strong>Attention</strong>, vous avez ".$count." ligne".(($count==1)?"":"s")." non valide avec un mail non icam.<br>Soit ".(($count==1)?"à la ligne":"aux lignes")." : ".implode(', ', $errorMail), 'warning');
    }

    if (!empty($error)){
      if (!empty($error)){ $count = count($error);
        Functions::setFlash("<strong>Attention</strong>, vous avez ".$count." ligne".(($count==1)?"":"s")." non valide qui ne respectent pas les labels donnés en première ligne.<br>Soit ".(($count==1)?"à la ligne":"aux lignes")." : ".implode(', ', $error), 'warning');
      }
      $textareaValue = implode(';', $labels) . "\n" . implode("\n", $_POST['import']);
    }else{
      $errorsMultiMail = array();
      foreach ($imports as $import) {
        $count = current($DB->queryFirst('SELECT COUNT(login) FROM users WHERE mail = :mail',array('mail'=>$import['mail'])));
        if ($count > 1) { // ERREUR
          $errorsMultiMail[] = $import['mail'];continue;
        } elseif ($count == 1) { // UPDATE
          $updateFields = array();
          foreach ($import as $key => $value)
            $updateFields[] = $key.' = :'.$key;
          $DB->query("UPDATE users SET ".implode(', ', $updateFields)." WHERE mail = :mail",$import);
          $updated++;
        } else { // INSERT
          $import['login'] = $import['mail'];
          $DB->query("INSERT INTO users (".implode(', ', array_keys($import)).") VALUES (:".implode(', :', array_keys($import)).")",$import);
          $imported++;
        }
      }
      if ($imported > 0 || $updated > 0) {
        if (!empty($imported))
          Functions::setFlash($imported.' Imports réussis avec succès.');
        if (!empty($updated))
          Functions::setFlash($updated.' Updates réussis avec succès.');
      }
      if (!empty($errorsMultiMail)){
        if (!empty($errorsMultiMail)){ $count = count($errorsMultiMail);
          Functions::setFlash("<strong>Attention</strong>, vous avez ".$count." ligne".(($count==1)?"":"s")." non valide: le mail est en double dans Ginger...<br>Soit pour ".(($count==1)?"le mail":"les mails")." : ".implode(', ', $errorsMultiMail), 'danger');
        }
      }

      // On laisse le texte ...
      $textareaValue = implode(';', $labels) . "\n";
      foreach ($imports as $v) {
        $textareaValue .= implode(';', array_values($v)) . "\n";
      }
    }
  }
}

$title_for_layout = 'Import de masse';
include 'includes/header.php';

?>
<h1 class="page-header clearfix">
  <div class="pull-left"><span class="glyphicon glyphicon-user"></span><small><span class="glyphicon glyphicon-plus"></span><span class="glyphicon glyphicon-plus"></span></small> Import de masse</div>
</h1>

<form action="admin_import.php" method="POST">
  <div class="form-group">
    <label for="textareaDataToImport">Données CSV:</label>
    <p class="help-block">
      On attend un fichier CSV avec pour première ligne les noms des champs dans l'ordre des lignes qui suiveront.<br>
      Pour rappel, voici les champs existants dans Ginger: 
      <em>nom, prenom, mail, promo, filiere, badge_uid, expiration_badge, sexe</em><br>
      <small>Le login est le même que le mail, pas besoin de rentrer le login donc!</small><br>
      On a besoin d'avoir au moins du champ mail pour faire un match parfait. <small><em>On aurait pu essayer juste avec le nom et le prénom à la limite...</em></small>
    </p>
    <textarea name="import" id="textareaDataToImport" class="form-control" style="width:100%;height:400px"
        placeholder="mail;nom;prenom;promo;filiere;badge_uid<?= "\n"?>mail1@2015.icam.fr;nom1;prenom1;115;Integre;11111111<?= "\n"?>mail2@2015.icam.fr;nom2;prenom2;2015;Apprenti;22222222<?= "\n"?>mail3@icam.fr;nom3;prenom3;;Permanant;33333333"><?= (isset($textareaValue))?$textareaValue:'' ?></textarea>
  </div>
  <div class="form-actions">
      <button class="btn btn-primary" type="submit">Save changes</button>
      &nbsp;
      <button class="btn" type="reset">Cancel</button>
  </div>
</form>

<?php
  include 'includes/footer.php';
?>