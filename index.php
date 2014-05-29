<?php 
	require_once 'includes/_header.php';
    $Auth->allow('member');
	$title_for_layout = 'Accueil administration';
	include 'includes/header.php'; // insertion du fichier header.php : entête, barre de navigation
?>
<div class="jumbotron">
    <h1>Administration</h1> <!--titre en haut -->
    <p>Bienvenue <?= ($Auth->isAdmin())?$Auth->user('prenom'):'Inconnu !' //Ecrire bienvenue + prénom de la personne connecté?> !</p>
    <?php if ($Auth->isAdmin()): ?>
        <p>
            <a href="logout.php" class="btn btn-primary btn-lg">
            Se déconnecter !
            </a>
        </p>
    <?php endif ?>
</div>

<div class="row clearfix">
    <div class="col-md-8">
        <div class="well board">
            <h1 class="page-header">Tableau de Bord</h1>
            <div class="row">
                <div class="col-sm-4 board-item">
                    <div class="board-links">
                        <ul class="list-unstyled">
                            <li><a href="admin_liste_members.php" title="Liste des membres"><i class="glyphicon glyphicon-list-alt"></i></a></li>
                            <li><a href="admin_edit_member.php" title="Ajouter un membre"><i class="glyphicon glyphicon-plus"></i></a></li>
                        </ul>
                    </div>
                    <a href="admin_liste_members.php" class="board-thumbnail thumbnail" title="Liste des membres"> 
                        <img src="img/icons/lequipe.jpg" alt="">
                        <h5>L'équipe</h5>
                    </a>
                </div>
                <?php if ($Auth->isAdmin()){ ?>
                <div class="col-md-4 board-item">
                    <div class="board-links">
                        <ul class="list-unstyled">
                            <li><a href="admin_liste_admins.php" title="Liste des Administrateurs"><i class="glyphicon glyphicon-list-alt"></i></a></li>
                            <li><a href="admin_edit_admin.php" title="Ajouter un Administrateurs"><i class="glyphicon glyphicon-plus"></i></a></li>
                        </ul>
                    </div>
                    <a href="admin_liste_admins.php" class="board-thumbnail thumbnail" title="Liste des Administrateurs"> 
                        <img src="img/icons/user.png" alt="">
                        <h5>Admins</h5>
                    </a>
                </div>
                <div class="col-md-4 board-item">
                    <a href="admin_parametres.php" class="board-thumbnail thumbnail" title="Paramètres du site (maintenance, inscriptions, ...">
                        <img src="img/icons/gear_48.png" alt="Paramètres">
                        <h5>Paramêtres du Site</h5>
                    </a>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="well">
            <h2 class="page-header">Quelques chiffres</h2>
            <table class="table">
                <tbody>
                    <tr>
                        <td><strong><?= $DB->findCount('users',[],'login'); ?></strong></td>
                        <td>&nbsp;</td>
                        <td>Membres PayIcam</td>
                    </tr>
                    <tr>
                        <td><strong><?= $DB->findCount('administrateurs'); ?></strong></td>
                        <td>&nbsp;</td>
                        <td>Administrateurs</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php';?>