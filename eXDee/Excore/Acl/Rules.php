<?php

class Excore_Acl_Rules extends Zend_Db_Table_Abstract implements Excore_Acl_Rules_Interface {
	protected $_name = 'site_acl_rules';
	protected $_primary = 'id';
	
	protected $id;
	protected $roleId;
	protected $resourceId;
	protected $privileges;
	protected $assert;
	protected $type;
	
	public function getAll(){
		$rules = $this->fetchAll()->toArray();
		foreach($rules as $index => $rule)
			if($rule ['privileges'] !== null)
				$rules [$index] ['privileges'] = explode(',', $rule ['privileges']);
		return $rules;
	}
}