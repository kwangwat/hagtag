<?php
class Admin_CategoryController extends Zend_Controller_Action {
	

	public function indexAction() {
	    
	    $model = new Admin_Model_Category();
	    $data = $model->getCategoryList();
		$this->view->assign("data", $data);
	}
	
	
}