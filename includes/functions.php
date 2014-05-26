<?php

if (empty($DB)) {
    require_once 'class/db.class.php';
    if(preg_match('/^\/axentia\//', $_SERVER['REQUEST_URI'])){
        $DB = new DB();
    }else{
        $serveur = 'localhost';
        $user    = 'recette-axentia';
        $pass    = 'RJKp6Hjp';
        $bdd     = 'recette-axentia';
        $DB      = new DB($serveur,$user,$pass,$bdd);
    }
}

/**
 * Fonction debug qui permet d'afficher joliment le contenu d'une variable dans un pre
 * @param <type> $var
 * @param char $nom
 */
function debug($var,$nom=NULL,$open=1){ //afficher les données tel que le pc les récupère
    //if (isset($_SESSION['role']) && $_SESSION['role']=='admin' || preg_match('/^\/SiteBdsIcamLille\//', $_SERVER['REQUEST_URI'])) {
        preg_match('#([a-z1-9_-]+.php)$#', $_SERVER['SCRIPT_FILENAME'], $matches);
        echo '<div><div><p class="alert alert-warning" onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;"><a class="close" href="#" onclick="$(this).parent().parent().slideUp();return false;">×</a>debug à la ligne <strong>'.__LINE__.'</strong>';
        if($nom!=NULL){echo ' de <em><strong>'.$nom.'</em></strong>';}
        echo ' dans <em><strong>'.$matches[0].'</em></strong></p>';
        echo '<pre'.((!empty($open))?'':' style="display:none;"').'>';
        print_r($var);
        echo '</pre></div></div>';
    //}
}

/**
 *
 * @param <type> $chaine
 * @param <type> $lg_max
 * @return string
 */
function racourcirChaine($chaine,$lg_max){
    if (strlen($chaine) > $lg_max)
    {
        $chaine = substr($chaine, 0, $lg_max);
        $last_space = strrpos($chaine, " ");
    //On ajoute ... à la suite de cet espace
        $chaine = substr($chaine, 0, $last_space)."...";
    }
    return $chaine;
}

class Functions{

    /**
     *
     * @param <type> $message
     * @param <type> $type 
     */
    static function setFlash($message, $type = 'success'){ // On créer un tableau dans lequel on stock un message et un type qu'on place dans la variable flash de la variable $_session
        $_SESSION['flash'][] = array(
            'message'   => $message,
            'type'      => $type
        );
    }

    /**
     *
     * @return string
     */
    static function flash(){ //parcourir dans les flash de la $_session, le array contenant le message défini grâce au setflash
        if(isset($_SESSION['flash'])){
            $html = '';
            foreach ($_SESSION['flash'] as $k => $v) {
                if(isset($v['message'])){
                    $html .= '<div class="alert alert-'.$v['type'].'"><button class="close" data-dismiss="alert">×</button>'.$v['message'].'</div>';
                }
            }
            $html .= '<div class="clear"></div>';
            $_SESSION['flash'] = array();
            return $html;
        }
    }

    /**
    * Permet de supprimer une valeur dans la bdd.
    * @global PDO $DB
    * @param string $name
    * @param string $title
    * @return boolean
    **/
    static function check_delete($name,$title=null,$page=null){
        global $DB;

        if (empty($page)) {
            $page = 'admin_liste_'.$name.'s.php';
        }
        
        if(!empty($_GET['del_'.$name])){
            $id=$_GET['del_'.$name];
            if(empty($title)) $title=$name;

            if ($DB->findCount($name.'s',"id=$id") != 0) {
                $DB->delete($name.'s','id='.$id);
                Functions::setFlash('<strong>'.$title.' #'.$id.' supprimé</strong>','success');
                header('Location:'.$page);exit;
            }else{
                Functions::setFlash('<strong>'.$title.' inconnu</strong>','danger');
                header('Location:'.$page);exit;
            }
        }else{
            return false;
        }
    }

