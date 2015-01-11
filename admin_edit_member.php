<?php 
	require_once 'includes/_header.php';
	$Auth->allow('member');
	require 'vendor/autoload.php';

	if (isset($_GET['login'],$_POST['login']) && $_GET['login'] != $_POST['login']) {
		$_GET['login'] = $_POST['login'];
	}

	require_once 'class/Member.class.php';

	if (isset($_GET['login']) && $_GET['login'] != -1 && Member::isMember($_GET['login'])) {
		$Member = new Member($_GET['login']);
		// Cas où on édite un User
		$nom_for_layout = 'Editer le membre <small>#'.$Member->login.'</small>';
	}else if (isset($_GET['login']) && $_GET['login'] != -1 && !Member::isMember($_GET['login'])){
		// Cas où l'login donnée ne corresponds à aucun utilisateur
		Functions::setFlash('<strong>Erreur :</strong> Ce login ne correspond à aucun member','danger');
		header('Location:admin_liste_members.php');exit;
	}else if ((isset($_GET['login']) && $_GET['login'] == -1)){
		$Member = new Member();
		// Cas de l'ajout d'un nouvel utilisateur
		$_GET["login"] = -1;
		$nom_for_layout = 'Ajouter un nouveau membre';
	}else{
		header('Location:admin_edit_member.php?login=-1');exit;
	}
	if (isset ($_POST['login'],$_POST['nom'],$_POST['prenom'])) {
		require_once 'includes/Forms.class.php';
		$form = new form();
		$validate = array(
			'nom'    => array('rule'=>'notEmpty','message' => 'Entrez votre nom'),
			'prenom' => array('rule'=>'notEmpty','message' => 'Entrez votre prénom'),
			'mail' => array('rule'=>'^[a-z-]+[.]+[a-z-]+[.a-z-]?@(mgf\.)?([0-9]{4}[.])?icam[.]fr$','message' => 'Entrez un email Icam valide !')
	    );
	    $form->setValidates($validate);

	    $d = $Member->checkForm($_POST); // $_POST for invite table : 'login','slug','nom','content','order'
	    $form->set($d);
	    if ($form->validates($d)) { // fin pré-traitement
	    	if ($d['login'] == -1 && current($DB->queryFirst('SELECT COUNT(*) FROM users WHERE login = :login',array('login'=>$d['mail'])))) {
	    		$form->errors['mail'] = 'Utilisateur déjà existant !!';
	    	}elseif ($d['login'] != -1 && $d['login'] != $d['mail'] && current($DB->queryFirst('SELECT COUNT(*) FROM users WHERE login = :login',array('login'=>$d['mail']))) ) {
	    		$form->errors['mail'] = 'Utilisateur déjà existant !! Vous ne pouvez pas changer vers le login/mail : '.$d['mail'];
	    	}elseif (!empty($d['badge_uid']) && current($DB->queryFirst('SELECT COUNT(*) FROM users WHERE login != :login AND badge_uid = :badge_uid',array('login'=>$d['login'],'badge_uid'=>$d['badge_uid']))) ) {
	    		$form->errors['badge_uid'] = 'Badge '.$d['badge_uid'].' déjà utilisé !';
	    	}else{
	        	$Member->save();
	    		header('Location:admin_edit_member.php?login='.$Member->login);exit;
	    	}
	    }
	}

	include 'includes/header.php';
	require_once 'includes/Forms.class.php'; if(!isset ($form)){$form = new form();}
	$form->set($Member->getAttrIdMember());
?>

<h1 class="page-header clearfix">
	<div class="pull-left"><span class="glyphicon glyphicon-user"></span> <?= $nom_for_layout; ?> </div>
	<div class="pull-right">
		<a href="admin_edit_member.php?login=-1" class="btn btn-info btn-lg">Ajouter</a>
		<a href="admin_liste_members.php" class="btn btn-primary btn-lg" onlick="">Retour liste</a>
	</div>
</h1>

<form action="admin_edit_member.php?login=<?= $_GET["login"]; ?>" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
	<div class="row">
	    <fieldset>
	        <legend>Informations générales :</legend>
        	<div>
        		<?= $form->input('login', 'hidden', array('value'=>$_GET["login"])); ?>
        		<?= $form->input('token', 'hidden', array('value'=>Auth::generateToken())); ?>
        	    <?= $form->input('mail','Email : ', array('maxlength'=>"105",'helper'=>'<em>Identifiant</em>')); ?>
        	    <?= $form->input('nom','Nom : ', array('maxlength'=>"55")); ?>
        	    <?= $form->input('prenom','Prénom : ', array('maxlength'=>"55")); ?>
        	</div>
	    </fieldset>
    	<fieldset>
    	    <legend>Badge :</legend>
    	    <div>
        	    <?= $form->input('badge_uid','Badge UID : ', array('maxlength'=>"20",'class'=>"has-warning",
        	    	'input-group-prepend'=>'<span id="tag-ctrl" class="input-group-addon"><span class="glyphicon glyphicon-tag"></span></span>',
        	    	'input-group-append'=>'<span id="badgeuse-ctrl" class="input-group-addon has-warning"><span class="glyphicon glyphicon-hdd" title="Connexion au lecteur de carte : non établie"></span></span>'
    	    	)); ?>
        	    <?= $form->input('expiration_badge','Date Expication Badge : ', array('class'=>"datepicker",'input-group-addon'=>'date')); ?>
    	    </div>
    	</fieldset>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
	        <button class="btn btn-primary" type="submit">Save changes</button>
	        &nbsp;
	        <button class="btn btn-default" type="reset">Cancel</button>
        </div>
    </div>
