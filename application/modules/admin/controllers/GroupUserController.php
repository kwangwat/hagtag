<?php
class Admin_GroupUserController extends Zend_Controller_Action {
	

	public function indexAction() {
	    
	    $model = new Admin_Model_GroupUser();
	    $data = $model->getGroupUser();
		$this->view->assign("data", $data);
	}
	
	
}