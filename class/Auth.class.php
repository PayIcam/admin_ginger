<?php class Auth{

    const TABLE_NAME = 'administrateurs';
    
    private $roles;

    function __construct(){
        global $DB;
        $this->roles = $DB->query('SELECT * FROM roles');
        // On organise les rôles par leur slug dans le tableau
        $roles = array(); 
        foreach($this->roles as $d){
            $roles[$d['slug']] = $d; 
        }
        $this->roles = $roles;
    }

    
    /**
     * Permet d'identifier un utilisateur
     **/
    function login($d){
        global $DB;

        $d = array(
            'email'    => $d['email'],
            'password' => md5($d['password'])
        );

        $return = $DB->queryFirst('SELECT administrateurs.id, administrateurs.email,administrateurs.nom,administrateurs.prenom,administrateurs.online,roles.name,roles.slug,roles.level FROM administrateurs LEFT JOIN roles ON administrateurs.role_id=roles.id WHERE email=:email AND password=:password',
            $d);
        if (empty($return)) {
            return false;
        }else if($return['online'] == 1 && $return['level'] != 0){ // si l'utilisateur est actif dans la BDD
            $_SESSION['Auth'] = array();
            $_SESSION['Auth'] = $return;
            return true;
        }else{
            Functions::setFlash('<strong>Votre compte n\'est pas actif !</strong><br/>Veuillez attendre que les administrateurs activent votre compte ou contactez nous !','warning');
            header('Location:connection.php');exit;
        }
        return false;
    }

    function loginUsingCas($ticket){
        global $DB;
        require 'class/Cas.class.php';
        $CAS = new Cas(Config::get('cas_url'));

        $userEmail = $CAS->authenticate($ticket,Config::get('admin_ginger_url'));

        if (!empty($userEmail) && $DB->findCount(self::TABLE_NAME,array('email'=>$userEmail),'email') == 1) {
            $return = $DB->queryFirst('SELECT administrateurs.id, administrateurs.email,administrateurs.nom,administrateurs.prenom,administrateurs.online,roles.name,roles.slug,roles.level FROM administrateurs LEFT JOIN roles ON administrateurs.role_id=roles.id WHERE email=:email',array('email'=>$userEmail));
            if (empty($return)) {
                Functions::setFlash("Vous ne faites pas parti des administrateur de l'Administration de Ginger.<br>Faites la demande aux responsables au besoin.",'warning');
                header('Location:connection.php');exit;
                return false;
            }else if($return['online'] == 1 && $return['level'] != 0){ // si l'utilisateur est actif dans la BDD
                $_SESSION['Auth'] = array();
                $_SESSION['Auth'] = $return;
                return true;
            }else{
                Functions::setFlash('<strong>Votre compte n\'est pas actif !</strong><br/>Veuillez attendre que les administrateurs activent votre compte ou contactez nous !','warning');
                header('Location:connection.php');exit;
            }
        }else if ($userEmail == 'AuthenticationFailure' || $userEmail == "Cas return is weird" || $userEmail == "Return cannot be parsed") {
            Functions::setFlash($userEmail,'danger');
            return false;
        }else if(!empty($userEmail)){
            Functions::setFlash("Vous ne faites pas parti des administrateur de l'Administration du projet PSA.<br>Faites la demande aux responsables au besoin.",'warning');
            header('Location:connection.php');exit;
        }
        return false;
    }

    public function logCasOut(){
        require 'class/Cas.class.php';
        $CAS = new Cas(Config::get('cas_url'));
        return $CAS->logout();
    }
    
    /**
     * Autorise un rang à accéder à une page, redirige vers forbidden sinon
     * */
    function allow($rang){
        $roles = $this->getLevels();

        if(!$this->user('slug')){
            $this->forbidden(); 
        }else{
            if($roles[$rang] > $this->user('level')){
                $this->forbidden(); 
            }else{
                return true;
            }
        }
    }
    
    /**
     * Récupère une info utilisateur
     ***/
    function user($field){
        if($field == 'role') $field = 'slug'; 
        if(isset($_SESSION['Auth'][$field])){
            return $_SESSION['Auth'][$field];
        }else{
            return false; 
        }
    }
    
    /**
     * Redirige un utilisateur
     * */
    function forbidden(){
        Functions::setFlash('<strong>Identification requise</strong> Vous ne pouvez accéder à cette page.','danger');
        header('Location:connection.php'.((!empty($_GET['ticket']))?'?ticket='.$_GET['ticket']:''));exit;
    }

    // -------------------- Security & Token functions -------------------- //
    public static function generateToken($nom = ''){
        $token = md5(uniqid(rand(147,1753), true));
        $_SESSION['tokens'][$nom.'_token'] = $token;
        $_SESSION['tokens'][$nom.'_token_time'] = time();
        return $token;
    }

    public static function validateToken($token, $nom = '', $temps = 600, $referer = ''){
        if (empty($referer)){
            $referer = Config::get('admin_ginger_url').basename($_SERVER['REQUEST_URI']);
        }
        if(isset($_SESSION['tokens'][$nom.'_token']) && isset($_SESSION['tokens'][$nom.'_token_time']) && !empty($token))
            if($_SESSION['tokens'][$nom.'_token'] == $token)
                if($_SESSION['tokens'][$nom.'_token_time'] >= (time() - $temps)){
                    if(!empty($_SERVER['HTTP_REFERER']) && dirname($_SERVER['HTTP_REFERER']) == dirname($referer))
                        return true;
                    elseif(empty($_SERVER['HTTP_REFERER']))
                        return true;
                }
        return false;
    }

    // -------------------- isXXX functions -------------------- //
    function isLogged(){ // vérification de de l'existence d'une session "Auth", d'une session ouverte
        if ($this->user('level') > 0)
            return true;
        else
            return false;
    }
    function isAdmin(){ //vérification que l'utilisateur loggué est administrateur
        if ($this->user('role') == 'admin')
            return true;
        else
            return false;
    }

    // -------------------- Getters -------------------- //
    public function getLevels($key = 'slug'){
        global $DB;
        if ($key != 'slug' || $key != 'id')
            $key = 'slug';

        $roles = array(); 
        foreach($this->roles as $d){
            $roles[$d[$key]] = $d['level']; 
        }
        return $roles;
    }
    public function getRoles($key = 'id'){
        global $DB;
        if ($key != 'slug' || $key != 'id')
            $key = 'id';

        $roles = array(); 
        foreach($this->roles as $d){
            $roles[$d[$key]] = $d['name']; 
        }
        return $roles;
    }
    public function getRole($key){
        if (isset($this->roles[$key])) {
            return $this->roles[$key];
        }else{ // C'est surement son iD
            foreach($this->roles as $d){
                if ($d['id'] == $key) {
                    return $d;
                }
            }
            return null;
        }
    }
    public function getRoleName($id){
        $role = $this->getRole($id);
        return $role['name'];
    }
}

$Auth = new Auth();
