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
                            <li><a href="admin_liste_members.php" title="Liste des membres"><span class="glyphicon glyphicon-list-alt"></span></a></li>
                            <li><a href="admin_edit_member.php" title="Ajouter un membre"><span class="glyphicon glyphicon-plus"></span></a></li>
                        </ul>
                    </div>
                    <a href="admin_liste_members.php" class="board-thumbnail thumbnail" title="Liste des membres"> 
                        <span class="glyphicon glyphicon-user" style="font-size: 40px;"></span>
                        <h5>Membres PayIcam</h5>
                    </a>
                </div>
                <?php if ($Auth->isAdmin()){ ?>
                <div class="col-sm-4 board-item">
                    <div class="board-links">
                        <ul class="list-unstyled">
                            <li><a href="admin_liste_admins.php" title="Liste des Administrateurs"><span class="glyphicon glyphicon-list-alt"></span></a></li>
                            <li><a href="admin_edit_admin.php" title="Ajouter un Administrateurs"><span class="glyphicon glyphicon-plus"></span></a></li>
                        </ul>
                    </div>
                    <a href="admin_liste_admins.php" class="board-thumbnail thumbnail" title="Liste des Administrateurs"> 
                        <span class="glyphicon glyphicon-tower" style="font-size: 40px;"></span>
                        <h5>Admins</h5>
                    </a>
                </div>
                <div class="col-sm-4 board-item">
                    <a href="admin_parametres.php" class="board-thumbnail thumbnail" title="Paramètres du site (maintenance, inscriptions, ...">
                        <span class="glyphicon glyphicon-cog" style="font-size: 40px;"></span>
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
            <?php //echo Functions::getProgressBar(25, 'info') ?>
            <?php /* echo Functions::getMultipleProgressBar(
                array('sum'=>100, 'all'=>array(
                    array('pourcent'=>1,'class'=>'info','title'=>'Promues : '.(1).'/'.(100)),
                    array('pourcent'=>25,'class'=>'warning','title'=>'Promues : '.(25).'/'.(100)),
                    array('pourcent'=>45,'class'=>'success','title'=>'Normales : '.(45).'/'.(100)),
                    array('pourcent'=>25,'class'=>'danger','title'=>'Promues : '.(25).'/'.(100))
                ))
            ); //*/ ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php';?>