    /**
    * Permet de supprimer une valeur dans la bdd.
    * @global PDO $DB
    * @param string $name
    * @param string $title
    * @return boolean
    **/
    static function check_activation($name,$title=null){
        global $DB;
        if(!empty($_GET['activate_'.$name])){
            $id=$_GET['activate_'.$name];
            if(empty($title)) $title=$name;
            if ($DB->findCount($name.'s',"id=$id") != 0) {
                $DB->save($name.'s',array('online'=> 1),array('update'=>array('id'=>$id)));  // On passe le champ activer à 1 <=> actif
                Functions::setFlash('<strong>'.$title.' #'.$id.' activé</strong>','success');
                header('Location:admin_liste_'.$name.'s.php');exit;
            }else{
                Functions::setFlash('<strong>'.$title.' inconnu</strong>','danger');
                header('Location:admin_liste_'.$name.'s.php');exit;
            }
        }else if (!empty($_GET['disactivate_'.$name])) {
            $id=$_GET['disactivate_'.$name];
            if(empty($title)) $title=$name;
            if ($DB->findCount($name.'s',"id=$id") != 0) {
                $DB->save($name.'s',array('online'=> 0),array('update'=>array('id'=>$id)));  // On passe le champ activer à 1 <=> actif
                Functions::setFlash('<strong>'.$title.' #'.$id.' desactivé</strong>','info');
                header('Location:admin_liste_'.$name.'s.php');exit;
            }else{
                Functions::setFlash('<strong>'.$title.' inconnu</strong>','danger');
                header('Location:admin_liste_'.$name.'s.php');exit;
            }
        }else{
            return false;
        }
    }

    /**
    * Permet de supprimer une valeur dans la bdd.
    * @param string $name
    * @param string $title
    * @return boolean
    **/
    static function check_global_actions($name,$title=null){
        global $DB;
        if (isset($_POST['action'],$_POST[$name.'s']) && ($_POST['action'] != -1 || ($_POST['action'] == -1 && isset($_POST['action2']) && $_POST['action2'] != -1))) {
            if ($_POST['action'] == -1 && isset($_POST['action2']) && $_POST['action2'] != -1) {
                $_POST['action'] = $_POST['action2'];
            }
            if ($_POST['action'] == 'delete') {
                $flash = array();
                foreach ($_POST[$name.'s'] as $id) {
                    if($DB->delete($name.'s','id='.$id)){
                        $flash[] = '<strong>'.$title.' #'.$id.' supprimé</strong>';
                    }
                }
                if(!empty($flash))Functions::setFlash(implode('<br/>', $flash),'success');
            }else if ($_POST['action'] == 'online') {
                $flash = array();
                foreach ($_POST[$name.'s'] as $id) {
                    if($DB->save($name.'s',array('online'=> 1),array('update'=>array('id'=>$id)))){
                        $flash[] = '<strong>'.$title.' #'.$id.' en ligne</strong>';
                    }
                }
                if(!empty($flash))Functions::setFlash(implode('<br/>', $flash),'success');
            }else if ($_POST['action'] == 'offline'){
                $flash = array();
                foreach ($_POST[$name.'s'] as $id) {
                    if($DB->save($name.'s',array('online'=> 0),array('update'=>array('id'=>$id)))){
                        $flash[] = '<strong>'.$title.' #'.$id.' hors ligne</strong>';
                    }
                }
                if(!empty($flash))Functions::setFlash(implode('<br/>', $flash),'success');
            }
        }else{
            return false;
        }
    }

