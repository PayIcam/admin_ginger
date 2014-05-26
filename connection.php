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
        Functions::setFlash('Identifiants incorects','danger');
        $errorLogin = true;
    }
    
}else if (!empty($_POST)) { // Si l'utilisateur n'a pas rempli tous les champs demandés
    Functions::setFlash('Veuillez remplir tous vos champs','danger');
}
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title>Connexion</title> <!--afficher dans le titre de la page web Bonjour icam précédé de title-for-layout qui est préciser dans chaque page-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Site internet Admin Ginger - Connexion">
    <meta name="author" content="Antoine Giraud">
    <link rel="shortcut icon" href="favicon.png">

    <!-- Le styles -->
    <link href="bootstrap-3.1.1/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
        body {
          padding-top: 40px;
          padding-bottom: 40px;
          background-color: #eee;
        }

        .form-signin {
          max-width: 330px;
          padding: 15px;
          margin: 0 auto;
        }
        .form-signin .form-signin-heading,
        .form-signin .checkbox {
          margin-bottom: 10px;
        }
        .form-signin .checkbox {
          font-weight: normal;
        }
        .form-signin .form-control {
          position: relative;
          height: auto;
          -webkit-box-sizing: border-box;
             -moz-box-sizing: border-box;
                  box-sizing: border-box;
          padding: 10px;
          font-size: 16px;
        }
        .form-signin .form-control:focus {
          z-index: 2;
        }
        .form-signin input[type="email"] {
          margin-bottom: -1px;
          border-bottom-right-radius: 0;
          border-bottom-left-radius: 0;
        }
        .form-signin input[type="password"] {
          margin-bottom: 10px;
          border-top-left-radius: 0;
          border-top-right-radius: 0;
        }
    </style>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le favicon (img du site ds le navigateur) -->
  </head>

  <body>

    <div class="container">
      <?= Functions::flash(); ?>
        <form class="form-signin<?= (isset($errorLogin))?' has-error':''; ?>" role="form" action="connection.php" method="POST">
            <h2 class="form-signin-heading">Identifiez-vous !</h2>
            <input type="email" name="email" class="form-control" placeholder="Email" required autofocus value="<?= (isset($return['email']))?$return['email']:''; ?>">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <button class="btn btn-lg btn-primary btn-block" type="submit">Se connecter</button>
        </form>
    <?php if (Functions::getConfig('inscriptions') == true): ?>
        <p><a href="register.php">Vous n'avez pas de compte ?</a></p>
    <?php endif ?>
  </body>
</html>