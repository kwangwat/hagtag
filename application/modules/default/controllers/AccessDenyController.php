<?php
/**
 * @see sfEventDispatcher
 */
require_once realpath(APPLICATION_PATH . '/../vendor/symfony/event_dispatcher/sfEventDispatcher.php');

class Default_AccessDenyController extends Zend_Controller_Action {
    protected static $_dispatcher = null;

    public function init() {
        /* Initialize action controller here */
        $this->setDispatcher(new sfEventDispatcher());
    }

    public function getDispatcher() {
        if(null === self::$_dispatcher) {
            self::$_dispatcher = $this->setDispatcher(new sfEventDispatcher());
        }

        return self::$_dispatcher;
    }

    public function setDispatcher(sfEventDispatcher $dispatcher) {
        self::$_dispatcher = $dispatcher;

        // Register Dispatcher
        Zend_Loader::loadClass("Ht_Utils_Logger");
        $logger = Ht_Utils_Logger::getInstance();
        self::$_dispatcher->connect('authentication.log', array($logger, 'save'));
    }

    public function indexAction() {
        //Zend_Loader::loadClass("Ht_Utils");
        $this->_helper->layout->disableLayout();
        $profile = Ht_Utils::getProfile();
        if($profile) {
            self::$_dispatcher->notify(new sfEvent($this, 'authentication.log', array(
                'message'  => array(
                    sprintf('User name `%s` goto denied page.', $profile->u_name . " " . $profile->u_lastname)
                ),
                'priority' => 4
            )));
        }
        //$this->_redirect('/admin/index/index');
    }
}