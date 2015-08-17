<?php
class Ht_Modules_Loader extends Zend_Controller_Plugin_Abstract {
    protected $_modules;

    public function __construct(array $modulesList) {
        $this->_modules = $modulesList;
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
        $module = $request->getModuleName();

        if(! isset($this->_modules[$module])){
            throw new Exception("Module does not exist!");
        }

        $bootstrapPath = $this->_modules[$module];

        //$bootstrapFile = dirname($bootstrapPath) . '/Bootstrap.php';
        $class = ucfirst($module) . '_Bootstrap';

        if(Zend_Loader::loadFile('Bootstrap.php', dirname($bootstrapPath)) && class_exists($class)){
            $bootstrap = new $class(new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/modules/' . $module . '/configs/module.ini'));
            $bootstrap->bootstrap();
        }
    }
}
