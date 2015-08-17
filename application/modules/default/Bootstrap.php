<?php
class Default_Bootstrap extends Zend_Application_Module_Bootstrap {
    protected $_moduleName = 'default';

    protected function _initAutoload() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => ucfirst($this->_moduleName),
            'basePath' => APPLICATION_PATH . "/modules/" . strtolower($this->_moduleName)
        ));
        return $autoloader;
    }

}
