<?php 

/* 
 * Objet Formulaire
 * Il permet la création rapide et simple de formulaires, et de ses helpers.
 */
class form extends Functions{

	public $data      = array();
	private $validate = array();
	public $errors    = array();
    /**
     * Permet d'initialiser les $data
     * @param array $data
     * @param Boolean $clear
     */
    function set($data){
        $this->data = $data;
    }
    public function getFieldData($fieldName){
    	$return = '';
    	if (isset($this->data[$fieldName])) {
    		$return = $this->data[$fieldName];
    	}elseif(strpos($fieldName,'[') && strpos($fieldName,']')){
    		$ex = explode('[', str_replace(']', '', $fieldName));
    		$countExplode = count($ex);
    		if ($countExplode == 1) {
    			if (isset($this->data[$ex[0]])) 
    				$return = $this->data[$ex[0]];
    		}if ($countExplode == 2) {
    			if (isset($this->data[$ex[0]][$ex[1]])) 
    				$return = $this->data[$ex[0]][$ex[1]];
    		}if ($countExplode == 3) {
    			if (isset($this->data[$ex[0]][$ex[1]][$ex[2]])) 
    				$return = $this->data[$ex[0]][$ex[1]][$ex[2]];
    		}
    	}
    	return $return;
    }
    function setValidates($validate){
        $this->validate = $validate;
    }
    function setErrors($errors){
        $this->errors = $errors;
    }
    function getDate(){
    	global $date;
    	if (isset($this->data['date']) && is_array($this->data['date'])) {
    		$date = $this->data['date'];
    	}else if(isset($this->data['date_debut'],$this->data['date_fin'])){
    	  $time_deb=strtotime($this->data['date_debut']);
    	  $time_fin=strtotime($this->data['date_fin']);
    		$date=array(
    			'date_debut'=>date("Y-m-d",$time_deb),
    			'heure_debut'=>date("H:i:s",$time_deb),
    			'date_fin'=>date("Y-m-d",$time_fin),
    			'heure_fin'=>date("H:i:s",$time_fin)
    		);
    		$this->data['date'] = $date;
		}else {
	      $time_deb=strtotime('now');
    	  $time_fin=strtotime('now'.'+2 hours');
    		$date=array(
    			'date_debut'=>date("Y-m-d",$time_deb),
    			'heure_debut'=>date("H:i:s",$time_deb),
    			'date_fin'=>date("Y-m-d",$time_fin),
    			'heure_fin'=>date("H:i:s",$time_fin)
    		);
    		$this->data['date'] = $date;
		}
                return $date;
    }
    
    public function validates($data){
		$errors = array();
		foreach ($this->validate as $k => $v) {
			if (!isset($data[$k])) {
				$errors[$k] = $v['message'];
			}else {
				if ($v['rule'] == 'notEmpty') {
					if (empty($data[$k])) {
						$errors[$k] = $v['message'];
					}
				}elseif (!preg_match('/^'.$v['rule'].'$/', $data[$k])) {
					$errors[$k] = $v['message'];
				}
			}
		}
		$this->errors = $errors;
		if(empty($errors)){
			return true;
		}
		return false;
	}
	
