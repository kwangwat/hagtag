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

class Ice_Controller_Plugin_AclDb extends Zend_Controller_Plugin_Abstract {

    protected static $_cache = null;

    public function preDispatch(Zend_Controller_Request_Abstract $request) {

        // Begin authorisation
        $auth = Zend_Auth::getInstance();
        $role = 'guest';

        if($auth->hasIdentity()){
            $user = $auth->getIdentity();
            if(is_object($user)){
                if(! isset($user->role)) {
                    $user->role = 'user';
                }
                $role = $user->role;
            }
        }

        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $module = $request->getModuleName();

        $resource = $module;
        $privellege = str_replace('-', '', $controller) . '_' . str_replace('-', '', $action);

        $cache = $this->getCache();
        $acl = null;

        if(! ($acl = $cache->load('acl_' . $role))){
            $acl = Isfa_Acl::getInstance($role);
            $cache->save($acl, 'acl_' . $role);
        }

        if(! $acl->has($resource)){
            $resource = null;
        }

        if(! $acl->isAllowed($role, $resource, $privellege)){
            $request->setModuleName('default')->setControllerName('index')->setActionName('index')->setDispatched(false);
        }

    }

    public function getCache() {
        if(null == self::$_cache){
            $frontendOptions = array(
                'lifetime'=>86400, // cache lifetime of 1 day
                'automatic_serialization'=>true
            );

            $backendOptions = array(
                'cache_dir'=>realpath(APPLICATION_PATH . '/../cache/metadata') // Directory where to put the cache files
            );

            // getting a Zend_Cache_Core object
            Zend_Loader::loadClass('Zend_Cache');

            self::$_cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
        }

        return self::$_cache;
    }

}
