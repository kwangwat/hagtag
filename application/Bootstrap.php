<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initController() {
        $this->bootstrap('FrontController');
        $controller = $this->getResource('FrontController');
        $modules = $controller->getControllerDirectory();
        $controller->setParam('prefixDefaultModule', true);
        //Setup the Custom Helpers
        Zend_Controller_Action_HelperBroker::addPrefix('Ht_Helper');
        
        //$controller->addModuleDirectory(APPLICATION_PATH . '/modules');
        //Zend_Loader::loadClass("Ht_Modules_Loader");
        //$controller->registerPlugin(new Ht_Modules_Loader($modules));
        return $controller;
    }
    
    protected function _initAutoload() {
    
//         new Zend_Application_Module_Autoloader(array(
//                 'namespace' => 'Default',
//                 'basePath' => APPLICATION_PATH . "/modules/default"
//         ));
    
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);
    
        return $autoloader;
    }
    
    protected function _initView() {
        // Start initail view
        $this->bootstrap('layout');
        
        $config = $this->getOption('views');
        $resources = $this->getOption('resources');
        $view = new Zend_View();
        
        if(isset($resources['layout']['layoutPath'])){
            $view->assign('layoutRootPath', $resources['layout']['layoutPath']);
        }
        
        $this->bootstrap('db');
        Zend_Loader::loadClass('Ht_Utils_SystemSetting');
        $sysSetting = Ht_Utils_SystemSetting::getSettings();
        $view->assign('sysSetting', $sysSetting);
        $view->assign('profile', Zend_Auth::getInstance()->getIdentity());
        Zend_Loader::loadClass("Ht_Model_SystemSetting");
        $this->setSystemLogConfiguration($sysSetting);
        
        // use the viewrenderer to keep the code DRY
        // instantiate and add the helper in one go
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);
        $viewRenderer->setViewSuffix('phtml');
        // add it to the action helper broker
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        /**
         * Set inflector for Zend_Layout
         */
        $inflector = new Zend_Filter_Inflector(':script.:suffix');
        $inflector->addRules(array(
                ':script' => array(
                        'Word_CamelCaseToDash',
                        'StringToLower' 
                ),
                'suffix' => 'phtml' 
        ));
        
        // Initialise Zend_Layout's MVC helpers
        $this->getResource('layout')->setLayoutPath(realpath($resources['layout']['layoutPath']))
                                    ->setView($view)
                                    ->setContentKey('content')
                                    ->setInflector($inflector);
        
        return $this->getResource('layout')->getView();
    }

    /**
     * ERROR LOG
     *
     * to log errors in controllers use the logging action helper
     *
     * @return Zend_Log
     */
//     protected function _initLogging() {
    
//     	$logsDir = APPLICATION_PATH.'/logs/';
    
//     	if (!is_dir($logsDir)){
//     		@mkdir($logsDir, 0755);
//     	}
    
//     	// init error logger
//     	$logger = new Zend_Log();
//     	$writer = new Zend_Log_Writer_Stream($logsDir.'application.log');
//     	$logger->addWriter($writer);
    
//     	return $logger;
    
//     }
    
    /**
     * Initial configuration
     * @return Zend_Config
     */
    protected function _initConfig() {
    	$config = new Zend_Config($this->getOptions(), true);
    	Zend_Registry::set('config', $config);
    	return $config;
    }
    
    /**
     * Setup the application cache.
     *
     * @return Zend_Cache
     * @link http://framework.zend.com/manual/en/zend.cache.html
     */
//     protected function _initCache() {
//     	$this->bootstrap('Config');
//     	$appConfig = Zend_Registry::get('config');
//     	$cache = null;

//     	// only attempt to init the cache if turned on
//     	if ($appConfig->app->caching) {
    
//     		// get the cache settings
//     		$config = $appConfig->app->cache;
//     		try {
//     			$cache = Zend_Cache::factory(
//     					$config->frontend->adapter,
//     					$config->backend->adapter,
//     					$config->frontend->options->toArray()
//     			);
//     		} catch (Zend_Cache_Exception $e) {
//     			//@todo
//     		}
    
//     		Zend_Registry::set('cache', $cache);
//     		return $cache;
//     	}
//     }
    
    protected function _initLanguages() {
        $_COOKIE["_lang"] = isset($_COOKIE["_lang"]) ? $_COOKIE["_lang"] : $this->getSystemSetting(Ht_Model_SystemSetting::KEY_DEFAULT_LANGUAGE);
        $languageDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'languages';
        
        Zend_Registry::set('languageDir', $languageDir);
        
        $translate = new Zend_Translate('gettext', $languageDir);
        $translate->addTranslation($languageDir . DIRECTORY_SEPARATOR . $_COOKIE["_lang"] . DIRECTORY_SEPARATOR . 'messages.mo', $_COOKIE["_lang"]);
        $translate->setLocale($_COOKIE["_lang"]);
        Zend_Registry::set('tr', $translate);
        Zend_Registry::set('Zend_Translate', $translate);
    }
    
    /**
     * Configuration system
     * @return string
     */
    private function getSystemSetting() {
        $view = $this->getResource('layout')->getView();
        $defaultLanguageKey = Ht_Model_SystemSetting::KEY_DEFAULT_LANGUAGE;
        if($view && isset($view->sysSetting[$defaultLanguageKey])){
            return $view->sysSetting[$defaultLanguageKey];
        }
        return self::LANG_ENGLISH_KEY;
    }
    
    private function setSystemLogConfiguration($sysSetting = array()) {
        if(Zend_Registry::isRegistered("config")){
            $config = Zend_Registry::get("config");
            if(! isset($config['log'])){
                $config['log'] = $this->_setLogConfig($sysSetting, array(
                        "active" => 0,
                        "type" => "",
                        "path" => "" 
                ));
            }else{
                $config['log'] = $this->_setLogConfig($sysSetting, array(
                        "active" => $config['log']['active'],
                        "type" => $config['log']['type'],
                        "path" => $config['log']['path'] 
                ));
            }
            
            Zend_Registry::set("config", $config);
        }else{
            $config = array(
                    "log" => $this->_setLogConfig($sysSetting, array(
                            "active" => 0,
                            "type" => "",
                            "path" => "" 
                    )) 
            );
            Zend_Registry::set("config", $config);
        }
    }
    
    private function _setLogConfig($sysSetting, $default = array()) {
        return array(
                "active" => isset($sysSetting[Ht_Model_SystemSetting::KEY_SYSTEM_LOGGING]) ? $sysSetting[Ht_Model_SystemSetting::KEY_SYSTEM_LOGGING] : (int)$default['active'],
                "type" => isset($sysSetting[Ht_Model_SystemSetting::KEY_SYSTEM_LOGGING_TYPE]) ? $sysSetting[Ht_Model_SystemSetting::KEY_SYSTEM_LOGGING_TYPE] : $default['type'],
                "path" => isset($sysSetting[Ht_Model_SystemSetting::KEY_SYSTEM_LOGGING_PATH]) ? $sysSetting[Ht_Model_SystemSetting::KEY_SYSTEM_LOGGING_PATH] : $default['path'] 
        );
    }

}

