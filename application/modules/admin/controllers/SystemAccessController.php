<?php
class Admin_SystemAccessController extends Zend_Controller_Action {
	

	public function indexAction() {
	    
	    $model = new Admin_Model_SystemAccess();
	    $data = $model->getAccesslog();
		$this->view->assign("data", $data);
	}
	
	
}