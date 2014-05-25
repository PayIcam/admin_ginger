<?php 
	require_once 'includes/_header.php';

	if(!Functions::isAdmin()){					// sécuriser l'accès
	    Functions::setFlash('<strong>Identification requise</strong> Vous ne pouvez accéder à cette page.','error');
	    header('Location:connection.php');exit;
	}
	if (isset($_GET['id'],$_POST['id']) && $_GET['id'] != $_POST['id']) {
		$_GET['id'] = $_POST['id'];
	}
	if (isset($_GET['id']) && $_GET['id'] != -1 && Functions::isUser($_GET['id'])) {
		// Cas où on édite un User
		$User = $DB->findFirst('users_admin',array('conditions' => array('id'=>$_GET['id'])));
		$title_for_layout = 'Editer l\'utilisateur #'.$User['id'];
		$img  = 'img/icons/user_edit.png';
	}else if (isset($_GET['id']) && $_GET['id'] != -1 && !Functions::isUser($_GET['id'])){
		// Cas où l'id donnée ne corresponds à aucun utilisateur
		Functions::setFlash('<strong>Erreur :</strong> Cet id ne correspond à aucuns users','error');
		header('Location:admin_liste_users.php');exit;
	}else if ((isset($_GET['id']) && $_GET['id'] == -1)){
		// Cas de l'ajout d'un nouvel utilisateur
		$_GET["id"] = -1;
		// création du User
			$UserTable = $DB->find('SHOW COLUMNS FROM users_admin');
			$User = array();
			foreach ($UserTable as $value) {
				$User[current($value)] = '';
			}
			$User['id'] = -1;
			$User['profil'] = 'inscrit';
		$title_for_layout = 'Ajouter un nouvel utilisateur';
		$img  = 'img/icons/user+.png';
	}else{
		header('Location:admin_edit_user.php?id=-1');exit;
	}

	$error['erreur'] = '';
	$profils = $DB->find('users_admin',array('fields'=>'profil','groupBy'=>'profil'));
	foreach ($profils as $key => $value) {
		$profils[$value] = $value;
		unset($profils[$key]);
	}
	if(!isset($profils['admin']))$profils['admin']='admin';
	if(!isset($profils['inscrit']))$profils['inscrit']='inscrit';
	if(!isset($profils['banned']))$profils['banned']='banned';

	if (isset ($_POST['id'],$_POST['nom'],$_POST['prenom'],$_POST['email'],$_POST['online'],$_POST['profil'])) {
		require_once 'includes/Forms.class.php';
		$form = new form();
		
		$validate = array(
			'prenom' => array('rule'=>'notEmpty','message' => 'Entrez votre prénom'),
			'nom'    => array('rule'=>'notEmpty','message' => 'Entrez votre nom'),
			'email'  => array('rule'=>'([a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4})','message' => 'Email non valide'),
			'profil' => array('rule'=>'(^('.implode('|', $profils).')$)','message' => 'Profil inexistant <em>Ps:Si vous voulez le rajouter, allez dans la Bdd.</em>')
	    ); $form->setValidates($validate);
	    $d = array(//récupération des data de $_POST pour les réafficher à terme
			'id'     => $_POST['id'],
			'prenom' => htmlspecialchars($_POST['prenom'], ENT_QUOTES, "UTF-8"),
			'nom'    => htmlspecialchars($_POST['nom'],    ENT_QUOTES, "UTF-8"),
			'email'  => htmlspecialchars($_POST['email'],  ENT_QUOTES, "UTF-8"),
			'online' => htmlspecialchars($_POST['online'], ENT_QUOTES, "UTF-8"),
			'profil' => htmlspecialchars($_POST['profil'], ENT_QUOTES, "UTF-8")
	    );
	    $User = $d;
	    $form->set($d);

	    if(!empty ($_POST['pass_new']) && !empty ($_POST['pass_new2']) ){
	        if (md5($_POST['pass_new']) == md5($_POST['pass_new2'])){
	            $d['password'] = md5($_POST['pass_new']);
	        }
	        else{
	            $form->errors['pass_new2'] = 'Les mots de passe ne concordent pas !';
	            $error['erreur'] = 1;
	        }
	    }
	    if (empty($error['erreur']) && $form->validates($d)) { // fin pré-traitement
	    	if($d['id'] == -1 && empty ($_POST['pass_new']) && empty ($_POST['pass_new2'])){
		    	$form->errors['pass_new'] = 'Il faut définir un mot de passe !';
		    	$form->errors['pass_new2'] = '';
		    	$error['erreur'] = 1;
		    }
	    	if (empty($error['erreur'])){
		        $id=$d['id'];
		        unset($d['id']);
		        if ($DB->findCount('users_admin','id='.$id) == 1) {
		        	$DB->save('users_admin',$d,array('update'=>array('id'=>$id)));
		        	Functions::setFlash("Changements effectués");
		        }else{
		        	$id = $DB->save('users_admin',$d,'insert');
		        	Functions::setFlash("Ajout de ".$d['prenom']." effectué");
		        }
		        
		        $User = $DB->findFirst('users_admin',array('conditions' => array('id'=>$id)));
		        
			    if(!empty ($d['password'])){
			        Functions::setFlash("Changement de mdp oppéré, nouveau mdp : ".htmlspecialchars($_POST['pass_new'], ENT_QUOTES, "UTF-8"));
			        unset($d['password']);
			    }
			}
	    }
	}//$_GET["id"]
