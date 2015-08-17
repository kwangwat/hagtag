<?php
class Admin_ProductImagesController extends Zend_Controller_Action {
	

	
	public function indexAction() {
	    
	    $model = new Admin_Model_ProductImages();
	    $data = $model->getProductImages();
		$this->view->assign("data", $data);
	}
	
	
}