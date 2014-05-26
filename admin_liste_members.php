<?php 
  require_once 'includes/_header.php';

  if(!Functions::islog()){          // sécuriser l'accès
      Functions::setFlash('<strong>Identification requise</strong> Vous ne pouvez accéder à cette page.','danger');
      header('Location:connection.php');exit;
  }
  require_once 'class/Member.class.php';
  // vérifions si il y a une demande de suppréssion de post
  Member::check_delete();
  Member::check_global_actions();

  // -------------------- Création de la liste des Members -------------------- //
  require_once 'class/ListMembers.class.php';
  $ListMembers = new ListMembers($_POST);
  if ((isset($_GET['page']) && $_GET['page'] != $ListMembers->page) || (isset($_POST['page']) && $_POST['page'] != $ListMembers->page)) {
    header('Location:admin_liste_members.php');exit;
  }

  $nom_for_layout = 'Liste des membres';
  $required_script[] = 'admin_search_member.js';
  include 'includes/header.php';
?>

<h1 class="page-header clearfix">
  <div class="pull-left"><img src="img/icons/contact.png" alt=""> Liste des membres</div>
  <div class="pull-right">
    <a id="export" href="export_liste_members.php" class="btn btn-primary btn-lg" onlick="">Exporter</a>
    <a href="admin_edit_member.php" class="btn btn-info btn-lg">Ajouter</a>
  </div>
</h1>
<?php 
  require_once 'includes/Forms.class.php'; if(!isset ($form)){$form = new form();}
  $form->set($ListMembers->getListFormParams());
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
<form id="form" action="admin_liste_members.php" method="POST">
  <?= $form->input('page', 'hidden', array('value'=>$ListMembers->page)); ?>
  <div class="clearfix"><?= $ListMembers->getActionsGroupees(1); ?></div>
  <div class="pagination-container"><?= $ListMembers->getPagination(); ?></div>
  <?php /* if (Functions::isAdmin()): ?>
  <div class="well" id="FormRechercheAvancee" style="display:none;">
    <h2 class="page-header" style="margin:10px auto;">Recherche Avancée</h2>
    <div class="row">
      <div>
        <h3>Type :</h3>
        <p><?= $form->input('options[selectAllTypes]','Tous les types',array('type'=>'checkbox','id'=>'selectAllTypes','checkboxNoClassControl'=>1,'selected'=>'selected')); ?></p>
        <p><?php
            echo $form->simpleSelect('options[type][]',array('data'=>Member::$types,'multiple'=>'multiple','id'=>'selectTypes','style'=>"height:200px;",'selected'=>((isset($form->data['options']['selectAllTypes']) && $form->data['options']['selectAllTypes'] == 1) || Member::getTypesCount() <= count($form->data['options']['type']))?'all':$form->data['options']['type']) );
        ?></p>
      </div>
    </div>
    <div style="margin-bottom: 0;text-align: center;">
      <div class="row">
        <div class="col-md-10">
          <button class="btn btn-primary btn-lg" type="submit" style="width: 100%;">Rechercher</button> 
        </div>
        <div class="col-md-2">
          <a href="admin_liste_members.php" id="resetSearchAdvancedForm" class="btn btn-lg"  style="width: 100%;">Reset</a> 
        </div>
      </div>
    </div>
  </div>
  <?php endif;//*/ ?>
  <table class="table table-bordered table-striped" id="membersList">
      <thead>
        <?= $ListMembers->getTHead(); ?>
      </thead>
      <tbody id="resultat">
        <?= $ListMembers->getMemberAsTr(); ?>
      </tbody>
      <?php if ($ListMembers->countMembers > 10): ?>
      <tfoot>
        <?= $ListMembers->getTHead(); ?>
      </tfoot>
      <?php endif ?>
  </table>
  <?php if ($ListMembers->countMembers > 10): ?>
    <div class="pagination-container"><?= $ListMembers->getPagination(); ?></div>
    <div class="clearfix"><?= $ListMembers->getActionsGroupees(2); ?></div>
  <?php endif ?>
</form>

<?php
  // Functions::tablesorter('membersList','[3,1],[2,0]','0: {sorter: false},7: {sorter: false}');

  include 'includes/footer.php';
?>