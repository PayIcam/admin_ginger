<?php
require_once 'includes/_header.php';
?><!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Administration Ginger - PayIcam">
    <meta name="author" content="Antoine Giraud">
    <link rel="shortcut icon" href="favicon.png">
    
    <title><?php if(isset($title_for_layout)){echo $title_for_layout.' - ';} ?><?= WEBSITE_TITLE; ?></title>
    
    <?php //* ?>
    <!-- build:css css/min.css -->
    <link rel="stylesheet" href="css/jqueryui/style.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/Aristo/Aristo.css">
    <link rel="stylesheet" href="js/colorpicker/jquery.colorpicker.css">
    <link rel="stylesheet" href="css/loaders.css">
    <link rel="stylesheet" href="css/style.css">
    <!-- endbuild -->
    <?php //*/ ?>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    
    <?php //* ?>
    <!-- build:js js/min.js -->
    <script src="js/jquery.js"></script>
    <script src="js/jquery-ui.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/colorpicker/jquery.colorpicker.js"></script>
    <script src="js/knob.js"></script>
    <script src="js/main.js"></script>
    <!-- endbuild -->
    <?php //*/ ?>
  </head>

  <body <?php if(isset($scrolSpy)){ echo ' data-spy="scroll" data-target=".subnav" data-offset="50"';} ?>>

    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?= HOME_URL; ?>">Admin Ginger</a>
        </div>
        <div class="navbar-collapse collapse">
          <?php if ((isset($title_for_layout) && ($title_for_layout != 'Maintenance' || ($title_for_layout == 'Maintenance' && $Auth->isAdmin())) ) || !isset($title_for_layout)): // Si on est sur la page de maintenance, on n'affiche pas les liens vers les autres parties du site.. A moins que l'on soit un admin.?>
          <ul class="nav navbar-nav">
            <li<?php if(Functions::isPage('index')) echo ' class="active"'; ?>><a href="index.php"><i class="glyphicon glyphicon-home glyphicon glyphicon-white"></i></a></li>
            <li><a href="http://barcafetlille.icam.fr" title="Retour Casper PayIcam">Retour PayIcam</a></li>
            <li class="dropdown<?php if(Functions::isPage('admin_liste_members','admin_edit_member')) echo ' active'; ?>" id="liste Icam PayIcam">
              <a href="#" data-toggle="dropdown" class="dropdown-toggle">Membres PayIcam <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li class="dropdown-header">Les Icam</li>
                <li><a href="admin_liste_members.php"><i class="glyphicon glyphicon-book"></i> Liste des membres</a></li>
                <li><a href="admin_edit_member.php"><i class="glyphicon glyphicon-plus"></i> Nouveau membre</a></li>
              </ul>
            </li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <?php if ($Auth->isLogged() || Config::getDbConfig('authentification') == false){if ($Auth->isAdmin()){ ?>
              <li class="dropdown<?php if(Functions::isPage('admin_liste_admins','admin_edit_admin','admin_parametres')) echo ' active'; ?>">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">Admin Site <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="index.php"><i class="glyphicon glyphicon-home"></i> Accueil administration</a></li>
                  <li class="divider"></li>
                  <li><a href="admin_liste_admins.php"><i class="glyphicon glyphicon-book"></i> Liste des utilisateurs</a></li>
                  <li><a href="admin_edit_admin.php"><i class="glyphicon glyphicon-plus"></i> Nouvel utilisateur</a></li>
                  <li class="divider"></li>
                  <li><a href="admin_parametres.php"><i class="glyphicon glyphicon-wrench"></i> Paramètres du Site</a></li>
                  <li class="divider"></li>
                  <li><a href="admin_test_websale.php"><i class="glyphicon glyphicon-credit-card"></i> Test Websale PayIcam</a></li>
                </ul>
              </li><?php } ?>
            <?php if($Auth->isLogged()){ ?>
              <!-- <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">Déconnection <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="logout.php"><i class="glyphicon glyphicon-log-out"></i> Log Only Admin Ginger Out</a></li>
                  <li><a href="logout.php?log_cas_out=1"><i class="glyphicon glyphicon-log-out"></i> Log Cas Out Too</a></li>
                </ul>
              </li> -->
              <li><a href="logout.php">Déconnection</a></li>
            <?php }}
            if(!$Auth->isLogged()){ ?>
              <li><a href="connection.php">Se Connecter</a></li>
            <?php } ?>
          </ul>
          <?php endif; ?>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container">
      <?= Functions::flash(); ?>