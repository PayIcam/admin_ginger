<?php
  require_once 'includes/_header.php';
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
    </style>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le favicon (img du site ds le navigateur) -->
  </head>
  <body>
    <div class="container text-center">
      <?= Functions::flash(); ?>

      <?php if (!empty($_GET['erreur'])): ?>
        <h1>Erreur <?= $_GET['erreur'] ?></h1>
      <?php endif ?>
      
      <p>
      <?php
      switch($_GET['erreur'])
      {
         case '400':
         echo 'Échec de l\'analyse HTTP.';
         break;
         case '401':
         echo 'Le pseudo ou le mot de passe n\'est pas correct !';
         break;
         case '402':
         echo 'Le client doit reformuler sa demande avec les bonnes données de paiement.';
         break;
         case '403':
         echo 'Requête interdite !';
         break;
         case '404':
         echo 'La page que vous cherchez n\'existe pas (ou plus).';
         break;
         case '405':
         echo 'Méthode non autorisée.';
         break;
         case '500':
         echo 'Erreur interne au serveur ou serveur saturé.';
         break;
         case '501':
         echo 'Le serveur ne supporte pas le service demandé.';
         break;
         case '502':
         echo 'Mauvaise passerelle.';
         break;
         case '503':
         echo 'Service indisponible.';
         break;
         case '504':
         echo 'Trop de temps à la réponse.';
         break;
         case '505':
         echo 'Version HTTP non supportée.';
         break;
         case 'token':
         echo 'Votre Token a expiré.';
         break;
         default:
         echo 'Erreur !';
      }
      ?>
      </p>

        <p><a href="<?= HOME_URL; ?>" class="btn btn-primary">Accueil</a></p>
    <script src="js/jquery.js"></script>
    <script src="js/jquery-ui.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>