    /**
     * Fonction maintenance : redirige automatiquement vers la page unfinished.php
     **/
    static function maintenance(){
        if(!isset ($_SESSION)){session_start();}
        $maintenance = Functions::getConfig('maintenance');
        // debug($_SESSION,'Session');
        // debug($maintenance,'maintenance');
        // debug($_SERVER['SCRIPT_NAME'],'$_SERVER');
        if ($maintenance == true) {
            if ((isset($_SESSION['Auth']['profil']) && $_SESSION['Auth']['profil'] == 'admin') || (isset($_SERVER['SCRIPT_NAME']) && preg_match("/connection.php/", $_SERVER['SCRIPT_NAME']) || preg_match('/maintenance.php/', $_SERVER['SCRIPT_NAME']) ) ) {
                /*
                Cas ou : 
                On est loggé en tant qu'admin (isset($_SESSION['Auth']['profil']) && $_SESSION['Auth']['profil'] == 'admin')
                On est sur la page connection.php (preg_match("/connection.php/", $_SERVER['SCRIPT_NAME']))
                On est sur la page maintenance.php (preg_match("/maintenance.php/", $_SERVER['SCRIPT_NAME']))
                */
            }else{ // Sinon, redirection !
                Functions::setFlash('redirection maintenance..','danger');
                header('Location:maintenance.php');
            }
        }
    }

    static function getConfig($name){
        global $DB;
        $conf = $DB->findFirst('configs',array(
            'fields' => 'value',
            'conditions' => array('name'=>$name)));
        if (empty($conf)) {
            $DB->query("INSERT INTO configs VALUES(:name, '')",array('name'=>$name));
            return "";
        }else return current($conf);
    }

    static function setConfig($name,$value){
        global $DB;
        return $DB->save('configs',array('value'=> $value),array('update'=>array('name'=>$name)));
    }

    static function islog(){ // vérification de de l'existence d'une session "Auth", d'une session ouverte
        global $DB;
        if(    isset($_SESSION['Auth'])
            && isset($_SESSION['Auth']['email'])
            && isset($_SESSION['Auth']['password'])
            && $DB->findCount('users_admin',array(
                    'email'    => $_SESSION['Auth']['email'],
                    'password' => $_SESSION['Auth']['password']
                )) == 1
        )   return true;
        else
            return false;
    }

    static function isAdmin(){ //vérification que l'utilisateur loggué est administrateur
        global $DB;
        if(    isset($_SESSION['Auth'])
            && isset($_SESSION['Auth']['email'])
            && isset($_SESSION['Auth']['password'])
            && $DB->findCount('users_admin',array(
                    'email'    => $_SESSION['Auth']['email'],
                    'password' => $_SESSION['Auth']['password'],
                    'profil'   => 'admin'
                )) == 1
        )   return true;
        else
            return false;
    }

    static function isUser($id){
        global $DB;
        if (is_numeric($id) && $DB->findCount('users_admin',array('id'=>$id)) == 1) return true;
        else return false;
    }

    static function isAuthor($id){
        global $DB;
        if (is_numeric($id) && $DB->findCount('authors',array('id'=>$id)) == 1) return true;
        else return false;
    }

    static function isPost($id){
        global $DB;
        if (is_numeric($id) && $DB->findCount('posts',array('id'=>$id)) == 1) return true;
        else return false;
    }

    static function isDoc($id){
        global $DB;
        if (is_numeric($id) && $DB->findCount('documents',array('id'=>$id)) == 1) return true;
        else return false;
    }

    static function isGuest($id){
        global $DB;
        if (is_numeric($id) && $DB->findCount('invites',array('id'=>$id)) == 1) return true;
        else return false;
    }

    static function isGroup($id){
        global $DB;
        if (is_numeric($id) && $DB->findCount('groups',array('id'=>$id)) == 1) return true;
        else return false;
    }

    static function isPage(){
        $i=0;
        foreach (func_get_args() as $key => $v){
            if ($v == 'index') {
                if (preg_match('/\/$/', $_SERVER['REQUEST_URI']))       $i++;
            }
            if(preg_match('/\/'.$v.'\.(php|html|htm)/', $_SERVER['REQUEST_URI']))  $i++;
        }
        if($i>0){return TRUE;}
        else{return FALSE;}
    }

