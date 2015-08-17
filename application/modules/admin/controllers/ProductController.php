<?php
class Admin_ProductController extends Zend_Controller_Action {
	

	public function indexAction() {
	    
	    $model = new Admin_Model_Product();
	    $data = $model->getProductList();
		$this->view->assign("data", $data);
	}
	
	
}