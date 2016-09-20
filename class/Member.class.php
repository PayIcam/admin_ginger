<?php

require_once 'includes/_header.php';

/**
* Classe des participants au Gala
*/
class Member{
	private $login;
	private $attr;
	private $DB;

	public static $membersCount;

	public static $filieres = array(
		'Intégré'    => 'Intégré',
		'Apprentissage'   => 'Apprentissage',
		'Permanent'  => 'Permanent',
		'mgf'        => 'Master Génie Féroviaire',
		'f_continue' => 'Formation Continue'
	);


	const TABLE_NAME = 'users';
	const LOG_FILE   = 'log/log-members.txt';

	// -------------------- Constructeur -------------------- //
	function __construct($login=-1,$attr=array()){
		global $DB;
		$this->DB = $DB;
		// Nouveau participant
		$this->login = $login;
		$this->attr  = $attr;
		$this->attr  = self::checkMemberFields($this->getAttrPlusId());

		// Récupérer les infos d'un participant
		$this->update();
	}

	// -------------------- Méthodes de base -------------------- //
	public function save(){
        $this->login = self::saveMemberData($this->getAttrPlusId());
        $this->update();
        return $this->login;
	}
	public function saveFields($fieldsToSave){
		if (!($this->login > 0) || empty($fieldsToSave))
			return false;
		if (!is_array($fieldsToSave))
			$fieldsToSave = array($fieldsToSave);
		$MemberTableFields = Functions::getFirstVals($this->DB->find('SHOW COLUMNS FROM '.self::TABLE_NAME));
		$d = array();
		foreach ($fieldsToSave as $fieldName) {
			if (in_array($fieldName, $MemberTableFields))
				$d[$fieldName] = $this->$fieldName;
		}
		$this->DB->save(self::TABLE_NAME,$d,array('update'=>array('login'=>$this->login)));
        $this->update();
        // Functions::setFlash('Changements effectués (login='.$this->login.')');
        return true;
	}
	public function update(){
		if (!empty($this->login) && empty($this->attr['nom']) && self::isMember($this->login)) {
			$this->attr = $this->DB->findFirst(self::TABLE_NAME,array('conditions'=>array('login'=>$this->login)));
			if(empty($this->attr)){Functions::setFlash("Erreur, member inexistante...");return false;}
			if(isset($this->attr['login'])) unset($this->attr['login']);
		}
	}
	public function getAttrPlusId(){
		$return = !empty($this->attr)?$this->attr:array();
		$return['login'] = $this->login;
		return $return;
	}
	public function getAttrIdMember(){
		$return = !empty($this->attr)?$this->attr:array();
		$return['login'] = $this->login;
		return $return;
	}
	public function checkForm($POST){
		$this->attr = self::checkMemberFields($POST); // $POST for savoir-faire table : 'login','slug','nom','content','order'
		return $this->getAttrPlusId();
	}

	// ---------------------------------------- Static Functions ---------------------------------------- //

	static public function getMembersCount(){
		global $DB;
		if (empty(self::$membersCount)) {
			self::$membersCount = $DB->findCount(self::TABLE_NAME,'','login');
			return self::$membersCount;
		}else{
			return self::$membersCount;
		}
	}

	public static function search($array){
		global $DB;
		return $DB->find(self::TABLE_NAME,array('conditions'=>$array,'fields'=>'login'));
	}

	public static function saveMemberData($d){
		global $DB;
		if (empty($d['nom'])) return 0;
		if (empty($d['login'])) $d['login'] = 0;
		$login_exist = current($DB->queryFirst('SELECT COUNT(*) FROM '.self::TABLE_NAME.' WHERE login = :login',array('login'=>$d['login'])));

		if ($d['login'] !== 0 && $d['login'] !== -1 && $login_exist == 1) {
			$login = $d['login']; unset($d['login']);
        	$DB->save(self::TABLE_NAME,$d,array('update'=>array('login'=>$login)));
        	Functions::setFlash("Changements effectués");
        }else{
        	if ($d['login'] == -1)
        		$d['login'] = $d['mail'];
        	$login = $DB->save(self::TABLE_NAME,$d,'insert');
        	$login = $d['login'];
        	Functions::setFlash("Ajout de ".$d['nom']." effectué");
        	// self::ajouterAuxLog(date('Y-m-d H:i:s').' : Ajout Member #'.$login.' '.$d['nom']."\n");
        }
        return $login;
	}

	public static function isMember($login){
        global $DB;
        if (!empty($login) && $DB->findCount(self::TABLE_NAME,array('login'=>$login),'login') == 1) return true;
    }

	public static function checkMemberFields($attributes){
        global $DB;
        // création du champ attr
		$MemberTable = Functions::getFirstVals($DB->find('SHOW COLUMNS FROM '.self::TABLE_NAME));
		$attr = array();
		foreach ($MemberTable as $tabName) {
			if ($tabName == "online")
				$attr[$tabName] = 1;
			else
				$attr[$tabName] = '';
		}
		$login=$attributes['login'];
		unset($attr['login']);

		foreach ($attr as $key => $v) {
			if (isset($attributes[$key])) {
				$attr[$key] = htmlspecialchars($attributes[$key], ENT_QUOTES, "UTF-8");
			}
		}
		return $attr;
    }

