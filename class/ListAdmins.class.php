<?php

require_once 'includes/_header.php';
require_once ROOT_PATH.'class/Admin.class.php';

/**
* Class ListAdmins
**/
class ListAdmins{

	const perPages = 50;
	private $searchFields = array('nom', 'prenom', 'email');
	private $exportFields = array('id','nom','prenom','email','online','role_id');

	private $keyword;
	private $page;
	private $perPages;
	private $options;
	private $globalCountAdmins;
	private $countAdmins;
	private $countSqlReturnedAdmins;
	private $countPages;
	
	private $adminsList;

	function __construct($data=array()){
		if (empty($data['recherche1']) && !empty($data['recherche2']))
			$this->keyword = $data['recherche2'];
		elseif (!empty($data['recherche1']))
			$this->keyword = $data['recherche1'];
		elseif (!empty($_GET['keyword']))
			$this->keyword = urldecode($_GET['keyword']);
		elseif (!empty($data) && !is_array($data))
			$this->keyword = $data;
		else
			$this->keyword = '';
		if ($this->keyword == '!') $this->keyword = '';

		if (!empty($data['page']))
			$this->page = $data['page'];
		elseif (!empty($_GET['page']))
			$this->page = $_GET['page'];
		else
			$this->page = 1;

		$this->perPages = (isset($data['perPages']) && $data['perPages'] >= 0) ? $data['perPages'] : self::perPages;
		$this->options  = !empty($data['options']) ? $data['options'] : array();

		$this->globalCountAdmins = Admin::getAdminsCount();
		if ($this->perPages > 0)
			$this->adminsList = $this->getAdminsList();
		else{
			$this->adminsList = array();
			$this->countAdmins = $this->globalCountAdmins;
			$this->countPages  = 1;
		}
		$this->countSqlReturnedAdmins = count($this->adminsList);
	}

	private function getAdminsList(){
		global $DB;
		$where = '';$q = array();

		$whereSearch = $this->getWhereForSearch();
		if (isset($whereSearch['leftJoin']) && $whereSearch['leftJoin'] != '')
			$leftJoin = $whereSearch['leftJoin'];
		else
			$leftJoin = '';
		if (!empty($whereSearch['data']))
			$q = $whereSearch['data'];
		if (!empty($whereSearch['where']))
			$whereSearch = $whereSearch['where'];
		else
			$whereSearch = '';

		$whereOptions = $this->getWhereForOptions();

		$where = (!empty($whereSearch) || !empty($whereOptions))?'WHERE ':' ';
		if (!empty($whereSearch))
			$where .= $whereSearch;
		if (!empty($whereSearch) && !empty($whereOptions))
			$where .= ' AND ';
		if (!empty($whereOptions))
			$where .= $whereOptions;

	    // --------------------- Vérification sur le nombre de pages --------------------- //
		$this->countAdmins = current($DB->queryFirst('SELECT COUNT(id) FROM '.Admin::TABLE_NAME.' '.$where, $q));
		$this->countPages  = ceil($this->countAdmins/$this->perPages);
		//*
		if ( empty($this->options)  || !empty($this->options['fields'])
			|| isset($this->options['selectAllTypes'],$this->options['type'])
			&& !( Admin::getTypesCount() > count($this->options['type'])
				&& $this->options['selectAllTypes'] == 0 && !empty($this->options['type']))
		){
			if ($this->page > $this->countPages || $this->page < 1){
				$this->page = 1;
			}
		}//*/

		// --------------------- Sélection des admin dans la base --------------------- //
	    $fields = (!empty($this->options['fields']))?((is_array($this->options['fields']))?implode(',', $this->options['fields']):$this->options['fields']):'*';
		$sql = 'SELECT '.$fields.' FROM '.Admin::TABLE_NAME.' '.(($leftJoin != '')?$leftJoin:'').' '.$where;
		//*
		if (empty($this->options) || !empty($this->options['fields'])
			|| (isset($this->options['selectAllTypes'],$this->options['type'])
					&& !( Admin::getTypesCount() > count($this->options['type'])
								&& $this->options['selectAllTypes'] == 0 && !empty($this->options['type'])))
		)
			$sql .= ' ORDER BY id ASC LIMIT '.(($this->page-1)*$this->perPages).','.$this->perPages;
		//*/
		// debug($sql,'$sql');
		$retour = $DB->query($sql, $q);

		return $retour;
	}