    static function getFirstVals($array){
        if (isset($array[0]) && is_array($array[0])) {
          $trash = array();
          foreach ($array as $value) {
            if (isset($value['id'],$value['name']))
                $trash[$value['id']] = $value['name'];
            else
                $trash[] = current($value);
          }
          return $trash;
        }else{
          return $array;
        }
    }

    static function getTables($tables,$db){
        $trash = array();
        foreach ($tables as $table) {
            $trash[$table] = getFirstVals($db->query('SHOW COLUMNS FROM '.$table));
        }
        return $trash;
    }

    static function AreArraysDifferent($Array2008,$Array2010){
        if (sizeof($Array2008) != sizeof($Array2010))
            return true;
        elseif (sizeof($Array2008) != sizeof(array_intersect_assoc($Array2008,$Array2010))) {
            return true;
        }else{
            return false;
        }
    }

    static function arrayDif($a1,$a2){
        $dif = array();
        foreach ($a1 as $v) {
            if (!in_array($v, $a2))
                $dif[] = $v;
        }
        return $dif;
    }

    static function arrayInter($a1,$a2){
        $inter = array();
        foreach ($a1 as $v) {
            if (in_array($v, $a2))
                $inter[] = $v;
        }
        return $inter;
    }

    /**
     * Combine several arrays together
     * @return array All arrays given in one array
     */
    static function arrayMerge(){
        $arrayCombine = array();
        foreach (func_get_args() as $array) {
            foreach ($array as $k => $v) {
                $arrayCombine[$k] = $v;
            }
        }
        return $arrayCombine;
    }

    static function getLanguage($langCode){
        $languages = array('fr'=>'Français','gb'=>'Anglais','es'=>'Español','de'=>'Allemand','cn'=>'Chinois');
        if (!empty($languages[$langCode]))
            return $languages[$langCode];
        else
            return null;

    }

    static function getGroupDocsCount($groupId = 0){
        global $DB;
        $count = 0;
        $where = array();
        if (!empty($groupId) && is_numeric($groupId)) {
          $where[] = 'group_id='.$groupId;
          $subgroups = Functions::getFirstVals($DB->query("SELECT id FROM groups WHERE ancestors_ids LIKE '%,".$groupId.",%'"));
          if (!empty($subgroups)) {
            $where = array_merge($where,$subgroups);
          }
          $sql = 'SELECT COUNT(gr.group_id) as count FROM ( SELECT group_id FROM groups_has_documents WHERE '.implode(' OR group_id=',$where).' GROUP BY document_id )gr';
          $count = $DB->queryFirst($sql);
          return $count['count'];
        }else{
          return current($DB->queryFirst('SELECT COUNT(id) as count FROM documents'));
        }
    }

    static function getHomeLocations(){
        global $DB;
        $floor='';
        $locations = array();
        foreach ($DB->query('SELECT id,name,floor FROM locations WHERE id != 0 ORDER BY floor') as $place) {
            if($floor != $place['floor']) $floor = $place['floor'];
            $locations[$floor][$place['id']] = $place['name'];
        }
        $locs = array(''=>'');$etages = array('0'=>'RDC','1'=>'Premier','2'=>'Second');
        foreach ($locations as $key => $value) {
            if (count($value) == 1)
                $locs[key($value)] = current($value);
            elseif(isset($etages[$key]))
                $locs[$etages[$key]] = $value;
            else
                $locs[$key] = $value;
        }
        return $locs;
    }

    static function getGroup($id){
        global $DB;
        if(Functions::isGroup($id))
            return $DB->queryFirst("SELECT id, name FROM `groups` WHERE `id` = ".$id);
        else
            return false;
    }

    static function getBaseGroups($parent_id=0){
        return Functions::getSubGroups($parent_id);
    }

