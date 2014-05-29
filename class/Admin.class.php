<?php 

require_once 'includes/_header.php';

/**
* Classe des participants au Gala
*/
class Admin{
	private $id;
	private $attr;
	private $DB;

	public static $adminsCount;

	const TABLE_NAME = 'administrateurs';
	const LOG_FILE   = 'log/log-admins.txt';

	// -------------------- Constructeur -------------------- //
	function __construct($id=-1,$attr=array()){
		global $DB;
		$this->DB = $DB;
		// Nouveau participant
		$this->id = $id;
		$this->attr  = $attr;
		$this->attr  = self::checkAdminFields($this->getAttrPlusId());
		
		// Récupérer les infos d'un participant
		$this->update();
	}

	// -------------------- Méthodes de base -------------------- //
	public function save(){
        $this->id = self::saveAdminData($this->getAttrPlusId());
        $this->update();
        return $this->id;
	}
	public function saveFields($fieldsToSave){
		if (!($this->id > 0) || empty($fieldsToSave)) 
			return false;
		if (!is_array($fieldsToSave))
			$fieldsToSave = array($fieldsToSave);
		$AdminTableFields = Functions::getFirstVals($this->DB->find('SHOW COLUMNS FROM '.self::TABLE_NAME));
		$d = array();
		foreach ($fieldsToSave as $fieldName) {
			if (in_array($fieldName, $AdminTableFields))
				$d[$fieldName] = $this->$fieldName;
		}
		$this->DB->save(self::TABLE_NAME,$d,array('update'=>array('id'=>$this->id)));
        $this->update();
        // Functions::setFlash('Changements effectués (id='.$this->id.')');
        return true;
	}
	public function update(){
		if (!empty($this->id) && empty($this->attr['nom']) && self::isAdmin($this->id)) { 
			$this->attr = $this->DB->findFirst(self::TABLE_NAME,array('conditions'=>array('id'=>$this->id)));
			if(empty($this->attr)){Functions::setFlash("Erreur, admin inexistante...");return false;}
			if(isset($this->attr['id'])) unset($this->attr['id']);
		}
	}
	public function getAttrPlusId(){
		$return = !empty($this->attr)?$this->attr:array();
		$return['id'] = $this->id;
		return $return;
	}
	public function getAttrIdAdmin(){
		$return = !empty($this->attr)?$this->attr:array();
		$return['id'] = $this->id;
		return $return;
	}
	public function checkForm($POST){
		$this->attr = self::checkAdminFields($POST); // $POST for savoir-faire table : 'id','slug','nom','content','order'
		return $this->getAttrPlusId();
	}
	
	// ---------------------------------------- Static Functions ---------------------------------------- //

	static public function getAdminsCount(){
		global $DB;
		if (empty(self::$adminsCount)) {
			self::$adminsCount = $DB->findCount(self::TABLE_NAME,'','id');
			return self::$adminsCount;
		}else{
			return self::$adminsCount;
		}
	}

	public static function search($array){
		global $DB;
		return $DB->find(self::TABLE_NAME,array('conditions'=>$array,'fields'=>'id'));
	}

	public static function saveAdminData($d){
		global $DB;
		if (empty($d['nom'])) return 0;
		if (empty($d['id'])) $d['id'] = 0;
		$id_exist = current($DB->queryFirst('SELECT COUNT(*) FROM '.self::TABLE_NAME.' WHERE id = :id',array('id'=>$d['id'])));

		if ($d['id'] !== 0 && $d['id'] !== -1 && $id_exist == 1) {
			$id = $d['id']; unset($d['id']);
			if (empty($d['password'])) unset($d['password']);
        	$DB->save(self::TABLE_NAME,$d,array('update'=>array('id'=>$id)));
        	Functions::setFlash("Changements effectués");
        }else{
        	$id = $d['id']; unset($d['id']);
        	if (isset($d['login']) && $d['login'] == -1)
        		$d['login'] = $d['email'];
        	$id = $DB->save(self::TABLE_NAME,$d,'insert');
        	if (empty($id)) {
        		$id = $d['id'];	
        	}
        	Functions::setFlash("Ajout de ".$d['nom']." effectué");
        	// self::ajouterAuxLog(date('Y-m-d H:m:s').' : Ajout Admin #'.$id.' '.$d['nom']."\n");
        }
        return $id;
	}

	public static function isAdmin($id){
        global $DB;
        if (!empty($id) && $DB->findCount(self::TABLE_NAME,array('id'=>$id),'id') == 1) return true;
    }

