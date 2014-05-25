<?php
  require_once 'includes/_header.php';
?><?php
if(isset($_GET['motclef']) && Functions::isAdmin()){
    

    $motclef = $_GET['motclef'];
    $q = array('motclef'=>'%'.$motclef. '%');
    if (!empty($motclef) && $motclef == 'online' || $motclef == 'offline' || $motclef == 'on' || $motclef == 'off') {
        $sql = 'SELECT * FROM users_admin WHERE online='.(($motclef == 'online' || $motclef == 'on')?'1':'0');
    }else if (!empty($motclef)) {
        $sql = 'SELECT * FROM users_admin WHERE prenom like :motclef or nom like :motclef or email like :motclef or profil like :motclef';
    }else{
        $sql = 'SELECT * FROM users_admin';
    }
    $Administrateurs = $DB->query($sql, $q);
    $count = sizeof($Administrateurs);

    if($count){
      foreach ($Administrateurs as $user){ ?>
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
      <a href="admin_liste_users.php?del_user=<?= $user['id']; ?>" title="Supprimer l'utilisateur #<?= $user['id']; ?>"><i class="icon-trash"></i></a>              
    </div>
  </td>
</tr>
            <?php
        }
    }else{?>
        <tr>
          <td colspan="7">
            <em>Aucuns utilisateurs trouvés.</em>
          </td>
        </tr>
    <?php }
}
?><?php

?>