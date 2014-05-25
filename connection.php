<?php
  require_once 'includes/_header.php';


if(!empty($_POST['email']) && !empty($_POST['password'])){ // Si des infos sont demandées et que les champs email et password sont non vides
    $email    = $_POST['email'];
    $password = md5($_POST['password']);

    //On vérifie si l'utilisateur existe
    $return = $DB->queryFirst('SELECT * FROM users_admin WHERE email = :email', array('email'=>$email)); // on prend le tout premier résultats et on le met sous forme array
    if(isset($return['password'],$return['email']) && $return['password']==$password){//Si il existe, on récupère email et password
        if($return['online'] == 1){ // si l'utilisateur est actif dans la BDD
            $_SESSION['Auth'] = array();
            $_SESSION['Auth'] = $return;
            Functions::setFlash('Vous êtes maintenant connecté','success'); // alors on le connecte
            header('Location:index.php');exit; // et on le redirige vers la page index.php (de laquelle il sera sorti s'il n'est pas admin)
        }else{
            Functions::setFlash('<strong>Votre compte n\'est pas actif !</strong><br/>Veuillez attendre que les administrateurs activent votre compte ou contactez nous !','info');
        } // Si utilisateur inactif, on met un message
    }else{
        //Si utilisateur inconnu
        Functions::setFlash('Identifiants incorects','error');
        $error_email = '';
        $error_password = '';
    }
    
}else if (!empty($_POST)) { // Si l'utilisateur n'a pas rempli tous les champs demandés
    Functions::setFlash('Veuillez remplir tous vos champs','error');
}
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title>Connexion</title> <!--afficher dans le titre de la page web Bonjour icam précédé de title-for-layout qui est préciser dans chaque page-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Site internet - Connexion">
    <meta name="author" content="Ithor">

    <!-- Le styles -->
    <link rel="stylesheet" href="css/jqueryui/style.css">
    <link href="css/bootstrap.css" rel="stylesheet">
    <!-- <link href="css/docs.css" rel="stylesheet"> -->
    <style type="text/css">
      body {
        padding-top: 40px;
        padding-bottom: 40px;
        background-color: #f5f5f5;
      }

      .form-signin {
        max-width: 300px;
        padding: 19px 29px 29px;
        margin: 0 auto 20px;
        background-color: #fff;
        border: 1px solid #e5e5e5;
        -webkit-border-radius: 5px;
           -moz-border-radius: 5px;
                border-radius: 5px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
      }
      .form-signin .form-signin-heading,
      .form-signin .checkbox {
        margin-bottom: 10px;
      }
      .form-signin input[type="text"],
      .form-signin input[type="password"] {
        font-size: 16px;
        height: auto;
        margin-bottom: 15px;
        padding: 7px 9px;
      }

    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le favicon (img du site ds le navigateur) -->
    <link rel="shortcut icon" href="img/favicon_IDiiL.ico">

    <script src="js/jquery.js"></script>
    <script src="js/jquery-ui.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/main.js"></script>
  </head>

  <body>

    <div class="container">
      <?= Functions::flash(); ?>
    <form action="connection.php" method="POST" class="form-signin"> <!-- on introduit un formulaire de connexion. Les données qu'il récupèrera, il les envoie à la page connection.php par la méthode POST-->
    <h2 class="form-signin-heading">Identifiez-vous !</h2> <!--titre en haut-->
        <div class="control-group <?php if(isset($error_email)){echo 'error';} ?>">
            <label class="control-label" for="email">Email :</label>
            <div class="controls">
            <input id="email" name="email" type="email" value="<?php if(isset($return['email'])){echo $return['email'];} ?>">
            <span class="help-inline"><?php if(isset($error_email)){ echo $error_email; } ?></span>
            </div>
        </div>

        <div class="control-group <?php if(isset($error_password)){echo 'error';} ?>">
            <label class="control-label" for="password">Password :</label>
            <div class="controls">
            <input id="password" name="password" type="password">
            <span class="help-inline"><?php if(isset($error_password)){ echo $error_password; } ?></span>
            </div>
        </div>

        <div class="control-group">
            <div class="controls">
                <button class="btn btn-primary" type="submit">Se connecter</button>
            </div>
        </div>               
    </form>
    <?php if (Functions::getConfig('inscriptions') == true): ?>
        <p><a href="register.php">Vous n'avez pas de compte ?</a></p>
    <?php endif ?>
  </body>
</html>