	public function getWhereForSearch(){
		$where = '';$q = array();

        // ---------------- Recherche en fonction du mot clé ---------------- //
        if (!empty($this->keyword)) {
		    // Multiple research fields
		    $explodedKeywords = explode(' ', htmlspecialchars(str_replace('!', '', $this->keyword)));
		    // On se simplifie la vie si il rentre plus de mots que de champs de recherche ... ?!
		    // if (count($explodedKeywords) > count($searchFields)) {
		    // 	$rowLastKeywordToMergeIn = (count($searchFields)-1);
		    // 	$rowLastKeywordToMerge = (count($explodedKeywords)-1);
		    // 	for ($i=$rowLastKeywordToMergeIn+1; $i < $rowLastKeywordToMerge ; $i++) { 
		    // 		$explodedKeywords[$rowLastKeywordToMergeIn] .= ' '.$explodedKeywords[$i];
		    // 		unset($explodedKeywords[$i]);
		    // 	}
		    // }
		    $q = array();
		    $whereKeywords = array();
		    $isNotLikeQuery = preg_match('/^[!]/i', $this->keyword);
		    foreach ($explodedKeywords as $k => $kw) {
		    	$k = 'motclef'.$k;
		    	$q[$k] = '%'.$kw.'%';
		    	if($isNotLikeQuery){
				    $whereKeywords[] = '('.implode(' NOT LIKE :'.$k.' AND ', $this->searchFields).' NOT LIKE :'.$k.')
';
			    }else{
			    	$whereKeywords[] = '('.implode(' LIKE :'.$k.' OR ', $this->searchFields).' LIKE :'.$k.')
';
			    }
		    }
		    $where .= '('.implode(' AND ', $whereKeywords).')
';
		}
		return array('where'=>$where,'data'=>$q);
	}

	public function getWhereForOptions($options = array()){
		if (empty($options)) {
			$options = $this->options;
		}
		if (!empty($options) && isset($options['selectAllTypes'],$options['type'],$options['doublons'])) {
			$whereOptions = '';
	    	if (!is_array($options)) {
                $whereOptions.= '('.$options.')
';
            }else{
                $cond = array();
                /*if ($options['selectAllTypes'] == 1)
                	$cond[] = 'type != "" ';
                else if ($options['selectAllTypes'] == 0 && empty($options['type']))
                	$cond[] = 'type = "" ';
                else if ($options['selectAllTypes'] == 0 && !empty($options['type']))
                	$cond[] = '(type IN('.implode(',', $options['type']).') )';
                //*/
                // if (!empty($options['doublons']) && $options['doublons'] == 1) {
                	// $cond[] = 'id IN( SELECT tmptable.bracelet_id FROM ( SELECT bracelet_id FROM guests WHERE bracelet_id != 0 GROUP BY bracelet_id HAVING COUNT(bracelet_id) >1 ) AS tmptable )';
                	// $this->perPages = 3000;
                // }
                
                if (!empty($cond)) {
               		$whereOptions .= '('.implode(' AND ',$cond).')
';
                }
            }
            return $whereOptions;
	    }else return '';
	}

