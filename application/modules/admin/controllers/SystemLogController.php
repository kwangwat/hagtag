<?php
class Admin_SystemLogController extends Zend_Controller_Action {
	

	
	public function indexAction() {
	    
	    $model = new Admin_Model_SystemLog();
	    $data = $model->getSystemlog();
		$this->view->assign("data", $data);
	}
	
	
}