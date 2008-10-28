<?php

class Excore_Plugin_Acl extends Zend_Controller_Plugin_Abstract {
	
	/**
	 * @var Zend_Acl
	 */
	protected $_acl;
	protected $_role;
	
	protected $_denyAction = 'deny';
	
	public function __construct(Zend_Acl $acl, $role){
		$this->_setAcl($acl);
		$this->_setRole($role);
	}
	
	protected function _setAcl(Zend_Acl $acl){$this->_acl = $acl;}
	protected function _setRole($role){$this->_role = $role;}
	
	public function preDispatch(Zend_Controller_Request_Abstract $request){
		$resource = null;
		$module = $request->getModuleName();
		$controller = $request->getControllerName();
		$action = $request->getActionName();
		$front = Zend_Controller_Front::getInstance();
		$defaultModule = $front->getDefaultModule();
		
		if($module != '' && $module != $defaultModule) $resource .= $module . ':';
		$resource .= $controller;
		
		if($this->_acl->has(new Zend_Acl_Resource($resource)))
		if(!$this->_acl->isAllowed(new Zend_Acl_Role($this->_role), new Zend_Acl_Resource($resource), $action))
			$request->setModuleName($defaultModule)
					->setControllerName('error')
					->setActionName($this->_denyAction)
					->setParam('error_handler', true);
	}
}