    static function getSubGroups($id,$prepend=null){
        global $DB;
        $return = false;
        if (Functions::isGroup($id))
            $return = Functions::getFirstVals($DB->query("SELECT id, name FROM `groups` WHERE `parent_id` = ".$id));
        elseif (empty($id))
            $return = Functions::getFirstVals($DB->query("SELECT id, name FROM `groups` WHERE `parent_id` = 0"));
        if (!empty($prepend)) {
            foreach ($return as $k => $v) {
                $return[$k] = $prepend.$v;
            }
        }
        return $return;
    }

    static function getAllSubGroups($id=0,$conParentGrName=0){
        global $DB;
        $id = (Functions::isGroup($id))?$id:0;
        $subGroups = array();
        foreach (Functions::getSubGroups($id) as $id => $name):
            if(!empty($conParentGrName)){
                $subGroups[$name] = Functions::arrayMerge(array($id=>$name),Functions::getSubGroups($id,'-&nbsp;'));
            }else{
                $subGroups[$name] = Functions::getSubGroups($id);
            }
        endforeach;
        return $subGroups;
    }

    static function getId($table,$conditions,$options=array('delete'=>true,'save'=>true)){
        global $DB;
        $count = $DB->findCount($table,$conditions,'id');
        if ($count == 1) {
            return current($DB->findFirst($table,array('conditions'=>$conditions,'fields'=>'id')));
        }else if($count > 1 && !empty($options['delete'])){
            $DB->delete($table,$conditions);
            return $DB->save($table,$conditions,'insert');
        }elseif(!empty($options['save'])){
            return $DB->save($table,$conditions,'insert');
        }else{
            return null;
        }
    }

    static function getEditorId($name){
        return Functions::getId('editors',array('name'=>$name),array('delete'=>false,'save'=>true));
    }

    static function saveGroup($group_id,$document_id=null){
        global $DB;
        $DB->delete('groups_has_documents',array('document_id'=>$document_id));
        if (Functions::isGroup($group_id)){
            $q = array('document_id'=>$document_id,'group_id'=>$group_id);
            $DB->save('groups_has_documents',$q,'insert');
        }
    }

    static function getCollectionId($col){
        return Functions::getId('collections',$col);
    }

    static function saveCollection($col,$document_id=null){
        global $DB;
        $DB->delete('document_has_collection',array('document_id'=>$document_id));
        if (!empty($col['name']) && !empty($col['col_number'])) {
            $d['name'] = $col['name'];
            $col_id    = Functions::getCollectionId($d);
            $q = array(
                'document_id'   => $document_id,
                'collection_id' => $col_id,
                'col_number'    => $col['col_number']
            );
            $DB->save('document_has_collection',$q,'insert');
        }
    }

    static function getAuthorId($col){
        return Functions::getId('authors',$col);
    }

    static function saveAuthors($authors,$document_id=null){
        global $DB;
        $DB->delete('document_has_author',array('document_id'=>$document_id));
        foreach ($authors as $author){
            if (!empty($author['name']) && !empty($author['role']) && !empty($author['sexe'])) {
                $name      = explode(', ', $author['name']);
                $d['name'] = $name[0];
                if (isset($name[1])) $d['firstname'] = $name[1];
                $author_id = Functions::getAuthorId($d);
                $q = array(
                    'document_id' => $document_id,
                    'author_id'   => $author_id,
                    'role'        => $author['role']
                );
                $DB->save('document_has_author',$q,'insert');
            }
        }
    }

    static function getTermId($col){
        return Functions::getId('terms',$col);
    }

    static function saveTerms($tags,$document_id,$type='tag'){
        global $DB;
        $DB->query('DELETE FROM terms_relationships
                    WHERE term_id IN (SELECT T.id FROM terms T
                        WHERE T.type=:type )
                    AND ref_id = :ref_id AND ref=:ref',
            array('ref_id'=>$document_id,'ref'=>'Document','type'=>$type)
        );
        if(!is_array($tags)){
            if (!empty($tags)) {
                $d      = array('name'=>$tags,'type'=>$type);
                $tag_id = Functions::getTermId($d);
                $q      = array(
                    'ref_id'=>$document_id,
                    'ref'=>'Document',
                    'term_id'=>$tag_id
                );
                $DB->save('terms_relationships',$q,'insert');
            }
        }else{
            foreach ($tags as $tag){
                if (!empty($tag['name'])) {
                    $d      = array('name'=>$tag['name'],'type'=>$type);
                    $tag_id = Functions::getTermId($d);
                    $q      = array(
                        'ref_id'=>$document_id,
                        'ref'=>'Document',
                        'term_id'=>$tag_id
                    );
                    $DB->save('terms_relationships',$q,'insert');
                }
            }
        }
    }

