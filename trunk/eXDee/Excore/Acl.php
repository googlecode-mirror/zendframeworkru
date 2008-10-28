<?php
/**
 * Excore_Acl
 * 
 * Класс формирования и настройки объекта Zend_Acl
 * по данным из базы данных (по умолчанию)
 * 
 * @category Excore
 * @package Excore_Acl
 * @author eXDee
 * @version 1.0
 */

class Excore_Acl {
	
	/** 
	 * Объект Zend_Acl
	 * 
	 * @var Zend_Acl 
	 */
	protected $_acl;
	
	/**
	 * Объект доступа к ролям ACL
	 * 
	 * @var Excore_Acl_Roles_Interface
	 */
	protected $_roles;
	
	/**
	 * Объект доступа к ресурсам ACL
	 * 
	 * @var Excore_Acl_Resources_Interface
	 */
	protected $_resources;
	
	/**
	 * Объект доступа к правилам ACL
	 * 
	 * @var Excore_Acl_Rules_Interface
	 */
	protected $_rules;
	
	
	/**
	 * Объект типов Allow/Deny
	 * 
	 * @var array
	 */
	protected $_ruleTypes = array(
		'TYPE_ALLOW'	=> 'TYPE_ALLOW',
		'TYPE_DENY'		=> 'TYPE_DENY'
	);
	
	/**
	 * Объект Cache (Zend_Cache_Core)
	 * 
	 * @var Zend_Cache_Core
	 */
	protected $_cache;
	
	/**
	 * Идентификатор объекта Cache
	 * 
	 * @var string
	 */
	protected $_cacheId = 'EXCORE_ACL';
	
	/**
	 * Метод-конструктор класса Excore_Acl
	 * 
	 * Обеспечивает базовую конфигурацию
	 * всех необходимых объектов
	 * 
	 * @param Array $config
	 * @return Excore_Acl
	 */
	public function __construct(array $config = null){
		if(isset($config ['cache']))	$this->setCache($config ['cache']);
		if(isset($config ['cacheId']))	$this->setCacheId($config ['cacheId']);
		if($this->_loadCache())			return $this;
		
		if(isset($config ['roles'])){
			$this->setRoles($config ['roles']);
		}else{
			require_once 'Excore/Acl/Roles.php';
			$this->setRoles(new Excore_Acl_Roles());
		}
		
		if(isset($config ['resources'])){
			$this->setResources($config ['resources']);
		}else{
			require_once 'Excore/Acl/Resources.php';
			$this->setResources(new Excore_Acl_Resources());
		}
		
		if(isset($config ['rules'])){
			$this->setRules($config ['rules']);
		}else{
			require_once 'Excore/Acl/Rules.php';
			$this->setRules(new Excore_Acl_Rules());
		}
		
		if(isset($config ['types']))	$this->setTypes($config ['types']);
		
		return $this;
	}
	
	/**
	 * Метод создающий объект Zend_Acl
	 * @return void
	 */
	protected function _build(){
		if($this->_acl) return;
		require_once 'Zend/Acl.php';
		$this->_acl = new Zend_Acl();
		$this->_loadRoles();
		$this->_loadResources();
		$this->_loadRules();
		$this->_saveCache();
		return;
	}
	
	/**
	 * Метод установки обекта Cache (Zend_Cache_Core)
	 * 
	 * @param Zend_Cache_Core $cache
	 * @return Excore_Acl
	 */
	public function setCache(Zend_Cache_Core $cache){
		$this->_cache = $cache;
		return $this;
	}
	
	/**
	 * Метод установки идентификатора
	 * объекта Cache
	 * 
	 * @param String $cacheId
	 * @return Excore_Acl
	 */
	public function setCacheId($cacheId){
		$this->_cacheId = $cacheId; 
		return $this;
	}
	
	
	/**
	 * Метод установки объекта 
	 * шранилища ролей ACL
	 * 
	 * @param Excore_Acl_Roles_Interface $roles
	 * @return Excore_Acl
	 */
	public function setRoles(Excore_Acl_Roles_Interface $roles){
		$this->_roles = $roles; 
		return $this;
	}
	
	/**
	 * Метод установки объекта
	 * шранилища ресурсов ACL
	 * 
	 * @param Excore_Acl_Resources_Interface $resources
	 * @return Excore_Acl
	 */
	public function setResources(Excore_Acl_Resources_Interface $resources){
		$this->_resources = $resources; 
		return $this;
	}
	
	
	/**
	 * Метод установки объекта
	 * хранилища правил ACL
	 * 
	 * @param Excore_Acl_Rules_Interface $rules
	 * @return Excore_Acl
	 */
	public function setRules(Excore_Acl_Rules_Interface $rules){
		$this->_rules = $rules; 
		return $this;
	}
	