	/**
	 * 
	 */
	/*
<div class="control-group ">
	<label class="control-label" for="inputWarning">Input with warning</label>
	<div class="controls">
		<input id="inputWarning" type="text">
		<span class="help-inline">Something may have gone wrong</span>
	</div>
</div>
	*/
	public function input($name,$label,$options=array()){
		$error = false;
		$classError = '';
		$idName = (!empty($options['id'])?$options['id']:'input'.$name);
		if (isset($this->errors[$name])) {
			$error = $this->errors[$name];
			$classError = 'error';
		}

		if(isset($options['value']) && $label=='hidden')
			$value = $options['value'];
		else
			$value = $this->getFieldData($name);

		if ($label=='hidden')
			return '<input type="hidden" name="'.$name.'" value="'.$value.'"/>';

		if (isset($options['type'],$options['checkboxNoClassControl']) && $options['type'] == 'checkbox')
			$html ='';
		else if (isset($options['type'],$options['class']) && ($options['class']=='wysiwyg') && $options['type']=='textarea')
			$html ='<div class="control-group '.$classError.'">
				<label class="control-label" for="'.$idName.'">'.$label.'</label>
				<div class="controls">';
		else
			$html ='<div class="control-group '.$classError.'">
				<label class="control-label" for="'.$idName.'">'.$label.'</label>
				<div class="controls">';
		if (!empty($options['append']))
        	$html .= ' '.$options['append'].' ';
		if (isset($options['datalist'])) {
			$options['list']="liste-".$name;
		}	
		$attr = ' ';
		foreach ($options as $k => $v) { if (!in_array($k,array('data','type','helper','datalist','append','prepend','input-prepend','input-append','selected','checkboxNoClassControl'))){
				$attr .= $k.'="'.$v.'" ';
		}}
		if (!isset($options['class'])) {
			$attr .= 'class="span3 "';
		}
		if (!isset($options['type']) && $name == 'email' || $name == 'mail') {
			$html.= '<div class="input-prepend">
				<span class="add-on">@</span><input type="text" name="'.$name.'" id="'.$idName.'" value="'.$value.'" '.$attr.'/>
			</div>';
		}else if (!isset($options['type']) && $name == 'date') {
			$html.= '<div class="input-prepend">
				<span class="add-on"><i class="icon-calendar ui-datepicker-trigger"></i></span><input type="text" name="'.$name.'" id="'.$idName.'" value="'.$value.'" '.$attr.'/>
			</div>';
		}else if (!isset($options['type']) && (isset($options['input-prepend']) || isset($options['input-append']))) {
			$html.= '<div class="input-prepend input-append">';
			if (isset($options['input-prepend']) && !is_array($options['input-prepend'])) {
				if (!preg_match('/span|button|input/', $options['input-prepend']))
					$html.= '<span class="add-on">'.$options['input-prepend'].'</span>';
				else
					$html.= $options['input-prepend'];
			}elseif (isset($options['input-prepend']) && is_array($options['input-prepend'])) {
				foreach ($options['input-prepend'] as $inkey => $inval) {
					if (($inkey == 'button' || $inkey == 'btn') && is_array($inval)) {
						$html.= '<button class="btn';
						if(!empty($inval['class'])) $html.= ' '.$inval['class'].' ';
						$html.= '" type="button">'.$inval['label'].'</button>';
					}elseif(!is_array($inval)){
						if (!preg_match('/span|button|input|select/', $inval))
							$html.= '<span class="add-on">'.$inval.'</span>';
						else
							$html.= $inval;
					}
				}
			}
			$html.= '<input type="text" name="'.$name.'" id="'.$idName.'" value="'.$value.'" '.$attr.'/>';
			if (isset($options['input-append']) && !is_array($options['input-append'])) {
				if (!preg_match('/span|button|input/', $options['input-append']))
					$html.= '<span class="add-on">'.$options['input-append'].'</span>';
				else
					$html.= $options['input-append'];				
			}elseif (isset($options['input-append']) && is_array($options['input-append'])) {
				foreach ($options['input-append'] as $inkey => $inval) {
					if (($inkey == 'button' || $inkey == 'btn') && is_array($inval)) {
						$html.= '<button class="btn';
						if(!empty($inval['class'])) $html.= ' '.$inval['class'].' ';
						$html.= '" type="button">'.$inval['label'].'</button>';
					}elseif(!is_array($inval)){
						if (!preg_match('/span|button|input|select/', $inval))
							$html.= '<span class="add-on">'.$inval.'</span>';
						else
							$html.= $inval;
					}
				}
			}
			$html.= '</div>';
		}else if (!isset($options['type'])) {
			$html.= '<input type="text" name="'.$name.'" id="'.$idName.'" value="'.$value.'" '.$attr.'/>';
		}elseif($options['type'] == 'textarea'){
			if (isset($options['class']) && $options['class']=='wysiwyg') {$html.= '<div class="clear"></div>';}
			$html.= '<textarea name="'.$name.'" id="'.$idName.'" '.$attr.'>'.$value.'</textarea>';
		}elseif($options['type'] == 'checkbox' || $options['type'] == 'radio'){
			if (isset($options['value'])) {
				foreach ($options['value'] as $k => $v) {
				   	$html .= '<label class="'.$options['type'].(isset($options['inline'])?' inline':'').'">';
				   	$html .= '<input type="'.$options['type'].'" id="'.$name.'['.$k.']" name="'.(($options['type'] == 'radio')?$name:$name.'['.$k.']').'" value="'.(($options['type'] == 'radio')?$k:'1').'"';
				   	if (!empty($value) && !isset($options['selected']))
				   		$html .= ' checked="checked" ';
				   	elseif ((isset($options['selected']) && ($k == $options['selected'] || $options['selected'] == $v))){
				   		$html .= ' checked="checked" ';
				   	}
				   	$html .= '/>';
				   	$html .= $v.'</label>';
				}
			}else {
				$html.= '<label class="'.$options['type'].' inline"
				'.((isset($options['type'],$options['checkboxNoClassControl']) && $options['type'] == 'checkbox')?' for="'.$idName.'"':'').'>
					<input type="hidden" name="'.$name.'" value="0"/>
					<input type="checkbox" '.((!empty($options['class']))?'class="'.$options['class'].'"':'').' id="'.$idName.'" name="'.$name.'" value="1" '.((!empty($value))?'checked="checked"':'').'/>
					'.((isset($options['type'],$options['checkboxNoClassControl']) && $options['type'] == 'checkbox')?$label:'').'
				</label>';
			}
		}elseif($options['type'] == 'file'){
			$html.= '<input type="file" class="input-file" id="'.$idName.'" name="'.$name.'" '.$attr.'/>';
		}elseif($options['type'] == 'password'){
			$html.= '<input type="password" id="'.$idName.'" name="'.$name.'" value="'.$value.'" '.$attr.'/>';
		}
		if (isset($options['datalist'])) {
			$html.='<datalist id="'.$options['list'].'">';
			foreach ($options['datalist'] as $value) {
				$html.='<option value="'.$value.'" label="'.$value.'"></option>';
			}
			$html.='</datalist>';
		}
		if ($error){
			if (isset($options['type']) && ($options['type'] != 'checkbox' || $options['type'] != 'radio') && empty($options['helper-block']))
				$html .= '<span class="help-inline '.$options['type'].' inline">'.$error.'</span>';
			else if(!empty($options['helper-block']))
				$html .= '<p class="help-block">'.$error.'</p>';
			else
				$html .= '<span class="help-inline">'.$error.'</span>';
		}else if(!empty($options['helper']) || !empty($options['helper-inline'])){
			if (!empty($options['helper-inline'])) $options['helper'] = $options['helper-inline'];
	        if (isset($options['type']) && ($options['type'] != 'checkbox' || $options['type'] != 'radio'))
				$html .= '<span class="help-inline '.$options['type'].' inline">'.$options['helper'].'</span>';
			else
				$html .= '<span class="help-inline">'.$options['helper'].'</span>';
    	}else if(!empty($options['helper-block'])){
    		$html .= '<p class="help-block">'.$options['helper-block'].'</p>';
        }
        if (!empty($options['append']))
        	$html .= ' '.$options['append'].' ';
        
        if (isset($options['type'],$options['checkboxNoClassControl']) && $options['type'] == 'checkbox')
			$html .='';
		else
        	$html.='</div></div>';			

		return $html;
	}

