<?php 
	require_once 'includes/_header.php';
	$Auth->allow('admin');
	require 'vendor/autoload.php';

	if (isset($_GET['id'],$_POST['id']) && $_GET['id'] != $_POST['id']) {
		$_GET['id'] = $_POST['id'];
	}

	require_once 'class/Admin.class.php';

	if (isset($_GET['id']) && $_GET['id'] != -1 && Admin::isAdmin($_GET['id'])) {
		$Admin = new Admin($_GET['id']);
		// Cas où on édite un User
		$nom_for_layout = 'Editer le membre <small>#'.$Admin->id.'</small>';
		$img  = 'img/icons/user_edit.png';
	}else if (isset($_GET['id']) && $_GET['id'] != -1 && !Admin::isAdmin($_GET['id'])){
		// Cas où l'id donnée ne corresponds à aucun utilisateur
		Functions::setFlash('<strong>Erreur :</strong> Ce id ne correspond à aucun admin','danger');
		header('Location:admin_liste_admins.php');exit;
	}else if ((isset($_GET['id']) && $_GET['id'] == -1)){
		$Admin = new Admin();
		// Cas de l'ajout d'un nouvel utilisateur
		$_GET["id"] = -1;
		$nom_for_layout = 'Ajouter un nouveau membre';
		$img  = 'img/icons/user+.png';
	}else{
		header('Location:admin_edit_admin.php?id=-1');exit;
	}
	if (isset ($_POST['id'],$_POST['nom'],$_POST['prenom'])) {
		require_once 'includes/Forms.class.php';
		$form = new form();
		$validate = array(
			'prenom' => array('rule'=>'notEmpty','message' => 'Entrez votre prénom'),
			'nom'    => array('rule'=>'notEmpty','message' => 'Entrez votre nom'),
			'email' => array('rule'=>'^[a-z-]+[.]+[a-z-]+@([0-9]{4}[.])?icam[.]fr$','message' => 'Entrez un email Icam valide !')
	    );
	    $form->setValidates($validate);

	    if(!empty ($_POST['pass_new']) && !empty ($_POST['pass_new2']) ){
	        if (md5($_POST['pass_new']) == md5($_POST['pass_new2'])){
	            $_POST['password'] = md5($_POST['pass_new']);
	            unset($_POST['pass_new'],$_POST['pass_new2']);
	        }
	        else{
	            $form->errors['pass_new2'] = 'Les mots de passe ne concordent pas !';
	            $error['erreur'] = 1;
	        }
	    }
	    $d = $Admin->checkForm($_POST); // $_POST for invite table : 'id','slug','nom','content','order'
	    $form->set($d);
	    if (empty($error['erreur']) && $form->validates($d)) { // fin pré-traitement
	    	if($d['id'] == -1 && empty($_POST['password']) && empty ($_POST['pass_new']) && empty ($_POST['pass_new2'])){
		    	$form->errors['pass_new'] = 'Il faut définir un mot de passe !';
		    	$form->errors['pass_new2'] = '';
		    }elseif ($d['id'] == -1 && current($DB->queryFirst('SELECT COUNT(*) FROM administrateurs WHERE email = :email',array('email'=>$d['email'])))) {
	    		$form->errors['email'] = 'Utilisateur déjà existant !!';
	    	}elseif ($d['id'] != -1 && current($DB->queryFirst('SELECT COUNT(*) FROM administrateurs WHERE email = :email AND id != :id',array('email'=>$d['email'],'id'=>$d['id']))) != 0 ) {
	    		$form->errors['email'] = 'Utilisateur déjà existant !! Vous ne pouvez pas changer vers l\'email : '.$d['email'];
	    	}else{
	        	$Admin->save();
	    		header('Location:admin_edit_admin.php?id='.$Admin->id);exit;
	    	}
	    }
	}

	include 'includes/header.php';
	require_once 'includes/Forms.class.php'; if(!isset ($form)){$form = new form();}
	$form->set($Admin->getAttrIdAdmin());

?>

<h1 class="page-header clearfix">
	<div class="pull-left"><img src="<?= $img; ?>" alt=""> <?= $nom_for_layout; ?> </div>
	<div class="pull-right">
		<a href="admin_edit_admin.php?id=-1" class="btn btn-info btn-lg">Ajouter</a>
		<a href="admin_liste_admins.php" class="btn btn-primary btn-lg" onlick="">Retour liste</a>
	</div>
</h1>

<form action="admin_edit_admin.php?id=<?= $_GET["id"]; ?>" method="post" class="form-horizontal" role="form">
	<div class="row">
	    <fieldset>
	        <legend>Informations générales :</legend>
        	<div>
        		<?= $form->input('id', 'hidden', array('value'=>$_GET["id"])); ?>
        		<?= $form->input('token', 'hidden', array('value'=>Auth::generateToken())); ?>
        	    <?= $form->input('email','Email : ', array('maxlength'=>"105",'helper'=>'<em>Identifiant</em>')); ?>
        	    <?= $form->input('nom','Nom : ', array('maxlength'=>"55")); ?>
        	    <?= $form->input('prenom','Prénom : ', array('maxlength'=>"55")); ?>
        	    <?= $form->input('online','En ligne :',array('type'=>'checkbox','selected'=>'online')); ?>
        	    <?= $form->select('role_id', 'Type : ', array('data'=>$Auth->getRoles())); ?>
        	</div>
	    </fieldset>
	    <fieldset class="password clear">
	        <legend><?= (!empty($_GET["id"]) && $_GET["id"] != -1)?'Modifier son mot de passe :':'Le mot de passe :'; ?></legend>
		    <div class="pass">
		        <?= $form->input('pass_new',(!empty($_GET["id"]) && $_GET["id"] != -1)?'Nouveau mot de passe :':'Mot de passe :', array('type'=>'password','maxlength'=>"55")); ?>
		        <?= $form->input('pass_new2','Confirmez le :', array('type'=>'password','maxlength'=>"55")); ?>
		    </div>
	    </fieldset>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
	        <button class="btn btn-primary" type="submit">Save changes</button>
	        &nbsp;
	        <button class="btn btn-default" type="reset">Cancel</button>
        </div>
    </div>
</form>

<?php include 'includes/footer.php'; ?>