	/**
	 * Метод установки параметра
	 * типов правил Allow/Deny
	 * 
	 * @param Array $types
	 * @throws Excore_Acl_Rules_Exception
	 * @return Excore_Acl
	 */
	public function setTypes(array $types = array()){
		if(count($types) != 2 || !array_key_exists('TYPE_ALLOW', $types) || !array_key_exists('TYPE_DENY', $types)){
			require_once 'Excore/Acl/Rules/Exception.php';
			throw new Excore_Acl_Rules_Exception('Invalid rule types given');
		}
		$this->_ruleTypes = $types;
		return $this;
	}
	
	
	/**
	 * Метод при необходимости
	 * создающий и 
	 * возвращающий объект
	 * Zend_Acl
	 * 
	 * @return Zend_Acl
	 */
	public function get(){
		$this->_build(); 
		return $this->_acl;
	}
	
	/**
	 * Метод загружающий и устанавливающий 
	 * при налачии объект Zend_Acl из кэша (Cache)
	 * Возвращает true вслучае успешной загрузки
	 * и false вслучае неудачи
	 * 
	 * @return bool
	 */
	protected function _loadCache(){
		if(!$this->_cache) return false;
		$cache = $this->_cache->load($this->_cacheId);
		if($cache){
			$this->_acl = $cache;
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * Метод сохраняющий объект Zend_Acl
	 * в кэш (Cache)
	 * 
	 * @return void
	 */
	protected function _saveCache(){
		if($this->_cache)
			$this->_cache->save($this->_acl, $this->_cacheId);
	}
	
	/**
	 * Метод загружающий роли ACL
	 * из хранилища ролей в объект Zend_Acl
	 * 
	 * @return void
	 */
	protected function _loadRoles(){
		$roles = $this->_roles->getAll();
		foreach($roles as $role)	$this->_loadRole($role);
	}
	
	/**
	 * Метод загружающий одиночную роль
	 * 
	 * @param Array $role
	 * @param Array $inheritanceChain
	 * @throws Excore_Acl_Roles_Exception
	 * @return void
	 */
	protected function _loadRole(array $role, array $inheritanceChain = null){
		if($inheritanceChain === null) $inheritanceChain = array();
		if(in_array($role ['id'], $inheritanceChain)){
			$inheritanceChain [] = $role ['id'];
			throw new Excore_Acl_Roles_Exception('Role chaining detected : ' . implode('->', $inheritanceChain));
		} 
		if(is_array($role ['inherits'])){
			foreach($role ['inherits'] as $inherit){
				$inheritanceChain [] = $role ['id'];
				if(!$this->_roles->exists($inherit))
					throw new Excore_Acl_Roles_Exception("Role `$inherit` as parent of role `{$role ['id']}` was not found.");
				$this->_loadRole($this->_roles->get($inherit), $inheritanceChain);
			}
		}
		if(!$this->_acl->hasRole(new Zend_Acl_Role($role ['id'])))
		$this->_acl->addRole(new Zend_Acl_Role($role ['id']));
	}
	
	/**
	 * Метод загружающий ресурсы ACL
	 * из хранилища ресурсов в объект Zend_Acl
	 * 
	 * @return void
	 */
	protected function _loadResources(){
		$resources = $this->_resources->getAll();
		foreach($resources as $resource)
			if(!$this->_acl->has(new Zend_Acl_Resource($resource ['id'])))
				$this->_acl->add(new Zend_Acl_Resource($resource ['id']));
	}
	
	/**
	 * Метод загружающий правила ACL
	 * из хранилища правил в объект Zend_Acl
	 * 
	 * @throws Excore_Acl_Rules_Exception
	 * @return void
	 */
	protected function _loadRules(){
		$rules = $this->_rules->getAll();
		foreach($rules as $rule){
			if(!in_array($rule ['type'], $this->_ruleTypes))
				throw new Excore_Acl_Rules_Exception("Rule type `{$rule ['type']}` is invalid rule type for current settings");
			if(!$this->_acl->hasRole(new Zend_Acl_Role($rule ['roleId'])))
				throw new Excore_Acl_Rules_Exception("Role `{$rule ['roleId']}` found in rules storage, but was not in roles storage");			
			if(!$this->_acl->has(new Zend_Acl_Resource($rule ['resourceId'])))
				throw new Excore_Acl_Rules_Exception("Resource `{$rule ['resourceId']}` found in rules storage, but was not in resources storage");
			
			$assert = $rule ['assert'];
			if($assert !== null) $assert = new $assert();
			
			switch($rule ['type']){
				case $this->_ruleTypes ['TYPE_ALLOW']:
					$this->_acl->allow(new Zend_Acl_Role($rule ['roleId']), 
						new Zend_Acl_Resource($rule ['resourceId']), 
						$rule ['privileges'], 
						$assert);
					break;
				case $this->_ruleTypes ['TYPE_DENY']:
					$this->_acl->deny(new Zend_Acl_Role($rule ['roleId']), 
						new Zend_Acl_Resource($rule ['resourceId']), 
						$rule ['privileges'], 
						$assert);
					break;
			}
		}
	}
}