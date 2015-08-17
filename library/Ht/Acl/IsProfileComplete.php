<?php
/**
 * 
 * @author chatupon
 *
 */
class Ht_Acl_IsProfileComplete implements Zend_Acl_Assert_Interface {
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function assert(Zend_Acl $acl,
            Zend_Acl_Role_Interface $role = null,
            Zend_Acl_Resource_Interface $resource = null,
            $privilege = null)
    {
        // check the user's profile
        if (null === $this->user){
            return false;
        }
        return $this->user->isProfileComplete();  // for example
    }
}

//Then when defining your Acl object:
// $user = Zend_Auth::getInstance()->getIdentity();
// $assertion = new My_Acl_Assertion_IsProfileComplete($user);
// $acl->allow($role, $resource, $privilege, $assertion);