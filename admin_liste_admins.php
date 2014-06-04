<?php 
  require_once 'includes/_header.php';
  $Auth->allow('admin');

  require_once 'class/Admin.class.php';
  // vérifions si il y a une demande de suppréssion de post
  Admin::check_delete();
  Admin::check_global_actions();

  // -------------------- Création de la liste des Admins -------------------- //
  require_once 'class/ListAdmins.class.php';
  $ListAdmins = new ListAdmins($_POST);
  if ((isset($_GET['page']) && $_GET['page'] != $ListAdmins->page) || (isset($_POST['page']) && $_POST['page'] != $ListAdmins->page)) {
    header('Location:admin_liste_admins.php');exit;
  }

  $nom_for_layout = 'Liste des membres';
  $js_for_layout[] = 'admin_search_admin.js';
  include 'includes/header.php';
?>

<h1 class="page-header clearfix">
  <div class="pull-left"><span class="glyphicon glyphicon-book"></span> Liste des membres</div>
  <div class="pull-right">
    <a id="export" href="export_liste_admins.php" class="btn btn-primary btn-lg" onlick="">Exporter</a>
    <a href="admin_edit_admin.php" class="btn btn-info btn-lg">Ajouter</a>
  </div>
</h1>
<?php 
  require_once 'includes/Forms.class.php'; if(!isset ($form)){$form = new form();}
  $form->set($ListAdmins->getListFormParams());
  /*if (!isset($form->data['options']['selectAllTypes']))
    $form->data['options']['selectAllTypes'] = 1;
  if (isset($form->data['options']['selectAllTypes']) &&  empty($form->data['options']['type'])) {
    $form->data['options']['selectAllTypes'] = 1;
  }
  if (isset($form->data['options']['selectAllTypes'],$form->data['options']['type']) && $form->data['options']['selectAllTypes'] == 0 && count($form->data['options']['type']) == count(Participant::$types))
    $form->data['options']['type'] = array();
  elseif (isset($form->data['options']['selectAllTypes'],$form->data['options']['type']) && $form->data['options']['selectAllTypes'] == 1)
    $form->data['options']['type'] = Participant::$types;//*/
?>
<div id="post"></div>
<form id="form" action="admin_liste_admins.php" method="POST">
  <?= $form->input('page', 'hidden', array('value'=>$ListAdmins->page)); ?>
  <div class="clearfix"><?= $ListAdmins->getActionsGroupees(1); ?></div>
  <div class="pagination-container"><?= $ListAdmins->getPagination(); ?></div>
  <?php /* if ($Auth->isAdmin()): ?>
  <div class="well" id="FormRechercheAvancee" style="display:none;">
    <h2 class="page-header" style="margin:10px auto;">Recherche Avancée</h2>
    <div class="row">
      <div>
        <h3>Type :</h3>
        <p><?= $form->input('options[selectAllTypes]','Tous les types',array('type'=>'checkbox','id'=>'selectAllTypes','checkboxNoClassControl'=>1,'selected'=>'selected')); ?></p>
        <p><?php
            echo $form->simpleSelect('options[type][]',array('data'=>Admin::$types,'multiple'=>'multiple','id'=>'selectTypes','style'=>"height:200px;",'selected'=>((isset($form->data['options']['selectAllTypes']) && $form->data['options']['selectAllTypes'] == 1) || Admin::getTypesCount() <= count($form->data['options']['type']))?'all':$form->data['options']['type']) );
        ?></p>
      </div>
    </div>
    <div style="margin-bottom: 0;text-align: center;">
      <div class="row">
        <div class="col-md-10">
          <button class="btn btn-primary btn-lg" type="submit" style="width: 100%;">Rechercher</button> 
        </div>
        <div class="col-md-2">
          <a href="admin_liste_admins.php" id="resetSearchAdvancedForm" class="btn btn-lg"  style="width: 100%;">Reset</a> 
        </div>
      </div>
    </div>
  </div>
  <?php endif;//*/ ?>
  <table class="table table-bordered table-striped" id="adminsList">
      <thead>
        <?= $ListAdmins->getTHead(); ?>
      </thead>
      <tbody id="resultat">
        <?= $ListAdmins->getAdminAsTr(); ?>
      </tbody>
      <?php if ($ListAdmins->countAdmins > 10): ?>
      <tfoot>
        <?= $ListAdmins->getTHead(); ?>
      </tfoot>
      <?php endif ?>
  </table>
  <?php if ($ListAdmins->countAdmins > 10): ?>
    <div class="pagination-container"><?= $ListAdmins->getPagination(); ?></div>
    <div class="clearfix"><?= $ListAdmins->getActionsGroupees(2); ?></div>
  <?php endif ?>
</form>

<?php
  // Functions::tablesorter('adminsList','[3,1],[2,0]','0: {sorter: false},7: {sorter: false}');

  include 'includes/footer.php';
?>