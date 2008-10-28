<?php

interface Excore_Acl_Roles_Interface {
	public function getAll();
	public function get($roleId);
	public function exists($roleId);
}