	/**
     * Créé un imput select=>option
     * @param string $field Champ de base
     * @param string $label Label à afficher
     * @param array $options Options du select 'valeur'=>'nom associé' ou bien un 'optgroup' => 'nom associé'
     * @return string
     */
    function select($field,$label,$options){
    	$error = false;
		$classError = '';
		if (isset($this->errors[$field])) {
			$error = $this->errors[$field];
			$classError = 'error';
		}
        $value = $this->getFieldData($field);

        $r = '<div class="control-group" '.$classError.'><label class="control-label" for="select'.$field.'">'.$label.'</label>';
        $r.= '<div class="controls"><select name="'.$field.'" id="'.(!empty($options['id'])?$options['id']:'select'.$field).'">';
        foreach ($options['data'] as $k => $v) {
        	if (is_array($v)) {
        		$r .= '<optgroup label="'.$k.'">';
        			foreach ($v as $key => $val) {
        				if($key == $value)
			                $r .= '<option value="'.$key.'" selected="selected">'.$val.'</option>';
			            else
			                $r .= '<option value="'.$key.'">'.$val.'</option>';
        			}
        		$r .= '</optgroup>';
        	}else{
        		if($k == $value)
	                $r .= '<option value="'.$k.'" selected="selected">'.$v.'</option>';
	            else
	                $r .= '<option value="'.$k.'">'.$v.'</option>';
            }
        }
        $r.= '</select>';
        if (!empty($error)) {
			$r .= '<span class="help-inline">'.$error.'</span>';
		}else if(!empty($options['helper'])){
            $r .= '<span class="help-inline"><em>'.$options['helper'].'</em></span>';
        }
        $r .= '</div></div>';

        return $r;
    }

