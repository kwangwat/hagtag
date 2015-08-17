<?php
/**
 *
 * @author Nattakorn Samnuan
 * @version
 */
require_once 'Zend/Controller/Plugin/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Ice_Controller_Plugin_PreDispatch extends Zend_Controller_Plugin_Abstract {
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        /*
        $front = Zend_Controller_Front::getInstance();
        $dispatcher = $front->getDispatcher();
        //$module = $dispatcher->getDefaultModule();
        $class = $dispatcher->getControllerClass($request);

        if (! $class) {
            $class = $dispatcher->getDefaultControllerClass($request);
        }

        $r = new ReflectionClass($class);
        $action = $dispatcher->getActionMethod($request);

        if (! $r->hasMethod($action)) {
            //$defaultAction = $dispatcher->getDefaultAction();
            //$controllerName = $request->getControllerName();
            $response = $front->getResponse();
            $response->setRedirect('/default/error/error');
            $response->sendHeaders();
            exit();
        }
        */
        // Identity

        $module = $request->getModuleName();
        _print($module);
        exit;
        if(! in_array($module, array(
            "default"
        ))){
            $auth = Zend_Auth::getInstance();
            if(! $auth->hasIdentity()){
                $front = Zend_Controller_Front::getInstance();
                $response = $front->getResponse();
                $response->setRedirect('/default/index/index');
                $response->sendHeaders();
            }
        }
    }
}