</form>

<script type="text/javascript">
	var ginger_url = '<?= Config::get('ginger_url') ?>';
	var ginger_key = '<?= Config::get('ginger_key') ?>';
	window.JCAPPUCINO_APPLET =  'ws://localhost:9191/events';
	var login = $('input[name=login]').val();
	var $inputbadge_uid = $('#inputbadge_uid');
	var $Parent_inputbadge_uid = $inputbadge_uid.parent().parent().parent();
	var $badgeuseCtrl = $('#badgeuse-ctrl');
	var $TagCtrl = $('#tag-ctrl');
	var $TagCtrlInsideSpan = $('#tag-ctrl>span');
	var service = {
        callback: {}
    };

	service.connect = function() {
        var ws = new WebSocket(JCAPPUCINO_APPLET);

        var handle = function(event, message) {
            for(i in service.callback[event]) {
                service.callback[event][i](message);
            }
        }

        ws.onopen = function() {
            handle("onopen", "");
            $badgeuseCtrl.removeClass('has-warning').removeClass('has-error').addClass('has-success').attr('title', 'Connexion au lecteur de carte : établie');
        };

        ws.onerror = function() {
            handle("onerror", "");
            $badgeuseCtrl.removeClass('has-warning').removeClass('has-success').addClass('has-error').attr('title', 'Connexion au lecteur de carte : Erreur');
        };

        ws.onclose = function() {
            handle("onclose", "");
            $badgeuseCtrl.removeClass('has-error').removeClass('has-success').addClass('has-warning').attr('title', 'Connexion au lecteur de carte : Non établie');
        };

        ws.onmessage = function(message) {
            var data = message.data.split(':');
            var event = data[0], data = data[1];
            handle(event, data);
            $badgeuseCtrl.removeClass('has-warning').removeClass('has-error').addClass('has-success').attr('title', 'Connexion au lecteur de carte : établie');
        };

        service.ws = ws;
    }

    service.send = function(event, message) {
        service.ws.send(event + ':' + message);
    }

    service.subscribe = function(event, callback) {
        if(!service.callback[event]) {
            service.callback[event] = [];
        }
        service.callback[event].push(callback);
    }

    service.connect();

    // -------------------- Functions Appel Ginger -------------------- //
    var xhr = new Array();
	function checkXhr(xhrName){
	  if(xhr[xhrName]){
	    xhr[xhrName].abort();
	    delete xhr[xhrName];
	  }
	}
	function checkUsr(badge_id) {
		$Parent_inputbadge_uid.removeClass('has-warning').removeClass('has-error').removeClass('has-success');
		$TagCtrl.removeClass('has-warning').removeClass('has-error').removeClass('has-success');
		$TagCtrlInsideSpan.removeClass('glyphicon glyphicon-tag').addClass('loader loader-quart-tiny');
	  	checkXhr('badge_id');
		xhr['badge_id'] =  $.ajax({
			url: ginger_url+"badge/"+badge_id+"?key="+ginger_key,
			type: "GET",
			dataType: "json"
		}).done(function( server_response ) {
			$TagCtrlInsideSpan.addClass('glyphicon glyphicon-tag').removeClass('loader loader-quart-tiny');
			if (server_response.login.length > 0 && server_response.login != login) {
	    		$TagCtrl.addClass('has-error').attr('title', 'Badge dejà utilisé par '+server_response.login+' ! Trouvez en un autre.');
	    		$Parent_inputbadge_uid.addClass('has-error');
	    	}else if(server_response.login.length > 0 && server_response.login == login){
	    		$TagCtrl.addClass('has-success').attr('title', 'Vous ('+login+') utilisez déjà ce badge '+badge_id+' !');
	    	}else{
	    		$TagCtrl.addClass('has-success').attr('title', 'Badge inconnu - Vous pouvez l\'utiliser');
	    	};
		}).fail(function( jqXHR, textStatus ) {
			$TagCtrlInsideSpan.addClass('glyphicon glyphicon-tag').removeClass('loader loader-quart-tiny');
			// Si on a un parse Error ... c'est que l'on a pas reçu de réponse
			$TagCtrl.addClass('has-success').attr('title', 'Badge inconnu - Vous pouvez l\'utiliser');
		});
	}

    // -------------------- Vérification & Réception badge_ic -------------------- //

    service.subscribe("cardInserted", function(badge_id) {
    	checkUsr(badge_id);
        console.log('badge_id : '+badge_id);
        $inputbadge_uid.val(badge_id).animate({
			backgroundColor: "#d9edf7",
		    borderColor: "#31708F",
		    color: "#31708f",
		}, 500 ).animate({
			backgroundColor: "#fff",
		    borderColor: "#ccc",
		    color: "#555",
		}, 500 );
    });
</script>

<?php

include 'includes/footer.php'; ?>