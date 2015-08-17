<?php

class Ht_Acl extends Zend_Acl {

    const ROLE_GUEST = 'guest';
    const ROLE_USER = 'user';
    const ROLE_OPERATION = 'operation';
    const ROLE_ADMIN = 'admin';

    public function __construct(Zend_Config $config) {
        $roles = $config->acl->roles;
        $resources = $config->acl->resources;

        $this->_addRoles($roles);
        $this->_addResources($resources);
    }

    private function _addRoles($roles) {
        foreach ($roles as $name => $parents) {
            if (!$this->hasRole($name)) {

                if (empty($parents) || !$parents) {
                    $parents = null;
                } else {
                    $parents = explode(',', $parents);
                }
                $this->addRole(new Zend_Acl_Role($name), $parents);
            }
        }
    }

    private function _addResources($resources) {
        if (!is_object($resources)) {
            return;
        }
        foreach ($resources as $permissions => $modules) {
            foreach ($modules as $module => $controllers) {
                if ('all' == $module) {
                    $module = null;
                } else {
                    if (!$this->has($module)) {
                        $this->add(new Zend_Acl_Resource($module));
                    }
                }

                foreach ($controllers as $controller => $actions) {

                    if ('all' == $controller) {
                        $controller = $module;
                    } else {
                        $controller = ($module) ? $module . '_' . $controller : $controller;
                        if (!$this->has($controller)) {
                            $this->add(new Zend_Acl_Resource($controller));
                        }
                    }

                    foreach ($actions as $action => $role) {
                        if ($action == 'all') {
                            $action = null;
                        }

                        if (is_null($controller) && is_null($action)) {
                            if ($role !== self::ROLE_GUEST) {
                                $this->allow($role, null, null);
                            } else {
                                $this->deny($role, null, null);
                            }
                        } else {
                            if ($permissions == 'deny') {
                                $this->deny($role, $controller, $action);
                            }
                            if ($permissions == 'allow') {
                                $this->allow($role, $controller, $action);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns the rule type associated with the specified Resource, Role, and privilege
     * combination.
     *
     * If a rule does not exist or its attached assertion fails, which means that
     * the rule is not applicable, then this method returns null. Otherwise, the
     * rule type applies and is returned as either TYPE_ALLOW or TYPE_DENY.
     *
     * If $resource or $role is null, then this means that the rule must apply to
     * all Resources or Roles, respectively.
     *
     * If $privilege is null, then the rule must apply to all privileges.
     *
     * If all three parameters are null, then the default ACL rule type is returned,
     * based on whether its assertion method passes.
     *
     * @param  Zend_Acl_Resource_Interface $resource
     * @param  Zend_Acl_Role_Interface     $role
     * @param  string                      $privilege
     * @return string|null
     */
    protected function _getRuleType(Zend_Acl_Resource_Interface $resource = null, Zend_Acl_Role_Interface $role = null, $privilege = null) {
        //_print(__LINE__."::_getRuleType-\$resource::".$resource."<br />");
        //_print(__LINE__."::_getRuleType-\$privilege::".$privilege."<br />");
        // get the rules for the $resource and $role
        if (null === ($rules = $this->_getRules($resource, $role))) {
            return null;
        }

        // follow $privilege
        if (null === $privilege) {
            if (isset($rules['allPrivileges'])) {
                $rule = $rules['allPrivileges'];
            } else {
                return null;
            }
        } else if (!isset($rules['byPrivilegeId'][$privilege])) {
            return null;
        } else {
            $rule = $rules['byPrivilegeId'][$privilege];
        }

        // check assertion first
        if ($rule['assert']) {
            $assertion = $rule['assert'];
            $assertionValue = $assertion->assert($this, ($this->_isAllowedRole instanceof Zend_Acl_Role_Interface) ?
                    $this->_isAllowedRole :
                    $role, ($this->_isAllowedResource instanceof Zend_Acl_Resource_Interface) ?
                    $this->_isAllowedResource :
                    $resource, $this->_isAllowedPrivilege);
        }

        if (null === $rule['assert'] || $assertionValue) {
            return $rule['type'];
        } else if (null !== $resource || null !== $role || null !== $privilege) {
            return null;
        } else if (self::TYPE_ALLOW === $rule['type']) {
            return self::TYPE_DENY;
        } else {
            return self::TYPE_ALLOW;
        }
    }

    /**
     * Returns true if and only if the Role has access to the Resource
     *
     * The $role and $resource parameters may be references to, or the string identifiers for,
     * an existing Resource and Role combination.
     *
     * If either $role or $resource is null, then the query applies to all Roles or all Resources,
     * respectively. Both may be null to query whether the ACL has a "blacklist" rule
     * (allow everything to all). By default, Zend_Acl creates a "whitelist" rule (deny
     * everything to all), and this method would return false unless this default has
     * been overridden (i.e., by executing $acl->allow()).
     *
     * If a $privilege is not provided, then this method returns false if and only if the
     * Role is denied access to at least one privilege upon the Resource. In other words, this
     * method returns true if and only if the Role is allowed all privileges on the Resource.
     *
     * This method checks Role inheritance using a depth-first traversal of the Role registry.
     * The highest priority parent (i.e., the parent most recently added) is checked first,
     * and its respective parents are checked similarly before the lower-priority parents of
     * the Role are checked.
     *
     * @param  Zend_Acl_Role_Interface|string     $role
     * @param  Zend_Acl_Resource_Interface|string $resource
     * @param  string                             $privilege
     * @uses   Zend_Acl::get()
     * @uses   Zend_Acl_Role_Registry::get()
     * @return boolean
     */
    public function isAllowed($role = null, $resource = null, $privilege = null) {
        // reset role & resource to null
        $this->_isAllowedRole = null;
        $this->_isAllowedResource = null;
        $this->_isAllowedPrivilege = null;

        if (null !== $role) {
            // keep track of originally called role
            $this->_isAllowedRole = $role;
            $role = $this->_getRoleRegistry()->get($role);
            if (!$this->_isAllowedRole instanceof Zend_Acl_Role_Interface) {
                $this->_isAllowedRole = $role;
            }
        }

        if (null !== $resource) {
            // keep track of originally called resource
            $this->_isAllowedResource = $resource;
            $resource = $this->get($resource);
            if (!$this->_isAllowedResource instanceof Zend_Acl_Resource_Interface) {
                $this->_isAllowedResource = $resource;
            }
        }

        if (null === $privilege) {
            // query on all privileges
            do {
                // depth-first search on $role if it is not 'allRoles' pseudo-parent
                if (null !== $role && null !== ($result = $this->_roleDFSAllPrivileges($role, $resource, $privilege))) {
                    return $result;
                }

                // look for rule on 'allRoles' psuedo-parent
                if (null !== ($rules = $this->_getRules($resource, null))) {
                    $byPrivilegeId = array_keys($rules['byPrivilegeId']);
                    foreach ($byPrivilegeId as $privilege) {
                        if (self::TYPE_DENY === $this->_getRuleType($resource, null, $privilege)) {
                            return false;
                        }
                    }
                    if (null !== ($ruleTypeAllPrivileges = $this->_getRuleType($resource, null, null))) {
                        return self::TYPE_ALLOW === $ruleTypeAllPrivileges;
                    }
                }

                // try next Resource
                $resource = $this->_resources[$resource->getResourceId()]['parent'];
            } while (true); // loop terminates at 'allResources' pseudo-parent
        } else {
            $this->_isAllowedPrivilege = $privilege;
            //_print("PASS::".__LINE__."<br />");
            // query on one privilege
            do {
                //_print(__LINE__."::isAllowed::".$privilege."<br />");
                // depth-first search on $role if it is not 'allRoles' pseudo-parent
                if (null !== $role && null !== ($result = $this->_roleDFSOnePrivilege($role, $resource, $privilege))) {
                    //_print(__LINE__."::isAllowed::".$result);
                    //exit;
                    return $result;
                }
                // look for rule on 'allRoles' pseudo-parent
                if (null !== ($ruleType = $this->_getRuleType($resource, null, $privilege))) {
                    //_print(__LINE__."\$ruleType::".$ruleType);
                    return self::TYPE_ALLOW === $ruleType;
                } else if (null !== ($ruleTypeAllPrivileges = $this->_getRuleType($resource, null, null))) {
                    //_print(__LINE__."\$ruleTypeAllPrivileges::");
                    //_print($ruleTypeAllPrivileges);
                    return self::TYPE_ALLOW === $ruleTypeAllPrivileges;
                }

                // try next Resource
                $resource = $this->_resources[$resource->getResourceId()]['parent'];

                //_print(__LINE__);
                //_print($resource);
            } while (true); // loop terminates at 'allResources' pseudo-parent
        }
    }

    /**
     * Performs a depth-first search of the Role DAG, starting at $role, in order to find a rule
     * allowing/denying $role access to a $privilege upon $resource
     *
     * This method returns true if a rule is found and allows access. If a rule exists and denies access,
     * then this method returns false. If no applicable rule is found, then this method returns null.
     *
     * @param  Zend_Acl_Role_Interface     $role
     * @param  Zend_Acl_Resource_Interface $resource
     * @param  string                      $privilege
     * @return boolean|null
     * @throws Zend_Acl_Exception
     */
    protected function _roleDFSOnePrivilege(Zend_Acl_Role_Interface $role, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
        //_print(__LINE__."_roleDFSOnePrivilege::".$privilege."<br />");
        if (null === $privilege) {
            /**
             * @see Zend_Acl_Exception
             */
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception('$privilege parameter may not be null');
        }

        $dfs = array('visited' => array(), 'stack' => array());

        if (null !== ($result = $this->_roleDFSVisitOnePrivilege($role, $resource, $privilege, $dfs))) {
            return $result;
        }

        while (null !== ($role = array_pop($dfs['stack']))) {
            if (!isset($dfs['visited'][$role->getRoleId()])) {
                if (null !== ($result = $this->_roleDFSVisitOnePrivilege($role, $resource, $privilege, $dfs))) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Visits an $role in order to look for a rule allowing/denying $role access to a $privilege upon $resource
     *
     * This method returns true if a rule is found and allows access. If a rule exists and denies access,
     * then this method returns false. If no applicable rule is found, then this method returns null.
     *
     * This method is used by the internal depth-first search algorithm and may modify the DFS data structure.
     *
     * @param  Zend_Acl_Role_Interface     $role
     * @param  Zend_Acl_Resource_Interface $resource
     * @param  string                      $privilege
     * @param  array                       $dfs
     * @return boolean|null
     * @throws Zend_Acl_Exception
     */
    protected function _roleDFSVisitOnePrivilege(Zend_Acl_Role_Interface $role, Zend_Acl_Resource_Interface $resource = null,
        $privilege = null, &$dfs = null) {
        if (null === $privilege) {
            /**
             * @see Zend_Acl_Exception
             */
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception('$privilege parameter may not be null');
        }

        if (null === $dfs) {
            /**
             * @see Zend_Acl_Exception
             */
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception('$dfs parameter may not be null');
        }

        //_print(__LINE__."::_roleDFSVisitOnePrivilege::".$privilege."<br />");
        if (null !== ($ruleTypeOnePrivilege = $this->_getRuleType($resource, $role, $privilege))) {
            return self::TYPE_ALLOW === $ruleTypeOnePrivilege;
        } else if (null !== ($ruleTypeAllPrivileges = $this->_getRuleType($resource, $role, null))) {
            //_print(__LINE__."::\$ruleTypeAllPrivileges::".$ruleTypeAllPrivileges);
            return self::TYPE_ALLOW === $ruleTypeAllPrivileges;
        }
        //_print(__LINE__."::_roleDFSVisitOnePrivilege::".$ruleTypeOnePrivilege."<br />");
        $dfs['visited'][$role->getRoleId()] = true;
        foreach ($this->_getRoleRegistry()->getParents($role) as $roleParent) {
            $dfs['stack'][] = $roleParent;
        }
        return null;
    }

}