    /**
    * Permet de supprimer une valeur dans la bdd.
    * @global PDO $DB
    * @param string $name
    * @param string $nom
    * @return boolean
    **/
    public static function check_delete($page=null){
        if (empty($page))
            $page = 'admin_liste_members.php';
        if(!empty($_GET['del_member'])){
            $nom = 'Member';
            self::deleteMember($_GET['del_member']);
            header('Location:'.$page);exit;
        }else if(!empty($_POST['action'])){
        	if ($_POST['action'] == -1 && isset($_POST['action2']) && $_POST['action2'] != -1)
                $_POST['action'] = $_POST['action2'];
            if ($_POST['action'] == 'delete') {
                $flash = array();
                foreach ($_POST['members'] as $login) {
                    self::deleteMember($login);
                }
            }
            return false;
        }
    }

    static function check_global_actions(){
		$name  = "member";
		$nom = "Member";
        global $DB;
        if (isset($_POST['action'],$_POST[$name.'s']) && ($_POST['action'] != -1 || ($_POST['action'] == -1 && isset($_POST['action2']) && $_POST['action2'] != -1))) {
            if ($_POST['action'] == -1 && isset($_POST['action2']) && $_POST['action2'] != -1) {
                $_POST['action'] = $_POST['action2'];
            }
            $flash = array();
            if ($_POST['action'] == 'delete') {
                foreach ($_POST[$name.'s'] as $login) {
                    if($DB->delete(self::TABLE_NAME,array('login'=>$login)))
                        $flash[] = '<strong>'.$nom.' #'.$login.' supprimé</strong>';
                }
            // }else if ($_POST['action'] == 'online') {
            //     foreach ($_POST[$name.'s'] as $login)
            //         if($DB->save(self::TABLE_NAME,array('online'=> 1),array('update'=>array('login'=>$login)))){
            //             $flash[] = '<strong>'.$nom.' #'.$login.' est en ligne</strong>';
            //     }
            // }else if ($_POST['action'] == 'offline'){
            //     foreach ($_POST[$name.'s'] as $login)
            //         if($DB->save(self::TABLE_NAME,array('online'=> 0),array('update'=>array('login'=>$login)))){
            //             $flash[] = '<strong>'.$nom.' #'.$login.' est hors ligne</strong>';
            //     }
            // }else if ($_POST['action'] == 'client'){
                // foreach ($_POST[$name.'s'] as $login)
                    // if($DB->save(self::TABLE_NAME,array('type'=> 'client'),array('update'=>array('login'=>$login)))){
                        // $flash[] = '<strong>'.$nom.' #'.$login.' est maintenant un client</strong>';
                // }
            }else if (strpos($_POST['action'], 'filiere_') === 0){
            	$filiere = str_replace('filiere_', '', $_POST['action']);
            	if (array_key_exists($filiere, Member::$filieres)) { // Si la filière est reconnue on l'enregistre à la troupe des user selectionnés
	                foreach ($_POST[$name.'s'] as $login)
	                    if($DB->save(self::TABLE_NAME,array('filiere'=> $filiere),array('update'=>array('login'=>$login)))){
	                        $flash[] = '<strong>'.$nom.' #'.$login.' est maintenant un '.$filiere.'</strong>';
	                }
            	}
            }else if (isset(self::$types[$_POST['action']]) && !empty(self::$types[$_POST['action']])){
                $type_value = self::$types[$_POST['action']];
                foreach ($_POST[$name.'s'] as $login)
                    if($DB->save(self::TABLE_NAME,array('type'=> $_POST['action']),array('update'=>array('login'=>$login)))){
                        $flash[] = '<strong>'.$nom.' #'.$login.' est '.$type_value.'</strong>';
                }
            }

            if(!empty($flash))Functions::setFlash(implode('<br/>', $flash),'success');
        }else{
            return false;
        }
    }

    public static function deleteMember($login){
    	global $DB;
    	if ($DB->findCount(self::TABLE_NAME,array('login'=>$login),'login') > 0) {
            $DB->delete(self::TABLE_NAME,array('login'=>$login));
            // self::ajouterAuxLog(date('Y-m-d h:m:s').' : Suppression Member #'.$login."\n");
            Functions::setFlash('<strong>Member #'.$login.' supprimée</strong>','success');
            return true;
        }else{
            Functions::setFlash('<strong>Member inconnu</strong>','danger');
            return false;
        }
    }

	static public function updateAllDataBase(){
		global $DB;
		// debug($DB->queryFirst('SELECT SUM(price) globalPrice FROM members'),'avant');
		// $MembersIds = Functions::getFirstVals($DB->find(self::TABLE_NAME,array('fields'=>array('login'))));
		// // debug($MembersIds,'$MembersIds');
		// $price = array();
		// foreach ($MembersIds as $login) {
		// 	$Member = new Member($login);
		// 	$Member->updatePrice();
		// 	$Member->saveFields('price');
		// 	$price[$login]=$Member->price;
		// }
		// debug($price,'$price');
		// debug($DB->queryFirst('SELECT SUM(price) globalPrice FROM members'),'après');
	}

	static public function getTypesCount(){
		$retour = 0;
		foreach (self::$types as $v) {
			$retour+=count($v);
		}
		return $retour;
	}

	/* -------------------- Export -------------------- */

    public static function ajouterAuxLog($msg){
        file_put_contents(self::LOG_FILE, $msg, FILE_APPEND);
    }

	// -------------------- Getters & Setters -------------------- //
	public function __get($var){
		if (!isset($this->$var)) {
			if (isset($this->attr[$var])) {
				return $this->attr[$var];
			}
		}else return $this->$var;
	}
	public function __set($var,$val){
		if (!isset($this->$var)) {
			if (isset($this->attr[$var])) {
				$this->attr[$var] = $val;
			}
		}else $this->$var = $val;
	}
}