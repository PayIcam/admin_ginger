<?php 
  require_once 'includes/_header.php';

	if(!Functions::isAdmin()){					// sécuriser l'accès
	    Functions::setFlash('<strong>Identification requise</strong> Vous ne pouvez accéder à cette page.','error');
	    header('Location:connection.php');exit;
	}
?>
<?php 
  // vérifions si il y a une demande de supprission de post
  Functions::check_delete('user','Utilisateur');
  // vérifions si un user doit être activé ou non ?
  Functions::check_activation('user','Utilisateur');
  // Fonctions de masse
  Functions::check_global_actions('user','Utilisateur');
  // Recherche
  if (!empty($_POST['recherche']) || !empty($_POST['recherche2'])) {
    if (!empty($_POST['recherche2']) && empty($_POST['recherche'])) { // Si la recherche est passée du deuxième champ et que le premier est vide
      $_POST['recherche'] = $_POST['recherche2'];
    }// Sinon, on prend en compte le premier.
    $motclef = htmlspecialchars($_POST['recherche']);
    $q = array('motclef'=>'%'.$motclef. '%');
    if (!empty($motclef) && $motclef == 'online' || $motclef == 'offline' || $motclef == 'on' || $motclef == 'off') {
      $sql = 'SELECT * FROM users_admin WHERE online='.(($motclef == 'online' || $motclef == 'on')?'1':'0');
    }else{
      $sql = 'SELECT * FROM users_admin WHERE prenom like :motclef or nom like :motclef or email like :motclef or profil like :motclef';
    }
    $Administrateurs = $DB->query($sql, $q);
    $sizeOfAdministrateurs = sizeof($Administrateurs);
  }else{
    $Administrateurs = $DB->find('users_admin');
    $sizeOfAdministrateurs = sizeof($Administrateurs);
    // debug($Administrateurs, 'Administrateurs');
  }
?>

<?php $title_for_layout = 'Liste des users'; ?>
<?php $required_script[] = 'admin_search_users.js' ?>
<?php include 'includes/header.php'; ?>
<h1 class="page-header clearfix">
  <div class="pull-left"><img src="img/icons/contact.png" alt=""> Liste des Administrateurs</div>
  <div class="pull-right"><a href="admin_edit_user.php" class="btn btn-info btn-large">Ajouter</a></div>
</h1>

<form action="admin_liste_users.php" method="POST">
<div class="clearfix">
  <p class="actions form-inline pull-left">
    <select name="action" id="action1" class="span2">
      <option selected="selected" value="-1">Action Groupée</option>
      <optgroup label="Activation, désactivation">
        <option value="online">Passer en ligne</option>
        <option value="offline">Passer hors ligne</option>
      </optgroup>
      <option value="delete">Supprimer </option>
    </select>
    <button class="btn" type="submit">Appliquer</button>
  </p>
  <p class="pull-left" style="margin-left:15px;">
    <input class="input-medium search-query" id="recherche" placeholder="Rechercher ..." name="recherche" type="text" value="<?php if(!empty($motclef)) echo $motclef; ?>">
    <button class="btn" type="submit">Search</button>
  </p>
  <p class="pull-right">
    <em><?= $sizeOfAdministrateurs; ?> Utilisateurs</em>
  </p>
</div>
<table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th><input onclick="toggleChecked(this.checked)" class="checkbox" type="checkbox"></th>
        <th>Id</th>
        <th>Prenom</th>
        <th>Nom</th>
        <th><i class="icon-envelope"></i> Email</th>
        <th><i class="icon-user"></i> Profil</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="resultat">
      <?php foreach ($Administrateurs as $user): ?>
      	<tr>
          <td>
            <input id="user_<?= $user['id']; ?>" class="checkbox" type="checkbox" value="<?= $user['id']; ?>" name="users[]">
          </td>
      		<td>
            <span class="badge badge-<?= ($user['online']==1)?'success':'inverse';?>" title="<?= ($user['online']==1)?'User actif':'User inactif';?>">
              <?= $user['id'] ?>
            </span>
          </td><?php //if (!empty($motclef)){ ob_start(); }; ?>
          <td><?= (empty($motclef))? $user['prenom']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $user['prenom']); ?></td>
          <td><?= (empty($motclef))? $user['nom']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $user['nom']); ?></td>
          <td><?= (empty($motclef))? $user['email']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $user['email']); ?></td>
      		<td>
            <i class="<?php
                    if($user['profil']=='admin')
                      echo 'icon-certificate';
                    else if($user['profil']=='inscrit')
                      echo 'icon-ok-circle';
                    else echo 'icon-ban-circle'; 
            ?>" title="<?= $user['profil'] ?>"></i>
            <?= (empty($motclef))? $user['profil']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $user['profil']); ?>
          </td>
          <td>
            <div class="pull-right">
              <?php if ($user['online']==1){ ?>
                <a href="admin_liste_users.php?disactivate_user=<?= $user['id']; ?>" title="Désactiver le compte"><i class="icon-ban-circle"></i></a> |
              <?php }else{ ?>
                <a href="admin_liste_users.php?activate_user=<?= $user['id']; ?>" title="Activer le compte"><i class="icon-ok-sign"></i></a> |
              <?php } ?>

              <a href="admin_edit_user.php?id=<?= $user['id']; ?>" title="Editer l'utilisateur #<?= $user['id']; ?>"><i class="icon-pencil"></i></a>
              <a href="admin_liste_users.php?del_user=<?= $user['id']; ?>" title="Supprimer l'utilisateur #<?= $user['id']; ?>" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');"><i class="icon-trash"></i></a>              
            </div>
          </td>
      	</tr>
      <?php endforeach; ?>
      <?php if (empty($Administrateurs)): ?>
        <tr>
          <td colspan="7">
            <em>Aucuns utilisateurs trouvés.</em>
          </td>
        </tr>
      <?php endif ?>
    </tbody>
    <?php if ($sizeOfAdministrateurs > 10): ?>
    <tfoot>
      <tr>
        <th><input onclick="toggleChecked(this.checked)" class="checkbox" type="checkbox"></th>
        <th>Id</th>
        <th>Prenom</th>
        <th>Nom</th>
        <th><i class="icon-envelope"></i> Email</th>
        <th><i class="icon-user"></i> Profil</th>
        <th>Actions</th>
      </tr>
    </tfoot>
    <?php endif ?>
</table>
<?php if ($sizeOfAdministrateurs > 10): ?>
<div class="clearfix">
  <p class="actions form-inline pull-left">
    <select name="action2" id="action2" class="span2">
      <option selected="selected" value="-1">Action Groupée</option>
      <optgroup label="Activation, désactivation">
        <option value="online">Passer en ligne</option>
        <option value="offline">Passer hors ligne</option>
      </optgroup>
      <option value="delete">Supprimer </option>
    </select>
    <button class="btn" type="submit">Appliquer</button>
  </p>
  <p class="pull-left" style="margin-left:15px;">
    <input class="input-medium search-query" id="recherche2" name="recherche2" type="text">
    <button class="btn" type="submit">Search</button>
  </p>
  <p class="pull-right">
    <em><?= $sizeOfAdministrateurs; ?> Utilisateurs</em>
  </p>
</div>
<?php endif ?>

</form>
<?php include 'includes/footer.php'; ?>