<?php class Auth{
    
    var $forbiddenPage = "connection.php";
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
        }else if($return['online'] == 1){ // si l'utilisateur est actif dans la BDD
            $_SESSION['Auth'] = array();
            $_SESSION['Auth'] = $return;
            return true;
        }else{
            Functions::setFlash('<strong>Votre compte n\'est pas actif !</strong><br/>Veuillez attendre que les administrateurs activent votre compte ou contactez nous !','warning');
        }
        return false;
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
        header('Location:connection.php');exit;
    }

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
