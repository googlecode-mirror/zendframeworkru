<?php

class Excore_Acl_Roles extends Zend_Db_Table_Abstract implements Excore_Acl_Roles_Interface {
	protected $_name = 'site_acl_roles';
	protected $_primary = 'id';
	
	protected $id;
	protected $inherits;
	
	public function getAll(){
		$roles = $this->fetchAll()->toArray();
		foreach($roles as $index => $role){
			if($role ['inherits'] === null)
				unset($roles [$index] ['inherits']);
			else
				$roles [$index] ['inherits'] = explode(',', $role ['inherits']);
		}
		return $roles;
	}
	
	public function get($roleId){
		$role = $this->find($roleId)->current()->toArray();
		if($role ['inherits'] === null)
			unset($role ['inherits']);
		else
			$role ['inherits'] = explode(',', $role ['inherits']);
		return $role;
	}
	
	public function exists($roleId){
		return (bool) $this->find($roleId)->count();
	}
}