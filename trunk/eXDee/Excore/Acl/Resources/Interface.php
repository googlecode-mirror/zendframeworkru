<?php

interface Excore_Acl_Resources_Interface {
	public function getAll();
	public function exists($resourceId);
}