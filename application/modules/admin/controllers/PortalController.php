<?php
class Admin_PortalController extends Zend_Controller_Action {
	
	public function getProfile() {
		return Zend_Auth::getInstance()->getIdentity();
	}
	
	public function indexAction() {
	    
	}
	
	public function newsAction() {
	     
	}
	
}