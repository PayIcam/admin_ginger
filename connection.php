<?php
  require_once 'includes/_header.php';

if(!empty($_POST['email']) && !empty($_POST['password'])){
    if($Auth->login($_POST)){
      Functions::setFlash('Vous êtes maintenant connecté','success'); // alors on le connecte
      header('Location:index.php');exit;
    }else{
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
    <link href="css/bootstrap.min.css" rel="stylesheet">
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
    <?php if (Config::getDbConfig('inscriptions') == true): ?>
        <p><a href="register.php">Vous n'avez pas de compte ?</a></p>
    <?php endif ?>

    <script src="js/jquery.js"></script>
    <script src="js/jquery-ui.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>