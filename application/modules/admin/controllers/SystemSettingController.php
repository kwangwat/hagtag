<?php
class Admin_SystemSettingController extends Zend_Controller_Action {
	

	
	public function indexAction() {
	    
	    $model = new Admin_Model_SystemSetting();
	    $data = $model->getSystemSeting();
		$this->view->assign("data", $data);
	}
	
	
	public function deleteAction() {
	    $this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    $code = $this->_getParam("code", null);
	    if(!empty($code)) {
	        $model = new Admin_Model_SystemSetting();
	        $model->deletedata($code);
	    }
	    return;
	}
	
}