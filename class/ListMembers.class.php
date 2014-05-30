<?php

require_once 'includes/_header.php';
require_once ROOT_PATH.'class/Member.class.php';

/**
* Class ListMembers
**/
class ListMembers{

	const perPages = 50;
	private $searchFields = array('nom', 'prenom', 'mail', 'badge_uid');
	private $exportFields = array('login','nom','prenom','mail','badge_uid','expiration_badge');

	private $keyword;
	private $page;
	private $perPages;
	private $options;
	private $globalCountMembers;
	private $countMembers;
	private $countSqlReturnedMembers;
	private $countPages;
	
	private $membersList;

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

		$this->globalCountMembers = Member::getMembersCount();
		if ($this->perPages > 0)
			$this->membersList = $this->getMembersList();
		else{
			$this->membersList = array();
			$this->countMembers = $this->globalCountMembers;
			$this->countPages  = 1;
		}
		$this->countSqlReturnedMembers = count($this->membersList);
	}

	private function getMembersList(){
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
		$this->countMembers = current($DB->queryFirst('SELECT COUNT(login) FROM '.Member::TABLE_NAME.' '.$where, $q));
		$this->countPages  = ceil($this->countMembers/$this->perPages);
		//*
		if ( empty($this->options)  || !empty($this->options['fields'])
			|| isset($this->options['selectAllTypes'],$this->options['type'])
			&& !( Member::getTypesCount() > count($this->options['type'])
				&& $this->options['selectAllTypes'] == 0 && !empty($this->options['type']))
		){
			if ($this->page > $this->countPages || $this->page < 1){
				$this->page = 1;
			}
		}//*/

		// --------------------- Sélection des member dans la base --------------------- //
	    $fields = (!empty($this->options['fields']))?((is_array($this->options['fields']))?implode(',', $this->options['fields']):$this->options['fields']):'*';
		$sql = 'SELECT '.$fields.' FROM '.Member::TABLE_NAME.' '.(($leftJoin != '')?$leftJoin:'').' '.$where;
		//*
		if (empty($this->options) || !empty($this->options['fields'])
			|| (isset($this->options['selectAllTypes'],$this->options['type'])
					&& !( Member::getTypesCount() > count($this->options['type'])
								&& $this->options['selectAllTypes'] == 0 && !empty($this->options['type'])))
		)
			$sql .= ' ORDER BY login ASC LIMIT '.(($this->page-1)*$this->perPages).','.$this->perPages;
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

	public function getMemberAsTr(){
		$motclef = $this->keyword;
		ob_start();
		if($this->countMembers){
			$token = Auth::generateToken();
	        foreach ($this->membersList as $member) { ?>
<tr>
  <td>
    <input id="member_<?= $member['login']; ?>" class="checkbox" type="checkbox" value="<?= $member['login']; ?>" name="members[]">
  </td>
  <td><?= (empty($motclef))? $member['login']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $member['login']); ?></td>
  <td><?= (empty($motclef))? $member['nom']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $member['nom']); ?></td>
  <td><?= (empty($motclef))? $member['prenom']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $member['prenom']); ?></td>
  <td><?= (empty($motclef))? $member['badge_uid']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $member['badge_uid']); ?></td>
  <td>
    <div class="pull-right">
      <a href="admin_edit_member.php?login=<?= $member['login'] ?>" title="Editer le member #<?= $member['login']; ?>"><i class="glyphicon glyphicon-pencil"></i></a>
      <a href="admin_liste_members.php?del_member=<?= $member['login']; ?>&amp;token=<?= $token; ?>" title="Supprimer le member #<?= $member['login']; ?>" onclick="return confirm('Voulez-vous vraiment supprimer ce member ?');"><i class="glyphicon glyphicon-trash"></i></a>              
    </div>
  </td>
</tr>
	        	<?php
	        }
	    }else{?>
	        <tr>
	          <td colspan="12">
	            <em>Aucun member trouvé.</em>
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
				<th>login</th>
				<th>Nom</th>
				<th>Prénom</th>
				<th>Badge UID</th>
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
	<em><span class="memberCount" title="nombre de membres affichés"><?= $this->countSqlReturnedMembers.'/'.$this->countMembers; ?></span> member</em>
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
					<a class="page" id="p<?= $i ?>" href="admin_liste_members.php?page=<?= $i ?>">
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

	public function exportMemberList(){
		global $DB;
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=data.csv');
		$MembersArray = $this->membersList;
		$output = fopen('php://output', 'w');

		// output the column headings
		fputcsv($output, $this->exportFields,';');
		foreach ($MembersArray as $member) {
			$array = array();
			foreach ($this->exportFields as $field) {
				$array[$field] = $member[$field];
			}
			fputcsv($output, $array,';');
		}
		fclose($output);
	}

	// -------------------- Informations functions -------------------- //

	public function getUrlParams($name){
		$retour = '';
		if ($name == 'export')
			$retour .= "export_liste_members.php";
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
			'globalCountMembers'      => $this->globalCountMembers,
			'countMembers'            => $this->countMembers,
			'countSqlReturnedMembers' => $this->countSqlReturnedMembers,
			'countPages'              => $this->countPages
		);
	}

	// -------------------- Getters & Setters -------------------- //

	public function __get($var){
		if (!isset($this->$var)) {
			// if (isset($this->membersNumbers[$var])) {
			// 	return $this->membersNumbers[$var];
			// }elseif (isset($this->icamAndTheirMembers[$var])) {
			// 	return $this->icamAndTheirMembers[$var];
			// }elseif (isset($this->nightOptions[$var])) {
			// 	return $this->nightOptions[$var];
			// }else{
				return false;
			// }
		}else return $this->$var;
	}
}