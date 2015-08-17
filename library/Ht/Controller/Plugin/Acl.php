<?php

require_once 'Zend/Controller/Plugin/Abstract.php';
require_once 'Ht/Acl.php';

/**
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Ht_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract {
    const KEY_ROLE_GUEST = 'guest';
    const APP_ENV_TESTING = 'testing';

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        if(APPLICATION_ENV == self::APP_ENV_TESTING) {
            return;
        }

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $acl = new Ht_Acl($config);

        $role = $this->getRole();

        $controller = $request->getControllerName();
        $module = $request->getModuleName();

        $resource = $module . '_' . $controller;

        if(!$acl->has($resource)) {
            $resource = $module;
        }

        if(!$acl->has($resource)) {
            $resource = null;
        }

        $this->_isAllowed($request, $acl, $role, $resource);
        return;
    }

    public function getRole() {
        $role = self::KEY_ROLE_GUEST;
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()) {
            $user = $auth->getIdentity();
            if(is_object($user)) {
                if(!isset($user->role)) {
                    $user->role = 'user';
                }
                $role = $user->role;
            }
        }
        return $role;
    }

    protected function _isAllowed($request, $acl, $role, $resource) {
        $privellege = $request->getActionName();
        
        if(!$acl->isAllowed($role, $resource, $privellege)) {
            $request->setModuleName('default')
                    ->setControllerName('access-deny')
                    ->setActionName('index')
                    ->setDispatched(false);
            
            //$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
            //$redirector->gotoUrlAndExit('/admin/index/index');
        }
    }
}
