<?php 
	require_once 'includes/_header.php';
	$title_for_layout = 'Accueil administration';
	include 'includes/header.php'; // insertion du fichier header.php : entête, barre de navigation
?>
<div class="hero-unit">
    <h1>Administration</h1> <!--titre en haut -->
    <p>Bienvenue <?= (Functions::isAdmin())?$_SESSION['Auth']['prenom']:'Inconnu !' //Ecrire bienvenue + prénom de la personne connecté?> !</p>
    <?php if (Functions::isAdmin()): ?>
        <p>
            <a href="logout.php" class="btn btn-primary btn-large">
            Se déconnecter !
            </a>
        </p>
    <?php endif ?>
</div>

<div class="row-fluid privatePage clearfix">
    <div class="span8 well pull-left" id="board">
	    <h1 class="page-header">Tableau de Bord</h1>
        <ul class="thumbnails pagination-centered">
            <li class="span4">
                <div class="links">
                    <ul class="unstyled">
                        <li><a href="admin_liste_members.php" title="Liste des membres"><i class="icon-list-alt"></i></a></li>
                        <li><a href="admin_edit_member.php" title="Ajouter un membre"><i class="icon-plus"></i></a></li>
                    </ul>
                </div>
                <a href="admin_liste_members.php" class="thumbnail" title="Liste des membres"> 
                    <img src="img/icons/lequipe.jpg" alt="">
                    <h5>L'équipe</h5>
                </a>
            </li>
        </ul>
        <hr>
        <ul class="thumbnails pagination-centered">
            <li class="span4">
                <div class="links">
                    <ul class="unstyled">
                        <li><a href="admin_liste_users.php" title="Liste des Administrateurs"><i class="icon-list-alt"></i></a></li>
                        <li><a href="admin_edit_user.php" title="Ajouter un Administrateurs"><i class="icon-plus"></i></a></li>
                    </ul>
                </div>
                <a href="admin_liste_users.php" class="thumbnail" title="Liste des Administrateurs"> 
                    <img src="img/icons/user.png" alt="">
                    <h5>Admins</h5>
                </a>
            </li>
            <li class="span4">
                <a href="admin_parametres.php" class="thumbnail" title="Paramètres du site (maintenance, inscriptions, ...">
                    <img src="img/icons/gear_48.png" alt="Paramètres">
                    <h5>Paramêtres du Site</h5>
                </a>
            </li>
        </ul>
    </div>
    <div class="span4 well pull-right">
        <h1 class="page-header">Quelques chiffres</h1>
        <table class="table">
            <!-- <caption><h2>Contenu du site</h2></caption> -->
            <tbody>
                <tr>
                    <td><strong><?= current($DB->queryFirst('SELECT COUNT(*) FROM users')); ?></strong></td>
                    <td>&nbsp;</td>
                    <td>Membres PayIcam</td>
                </tr>
                <tr>
                    <td><strong><?= $DB->findCount('users_admin'); ?></strong></td>
                    <td>&nbsp;</td>
                    <td>Administrateurs</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php';?>