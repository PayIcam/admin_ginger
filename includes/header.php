<?php
require_once 'includes/_header.php';
?><!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Administration du site internet d'IDiiL">
    <meta name="author" content="Antoine Giraud">
    <link rel="shortcut icon" href="img/favicon_IDiiL.ico">
    
    <title><?php if(isset($title_for_layout)){echo $title_for_layout.' - ';} ?><?= WEBSITE_TITLE; ?></title>
    
    <?php //* ?>
    <!-- build:css css/min.css -->
    <link rel="stylesheet" href="css/jqueryui/style.css">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/bootstrap-responsive.css">
    <link rel="stylesheet" href="css/Aristo/Aristo.css">
    <link rel="stylesheet" href="js/colorpicker/jquery.colorpicker.css">
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
    <script src="js/bootstrap.js"></script>
    <script src="js/colorpicker/jquery.colorpicker.js"></script>
    <script src="js/main.js"></script>
    <!-- endbuild -->
    <?php //*/ ?>
  </head>

  <body <?php if(isset($scrolSpy)){ echo ' data-spy="scroll" data-target=".subnav" data-offset="50"';} ?>>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="index.php">Administration</a>
          <?php if ((isset($title_for_layout) && ($title_for_layout != 'Maintenance' || ($title_for_layout == 'Maintenance' && Functions::isAdmin())) ) || !isset($title_for_layout)): // Si on est sur la page de maintenance, on n'affiche pas les liens vers les autres parties du site.. A moins que l'on soit un admin.?>
          <div class="nav-collapse">
            <ul class="nav">
              <li<?php if(Functions::isPage('index')) echo ' class="active"'; ?>><a href="index.php"><i class="icon-home icon-white"></i></a></li>
              <li><a href="../" title="Retour au site">Retour au site</a></li>
              <li class="dropdown<?php if(Functions::isPage('admin_liste_members','admin_edit_member')) echo ' active'; ?>" id="savoir-faire">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">Divers <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li class="nav-header">L'équipe</li>
                  <li><a href="admin_liste_members.php"><i class="icon-list-alt"></i> Liste des membres</a></li>
                  <li><a href="admin_edit_member.php"><i class="icon-plus"></i> Nouveau membre</a></li>
                  <li class="divider"></li>
                </ul>
              </li>
            </ul>
            <ul class="nav pull-right">
              <?php if (Functions::islog() || Functions::getConfig('authentification') == false){if (Functions::isAdmin()){ ?>
                <li class="dropdown<?php if(Functions::isPage('admin_liste_users','admin_edit_user','admin_parametres')) echo ' active'; ?>" id="admin">
                  <a data-toggle="dropdown" class="dropdown-toggle" href="#">Admin Site <b class="caret"></b></a>
                  <ul class="dropdown-menu">
                    <li><a href="index.php"><i class="icon-home"></i> Accueil administration</a></li>
                    <li class="divider"></li>
                    <li><a href="admin_liste_users.php"><i class="icon-user"></i> Liste des utilisateurs</a></li>
                    <li><a href="admin_edit_user.php"><i class="icon-plus"></i> Nouvel utilisateur</a></li>
                    <li class="divider"></li>
                    <li><a href="admin_parametres.php"><i class="icon-wrench"></i> Paramètres du Site</a></li>
                  </ul>
                </li><?php } ?>
              <?php if(Functions::islog()){ ?>
              <li class="divider-vertical"></li>
              <li><a href="logout.php">Se Déconnecter</a></li>
              <?php }}
              if(!Functions::islog()){ ?>
              <li class="divider-vertical"></li>
              <li><a href="connection.php">Se Connecter</a></li>
              <?php } ?>
            </ul>
          </div><!--/.nav-collapse -->
          <?php endif; ?>
        </div>
      </div>
    </div>


    <div class="container">
      <?= Functions::flash(); ?>