?>

<?php include 'includes/header.php'; ?>
<?php require_once 'includes/Forms.class.php'; if(!isset ($form)){$form = new form();} ?>
<?php $form->set($User);  ?>
<?php // debug($_POST, '$_POST'); ?>

<h1 class="page-header clearfix">
	<div class="pull-left"><img src="<?= $img; ?>" alt=""> <?= $title_for_layout; ?></div>
	<div class="pull-right">
		<a href="admin_liste_users.php" class="btn btn-primary btn-large" onlick="">Retour liste</a>
		<a href="admin_edit_user.php?id=-1" class="btn btn-info btn-large">Ajouter</a>
	</div>
</h1>

<form action="admin_edit_user.php?id=<?= $_GET["id"]; ?>" method="post" class="form-horizontal">
    <?php if(isset ($error['general'])){echo '<p><em class="error">'.$error['general'].'</em></p><br/>';} ?>
    <fieldset>
        <legend>Informations Générales :</legend>
        <div>
        	<?= $form->input('id', 'hidden', array('value'=>$_GET["id"])); ?>
            <?= $form->input('nom','Nom : ', array('maxlength'=>"255")); ?>
            <?= $form->input('prenom','Prénom : ', array('maxlength'=>"255"/*, 'required'=>'1'*/)); ?>
            <?= $form->input('email','Email : ', array('maxlength'=>"255",'helper'=>'<em>Identifiant</em>')); ?>
            <?= $form->select('profil', 'Profil : ', array('data'=>$profils)); ?>
            <?= $form->input('online','En ligne :',array('type'=>'checkbox')); ?>
        </div>
    </fieldset>
    <fieldset class="password clear">
        <legend><?= (!empty($_GET["id"]) && $_GET["id"] != -1)?'Modifier son mot de passe :':'Le mot de passe :'; ?></legend>
	    <div class="pass">
	        <?= $form->input('pass_new',(!empty($_GET["id"]) && $_GET["id"] != -1)?'Nouveau mot de passe :':'Mot de passe :', array('type'=>'password','maxlength'=>"30")); ?>
	        <?= $form->input('pass_new2','Confirmez le :', array('type'=>'password','maxlength'=>"30")); ?>
	    </div>
    </fieldset>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        &nbsp;
        <button class="btn" type="reset">Annuler</button>
    </div>
</form>


<?php include 'includes/footer.php'; ?>