	public function getAdminAsTr(){
		global $Auth;
		$motclef = $this->keyword;
		ob_start();
		if($this->countAdmins){
	        foreach ($this->adminsList as $admin) { ?>
<tr>
  <td>
    <input id="admin_<?= $admin['id']; ?>" class="checkbox" type="checkbox" value="<?= $admin['id']; ?>" name="admins[]">
  </td>
  <td>
    <span class="badge badge-<?= ($admin['online']==1)?'success':'inverse';?>" title="<?= ($admin['online']==1)?'Admin actif':'Admin inactif';?>">
      <?= $admin['id'] ?>
    </span>
  </td>
  <td><?= (empty($motclef))? $admin['nom']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $admin['nom']); ?></td>
  <td><?= (empty($motclef))? $admin['prenom']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $admin['prenom']); ?></td>
  <td><?= (empty($motclef))? $admin['email']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $admin['email']); ?></td>
  <td><?= $Auth->getRoleName($admin['id']); ?></td>
  <td>
    <div class="pull-right">
      <a href="admin_edit_admin.php?id=<?= $admin['id'] ?>" title="Editer l'admin #<?= $admin['id']; ?>"><i class="glyphicon glyphicon-pencil"></i></a>
      <a href="admin_liste_admins.php?del_admin=<?= $admin['id']; ?>" title="Supprimer l'admin #<?= $admin['id']; ?>" onclick="return confirm('Voulez-vous vraiment supprimer ce admin ?');"><i class="glyphicon glyphicon-trash"></i></a>              
    </div>
  </td>
</tr>
	        	<?php
	        }
	    }else{?>
	        <tr>
	          <td colspan="12">
	            <em>Aucun admin trouvé.</em>
	          </td>
	        </tr>
	    <?php }

	    $return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	public function getTHead(){
		global $DB;
		ob_start(); ?>
			<tr>
				<th><input onclick="toggleChecked(this.checked)" class="checkbox" type="checkbox"></th>
				<th>Id</th>
				<th>Nom</th>
				<th>Prénom</th>
				<th>Email</th>
				<th>Rôle</th>
				<th>Actions</th>
			</tr>
	    <?php 
	    $return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	public function getActionsGroupees($id){
		global $DB;
		ob_start(); ?>
<p class="actions form-inline pull-left">
	<select name="action" id="action<?= $id ?>" class="form-control">
	  <option selected="selected" value="-1">Action Groupée</option>
	  <optgroup label="Mettre en ligne">
        <option value="online">Oui</option>
        <option value="offline">Non</option>
      </optgroup>
	  <option value="delete">Supprimer </option>
	</select>
	<button class="btn btn-default" type="submit">Appliquer</button>
</p>
<div class="pull-left form-inline" style="margin-left:15px;">
	<div class="form-group">
		<input class="form-control search-query" id="recherche<?= $id ?>" name="recherche<?= $id ?>" placeholder="Rechercher ..." type="text" value="<?= $this->keyword; ?>">
	</div>
	<button class="btn btn-default" type="submit">Submit</button>
</div>
<?php /* ?>
<div class="pull-left" style="margin-left:15px;">
	<a id="BtnRechercheAvancee" href="#FormRechercheAvancee" class="btn btn-primary" onclick="jQuery('#FormRechercheAvancee').slideToggle(); return false;">Recherche Avancée</a>
	<small class="loader" style="margin-left:10px; display:none;"><img src="img/icons/spinner.gif" alt="loader"></small>
</div>
<?php //*/ ?>
<p class="pull-right">
	<em><span class="adminCount" title="nombre de membres affichés"><?= $this->countSqlReturnedAdmins.'/'.$this->countAdmins; ?></span> admin</em>
</p>
	    <?php 
	    $return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	public function getPagination($forjs=false){
		global $DB;
		/*	//pagination-centered
			<!-- <li class="disabled"><a href="#">«</a></li> -->
			<!-- <li><a href="#">»</a></li> -->
		*/
		ob_start();
		?><ul class="pagination">
			<?php for ($i=1; $i <= $this->countPages; $i++): ?>
				<li id="p<?= $i ?>" <?= ($i == $this->page)?'class="active"':''; ?>>
					<a class="page" id="p<?= $i ?>" href="admin_liste_admins.php?page=<?= $i ?>">
						<?= $i ?>
					</a>
				</li>
			<?php endfor; ?>
		</ul>
		<?php 
		$return = ob_get_contents();
		ob_end_clean();
		if ($forjs) {
			$return = str_replace("
", "\\
", trim($return));
		}
		return $return;
	}

	// -------------------- Export functions -------------------- //

	public function exportAdminList(){
		global $DB;
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=data.csv');
		$AdminsArray = $this->adminsList;
		$output = fopen('php://output', 'w');

		// output the column headings
		fputcsv($output, $this->exportFields,';');
		foreach ($AdminsArray as $admin) {
			$array = array();
			foreach ($this->exportFields as $field) {
				$array[$field] = $admin[$field];
			}
			fputcsv($output, $array,';');
		}
		fclose($output);
	}

	// -------------------- Informations functions -------------------- //

	public function getUrlParams($name){
		$retour = '';
		if ($name == 'export')
			$retour .= "export_liste_admins.php";
		$retour .= '?keyword='.$this->keyword;
		if (!empty($this->options) && is_array($this->options)) {
			foreach ($this->options as $k => $v) {
				$retour .= "&amp;".$k."=".$v;
			}
		}
		return $retour;
	}

	public function getListFormParams(){
		return array(
			'keyword'    => $this->keyword,
			'recherche1' => $this->keyword,
			'recherche2' => $this->keyword,
			'page'       => $this->page,
			'perPages'   => $this->perPages,
			'options'    => $this->options
		);
	}

	public function generalData(){
		return array(
			'keyword'                 => $this->keyword,
			'page'                    => $this->page,
			'perPages'                => $this->perPages,
			'options'                 => $this->options,
			'globalCountAdmins'      => $this->globalCountAdmins,
			'countAdmins'            => $this->countAdmins,
			'countSqlReturnedAdmins' => $this->countSqlReturnedAdmins,
			'countPages'              => $this->countPages
		);
	}

	// -------------------- Getters & Setters -------------------- //

	public function __get($var){
		if (!isset($this->$var)) {
			// if (isset($this->adminsNumbers[$var])) {
			// 	return $this->adminsNumbers[$var];
			// }elseif (isset($this->icamAndTheirAdmins[$var])) {
			// 	return $this->icamAndTheirAdmins[$var];
			// }elseif (isset($this->nightOptions[$var])) {
			// 	return $this->nightOptions[$var];
			// }else{
				return false;
			// }
		}else return $this->$var;
	}
}