    /**
     * Returns a select to choose rather male or female option
     * @param  string $name='sexe'        The name of your select
     * @param  string $class='input-mini' The class you want your select to have
     * @return string <select name="'.$name.'" class="'.$class.'"><option value="male">Male</option><option>Female</option></select>
     */
    static function selectMaleFemale($name='sexe',$class='input-mini'){
    	return form::simpleSelect($name,array('class'=>$class,'data'=>array('M'=>'M.','Mme'=>'Mme.')));
    }

    static function simpleSelect($name,$options){
    	if(isset($options['value']))$options['data'] = $options['value'];
    	if(isset($options['options']))$options['data'] = $options['options'];
    	$attr = ' ';
		foreach ($options as $k => $v) { if (!in_array($k,array('data','type','helper','datalist','append','prepend','input-prepend','input-append','selected'))){
				$attr .= $k.'="'.$v.'" ';
		}}
    	$select = '<select name="'.$name.'" id="'.(!empty($options['id'])?$options['id']:'select'.$name).'" class="'.((!empty($options['class']))?$options['class']:'').'" '.$attr.'>';
    	foreach ($options['data'] as $k => $v) {
        	if (is_array($v)) {
        		$select .= '<optgroup label="'.$k.'">';
        			foreach ($v as $key => $val) {
        				if(isset($options['selected']) && !is_array($options['selected']) && (isset($options['data'][$options['selected']]) || in_array($options['selected'], $options['data'])) || $options['selected'] == 'all')
			                $select .= '<option value="'.$key.'" selected="selected">'.$val.'</option>';
			            else if(isset($options['selected']) && is_array($options['selected']) && in_array($val,$options['selected']) || in_array($key,$options['selected']))
			                $select .= '<option value="'.$key.'" selected="selected">'.$val.'</option>';
			            else
			                $select .= '<option value="'.$key.'">'.$val.'</option>';
        			}
        		$select .= '</optgroup>';
        	}else{
        		if(isset($options['selected']) && !is_array($options['selected']) && (isset($options['data'][$options['selected']]) || in_array($options['selected'], $options['data'])) || $options['selected'] == 'all')
	                $select .= '<option value="'.$k.'" selected="selected">'.$v.'</option>';
	            else if(isset($options['selected']) && is_array($options['selected']) && in_array($v,$options['selected']) || in_array($k,$options['selected']))
	                $select .= '<option value="'.$k.'" selected="selected">'.$v.'</option>';
	            else
	                $select .= '<option value="'.$k.'">'.$v.'</option>';
            }
        }
        $select .= '</select>';
    	return $select;
    }

}
?>