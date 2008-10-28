<?php

class Excore_Acl_Resources extends Zend_Db_Table_Abstract implements Excore_Acl_Resources_Interface {
	protected $_name = 'site_acl_resources';
	protected $_primary = 'id';
	
	protected $id;
	
	public function getAll(){
		return $this->fetchAll()->toArray();
	}
	
	public function exists($resourceId){
		return (bool) $this->find($resourceId)->count();
	}
}