    /**
     *
     * @param <type> $id
     */
    static function tablesorter($id,$col='[1,0]',$header = '0: {sorter: false}'){
        echo "<script src='js/jquery.tablesorter.min.js'></script>
    <script >
        jQuery(function() {
            jQuery('table#$id').addClass('tablesorter').tablesorter({
                sortList: [".$col."],
                headers : {".$header."}
            });
        });
    </script>";
    }

    static function getProgressBar( $pourcent, $width, $height, $color, $class='success' ) {
        $bar = '<div style="margin:0 auto;height:'.$height.'px;width:'.$width.'px;border:1px solid '.$color.';text-align:left;display:inline-block;position:relative;" class="progress progress-'.$class.'">
            <div class="progress-bar" style="width: '.$pourcent.'%;"></div>
        </div>';
        return $bar;
    }

    static function getMultipleProgressBar( $options,$mainbar) {
        if (empty($mainbar)) {
            $mainbar = array('height'=>'6','width'=>'40','display'=>'block','class'=>'success');
        }
        $returnbar = '<span style="height:'.$mainbar['height'].'px;width:'.(($mainbar['width']>0)?$mainbar['width'].'px':$mainbar['width']).';text-align:left;margin:auto;'.((!empty($mainbar['display']) && $mainbar['display']=='inline-block')?'margin:auto 5px;':'').'display:'.((!empty($mainbar['display']))?$mainbar['display']:'block').';" class="progress'.((!empty($options['all']) && (!empty($mainbar['class']) && $mainbar['class'] != 'no'))?' bar-'.((!empty($mainbar['class']))?$mainbar['class']:$options['all'][count($options['all'])-1]['class']):'').'">';
            if (!empty($options['sum'])) {
                foreach ($options['all'] as $key => $bar) {
                    if ($key<count($options['all'])-1) {
                        $returnbar .= '<span class="progress-bar'.((!empty($bar['class']))?' bar-'.$bar['class']:'').'" style="width: '.round($bar['pourcent']/$options['sum']*100,2).'%;"'.((!empty($bar['title']))?' rel="tooltip" title="'.$bar['title'].'" data-original-title="'.$bar['title'].'"':'').'></span>';
                    }else{
                        $returnbar .= '<span class="progress-bar'.((!empty($bar['class']))?' bar-'.$bar['class']:'').'" style="width: '.round($bar['pourcent']/$options['sum']*100-0.01,2).'%;"'.((!empty($bar['title']))?' rel="tooltip" title="'.$bar['title'].'" data-original-title="'.$bar['title'].'"':'').'></span>';
                    }//rel="tooltip" href="#" data-original-title
                }
            }
        $returnbar .= '</span>';
        return $returnbar;
    }

    public static function cleanImageName($value){
        $return = $value;
        $return = strtolower($return);
        $return = str_replace(array('(',')','{','}'), '', $return);
        $return = str_replace(array(' - '), '-', $return);
        $return = str_replace(array(' ','\''), '-', $return);
        $return = str_ireplace(array('é','è','ê','ë'), 'e', $return);
        $return = str_ireplace(array('à','â','ä'), 'a', $return);
        $return = str_ireplace(array('ï','î'), 'i', $return);
        $return = str_ireplace(array('ö','ô'), 'o', $return);
        $return = str_ireplace(array('û','ü'), 'u', $return);
        return $return;
    }
}