	public static function checkAdminFields($attributes){
        global $DB;
        // création du champ attr
		$AdminTable = Functions::getFirstVals($DB->find('SHOW COLUMNS FROM '.self::TABLE_NAME));
		$attr = array();
		foreach ($AdminTable as $tabName) {
			if ($tabName == "online")
				$attr[$tabName] = 1;
			else
				$attr[$tabName] = '';
		}
		$id=$attributes['id'];
		unset($attr['id']);

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
            $page = 'admin_liste_admins.php';
        if(!empty($_GET['del_admin'])){
            $nom = 'Admin';
            self::deleteAdmin($_GET['del_admin']);
            // header('Location:'.$page);exit;
        }else if(!empty($_POST['action'])){
        	if ($_POST['action'] == -1 && isset($_POST['action2']) && $_POST['action2'] != -1)
                $_POST['action'] = $_POST['action2'];
            if ($_POST['action'] == 'delete') {
                $flash = array();
                foreach ($_POST['admins'] as $id) {
                    self::deleteAdmin($id);
                }
            }
            return false;
        }
    }

    static function check_global_actions(){
		$name  = "admin";
		$nom = "Admin";
        global $DB;
        if (isset($_POST['action'],$_POST[$name.'s']) && ($_POST['action'] != -1 || ($_POST['action'] == -1 && isset($_POST['action2']) && $_POST['action2'] != -1))) {
            if ($_POST['action'] == -1 && isset($_POST['action2']) && $_POST['action2'] != -1) {
                $_POST['action'] = $_POST['action2'];
            }
            $flash = array();
            if ($_POST['action'] == 'delete') {
                foreach ($_POST[$name.'s'] as $id) {
                    if($DB->delete(self::TABLE_NAME,array('id'=>$id)))
                        $flash[] = '<strong>'.$nom.' #'.$id.' supprimé</strong>';
                }
            }else if ($_POST['action'] == 'online') {
                foreach ($_POST[$name.'s'] as $id)
                    if($DB->save(self::TABLE_NAME,array('online'=> 1),array('update'=>array('id'=>$id)))){
                        $flash[] = '<strong>'.$nom.' #'.$id.' est en ligne</strong>';
                }
            }else if ($_POST['action'] == 'offline'){
                foreach ($_POST[$name.'s'] as $id)
                    if($DB->save(self::TABLE_NAME,array('online'=> 0),array('update'=>array('id'=>$id)))){
                        $flash[] = '<strong>'.$nom.' #'.$id.' est hors ligne</strong>';
                }
            // }else if ($_POST['action'] == 'client'){
                // foreach ($_POST[$name.'s'] as $id)
                    // if($DB->save(self::TABLE_NAME,array('type'=> 'client'),array('update'=>array('id'=>$id)))){
                        // $flash[] = '<strong>'.$nom.' #'.$id.' est maintenant un client</strong>';
                // }
            // }else if ($_POST['action'] == 'admin'){
                // foreach ($_POST[$name.'s'] as $id)
                    // if($DB->save(self::TABLE_NAME,array('type'=> 'admin'),array('update'=>array('id'=>$id)))){
                        // $flash[] = '<strong>'.$nom.' #'.$id.' est maintenant un admin</strong>';
                // }
            }else if (isset(self::$types[$_POST['action']]) && !empty(self::$types[$_POST['action']])){
                $type_value = self::$types[$_POST['action']];
                foreach ($_POST[$name.'s'] as $id)
                    if($DB->save(self::TABLE_NAME,array('type'=> $_POST['action']),array('update'=>array('id'=>$id)))){
                        $flash[] = '<strong>'.$nom.' #'.$id.' est '.$type_value.'</strong>';
                }
            }

            if(!empty($flash))Functions::setFlash(implode('<br/>', $flash),'success');
        }else{
            return false;
        }
    }

    public static function deleteAdmin($id){
    	global $DB;
    	if ($DB->findCount(self::TABLE_NAME,array('id'=>$id),'id') > 0) {
            $DB->delete(self::TABLE_NAME,array('id'=>$id));
            // self::ajouterAuxLog(date('Y-m-d h:m:s').' : Suppression Admin #'.$id."\n");
            Functions::setFlash('<strong>Admin #'.$id.' supprimée</strong>','success');
            return true;
        }else{
            Functions::setFlash('<strong>Admin inconnu</strong>','danger');
            return false;
        }
    }

	static public function updateAllDataBase(){
		global $DB;
		// debug($DB->queryFirst('SELECT SUM(price) globalPrice FROM admins'),'avant');
		// $AdminsIds = Functions::getFirstVals($DB->find(self::TABLE_NAME,array('fields'=>array('id'))));
		// // debug($AdminsIds,'$AdminsIds');
		// $price = array();
		// foreach ($AdminsIds as $id) {
		// 	$Admin = new Admin($id);
		// 	$Admin->updatePrice();
		// 	$Admin->saveFields('price');
		// 	$price[$id]=$Admin->price;
		// }
		// debug($price,'$price');
		// debug($DB->queryFirst('SELECT SUM(price) globalPrice FROM admins'),'après');
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