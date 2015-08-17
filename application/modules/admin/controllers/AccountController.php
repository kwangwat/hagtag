<?php
class Admin_AccountController extends Zend_Controller_Action {
	

	public function indexAction() {
	    
	    $model = new Admin_Model_Account();
	    $data = $model->getAccountUser();
		$this->view->assign("data", $data);
	}
	
	
}