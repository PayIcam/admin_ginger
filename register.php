<?php 
  require_once 'includes/_header.php';

    $inscriptions = Config::getDbConfig('inscriptions');
    if ($inscriptions == false && !$Auth->isAdmin()) {
        Functions::setFlash('Les inscriptions sont actuellement fermées.','info');
        header('Location:index.php');exit;
    }

if(!empty($_POST) && strlen($_POST['prenom'])>4 && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $password = sha1($_POST['password']);
    // $token = sha1(uniqid(rand()));
    if (!$DB->findCount('administrateurs',array('email'=>$email))) {
        $q = array('nom'=>$nom, 'prenom'=>$prenom, 'email'=>$email, 'password'=>$password);
        $sql = 'INSERT INTO administrateurs (nom, prenom, email, password) VALUES (:nom, :prenom, :email, :password)';
        $DB->query($sql, $q);
        Functions::setFlash('Votre inscription a bien été prise ne compte, un administrateur va activer votre compte.<br/>Si cela tarde, n\'hésitez pas à prendre contact.');
        header('Location:connection.php');exit();
    }else{
        $error_email = ' Cet email est déjà pris !';
    }
    
    /*
    //Envoyer un mail pour la validation du compte
    $to = $email;
    $sujet = 'Activation de votre compte';
    $body = '
    Bonjour, veuillez activer votre compte en cliquant ici ->
    <a href="http://localhost/Tutoriels/PHP-Gestion_Membres/activate.php?token='.$token.'&email='.$to.'">Activation du compte</a>';
    $entete = "MIME-Version: 1.0\r\n";
    $entete .= "Content-type: text/html; charset=UTF-8\r\n";
    $entete .= 'From: bdsicamlille.fr ::' . "\r\n" .
    'Reply-To: contact@bds.icam.fr' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

    mail($to,$sujet,$body,$entete);
    */
}else{
    if(!empty($_POST) && strlen($_POST['prenom'])<4){
        $error_prenom = ' Votre prenom doit comporter au minimun 4 caracteres !';
    }
    if(!empty($_POST) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
        $error_email = ' Votre Email n\'est pas valide !';
    }

}

?>
<?php $title_for_layout = 'Inscriptions'; ?>
<?php include 'includes/header.php'; ?>
    <h1 class="page-header"><img src="img/icons/contact.png" alt=""> Formulaire d'inscription :</h1>
    <form action="register.php" method="POST" class="form-horizontal">
        <div class="form-group <?php if(isset($error_nom)){echo 'error';} ?>">
            <label class="col-sm-2 control-label" for="nom">Nom :</label>
            <div class="col-sm-10">
            <input class="form-control" id="nom" name="nom" type="text">
            <span class="help-block"><?php if(isset($error_nom)){ echo $error_nom; } ?></span>
            </div>
        </div>

        <div class="form-group <?php if(isset($error_prenom)){echo 'error';} ?>">
            <label class="col-sm-2 control-label" for="prenom">Prenom :</label>
            <div class="col-sm-10">
            <input class="form-control" id="prenom" name="prenom" type="text">
            <span class="help-block"><?php if(isset($error_prenom)){ echo $error_prenom; } ?></span>
            </div>
        </div>

        <div class="form-group <?php if(isset($error_email)){echo 'error';} ?>">
            <label class="col-sm-2 control-label" for="email">Email :</label>
            <div class="col-sm-10">
            <input class="form-control" id="email" name="email" type="email">
            <span class="help-block"><?php if(isset($error_email)){ echo $error_email; } ?></span>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label" for="password">Password :</label>
            <div class="col-sm-10">
            <input class="form-control" id="password" name="password" type="password">
            <span class="help-block"></span>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-primary" type="submit">Se connecter</button>
            </div>
        </div>               
    </form>
<?php include 'includes/footer.php'; ?>