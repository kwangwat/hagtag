<?php
/**
 * @see sfEventDispatcher
 */
require_once realpath(APPLICATION_PATH . '/../vendor/symfony/event_dispatcher/sfEventDispatcher.php');
require_once "Ht/Model/SystemSetting.php";

class Default_IndexController extends Zend_Controller_Action {

    const DEFAULT_LANG = "en";
    const ERR_CODE_USER_PASS_INVALID = 1;
    const ERR_CODE_SYSTEM_ERROR = 2;
    const ERR_CODE_DATE_TIME_NOT_MATCH = 3;
    const ERR_CODE_NOT_ALLOW_USER = 4;
    const ERR_CODE_INACTIVE_USER = 5;
    const ERR_CODE_SESSION_TIMEOUT = 6;
    const ERR_CODE_SESSION_EXISTS = 7;

    protected $_IDToken = "Hagtag";
    protected $_dispatcher;

    /**
     * Auth mode Single or Multiple
     *
     * @var String
     */
    protected $_logAccess = 0;
    protected $_baseUrl = "/default/index/index";
    protected $_firstPage = "/home/portal/index";

    public function init() {
        parent::init();

        $this->setDispatcher(new sfEventDispatcher());

        $this->_IDToken = $this->getSysSetting(Ht_Model_SystemSetting::KEY_CRYPT_KEY, $this->_IDToken);
        $this->_logAccess = (int)$this->getSysSetting(Ht_Model_SystemSetting::KEY_ACCESS_LOGGING, $this->_logAccess);

        $this->view->tr = Zend_Registry::get("tr");
        
    }
    
//     public function preDispatch() {
//         $this->_helper->lastDecline()->saveRequestUri();
    
//         // redirect to login action
//     }

    /**
     * @param sfEventDispatcher $dispatcher
     */
    public function setDispatcher(sfEventDispatcher $dispatcher) {
        $this->_dispatcher = $dispatcher;

        // Register Dispatcher
        Zend_Loader::loadClass("Ht_Utils_Logger");
        $logger = Ht_Utils_Logger::getInstance();
        $this->_dispatcher->connect('authentication_ldap.log', array($logger, 'save'));
        $this->_dispatcher->connect('authentication.log', array($logger, 'save'));
        $this->_dispatcher->connect('system.log', array($logger, 'save'));
    }

    /**
     * Get system setting
     *
     * @param string $key
     * @param mixed|null $default
     * @return string
     */
    public function getSysSetting($key, $default = null) {
        return isset($this->view->sysSetting[$key]) ? $this->view->sysSetting[$key] : $default;
    }

    public function redirecWithErrorCode($errorCode) {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect($this->_baseUrl . "/error/" . $errorCode);
    }

    public function indexAction() {
        // action body
        
        $this->_helper->layout->disableLayout();
        $this->view->error = $this->_getParam('error');
        $this->view->IDToken0 = base64_encode("IDToken0=" . $this->_IDToken); 
    }
    
    public function registerAction() {
    	$this->_helper->layout->disableLayout();
    }

    public function loginAction() {
        $this->_helper->viewRenderer->setNoRender();

        if($this->getRequest()->isPost()) {
            try {
                // collect the data from the user
                $f = new Zend_Filter_StripTags();
                $username = $f->filter($this->_request->getPost("IDToken1"));
                $password = $f->filter($this->_request->getPost("IDToken2"));
                $remember = $f->filter($this->_request->getPost("RememberMe"));

                if($remember == 1) {
                    try {
                        setcookie("_iacl", $username, time() + 2592000, "/");
                    } catch(Exception $exc) {
                        $exc = null;
                    }
                }
                //setcookie("_lang", "en", time() + 2592000, "/");
                $this->_processAuthWithDb($username, $password);
            } catch(Exception $e) {
                $this->_dispatcher->notify(new sfEvent($this, 'system.log', array(
                    'message' => array($e->getMessage()),
                    'priority' => Zend_Log::ERR
                )));

                throw new Exception($e->getMessage(), $e->getCode());
                return;
            }
        } else {
            $this->_dispatcher->notify(new sfEvent($this, 'system.log', array(
                'message' => array("Wrong method call login action."),
                'priority' => Zend_Log::NOTICE
            )));
            $this->redirecWithErrorCode(self::ERR_CODE_SYSTEM_ERROR);
        }
        return;
    }

    protected function _processAuthWithDb($username, $password) {

        $defaultHomepage = Ht_Utils_SystemSetting::getSetting(Ht_Model_SystemSetting::KEY_DEFAULT_PAGE, $this->_firstPage);

        $db = Zend_Db_Table::getDefaultAdapter();

        // setup Zend_Auth adapter for a database table;
        Zend_Loader :: loadClass("Ht_Auth_Adapter_DbTable");
        $adapter = new Ht_Auth_Adapter_DbTable($db, 'ht_user', 'use_login', 'use_password', 'MD5(?)');

        // Set the input credential values to authenticate against
        $adapter->setIdentity($username);
        $adapter->setCredential($password);

        // do the authentication
        //Zend_Loader::loadClass('Zend_Auth');
        $auth = Zend_Auth::getInstance();

        //Zend_Loader::loadClass("Zend_Auth_Storage_Session");
        $auth->setStorage(new Zend_Auth_Storage_Session());
        
        if($auth->hasIdentity()) {
            $auth->clearIdentity();
        }
        
        $result = $auth->authenticate($adapter);
        
        if($result->isValid()) {
            $storage = $auth->getStorage();
            // store the identity as an object where the password column has
            // been omitted
            $resultRow = $adapter->getResultRowObject(null, 'use_password');

            if((int)$resultRow->u_status == 'N') {
                $this->redirecWithErrorCode(self::ERR_CODE_INACTIVE_USER);
                return;
            }

            // Set access log.
            $this->setSystemAccessLog($resultRow);
            $storage->write($resultRow);

            $this->_dispatcher->notify(new sfEvent($this, 'authentication.log', array(
                'message' => array(sprintf('`%s` login success.', trim($resultRow->u_name . ' ' . $resultRow->u_lastname))),
                'priority' => 6
            )));
            //$this->_helper->lastDecline();
            $this->_redirect($defaultHomepage);
        } else {
            $this->_dispatcher->notify(new sfEvent($this, 'authentication.log', array(
                'message' => array(
                    sprintf('User name `%s` login fail.', trim($username)),
                    implode(", ", (array)$result->getMessages())
                ),
                'priority' => Zend_Log::WARN
            )));

            $this->redirecWithErrorCode(self::ERR_CODE_USER_PASS_INVALID);
        }

        return;
    }

    protected function setSystemAccessLog(&$data) {
        if($this->_logAccess === 0 || !$data->use_id || isset($data->access_id)) {
            return $this;
        }

        require_once realpath(APPLICATION_PATH . '/../vendor/phpsniff-2.1.3/phpSniff.class.php');
        $_sniff = new phpSniff();
        $clientInfo = $_sniff->property();

        /**
         * @see Ht/Utils/Generater.php
         */
        try {
            Zend_Loader::loadClass("Ht_Utils_Generater");
            Zend_Loader::loadClass("Ht_Utils_Generater_Id");
            $generator = new Ht_Utils_Generater_Id(array(
                Ht_Utils_Generater::GENERATED_DIGIT32,
                Ht_Utils_Generater::GENERATED_INCLUDE_NUMBER,
                Ht_Utils_Generater::GENERATED_USE_LOWERLETTER,
                Ht_Utils_Generater::GENERATED_USE_UPPERLETTER
            ));
            $access_id = $generator->getGeneratedId();
        } catch(Exception $exc) {
            echo $exc->getMessage(), "<hr />";
            echo $exc->getTraceAsString();
        }

        $row = array(
            "access_id" => $access_id,
            "acc_id" => $data->u_id,
            "login_time" => new Zend_Db_Expr("NOW()"),
            "acc_ip" => $clientInfo["ip"],
            "acc_agent" => $clientInfo["ua"],
            "acc_browser" => $clientInfo["long_name"],
            "acc_os" => $clientInfo["os"]
        );
        unset($_sniff, $clientInfo);

        try {
            $sysaccess = new Default_Model_DbTable_SysAccess();
            $sysaccess->insert($row);

            $user = new Zend_Db_Table('ht_user');
            $where = $user->getAdapter()->quoteInto("use_id =?", $data->use_id);
            $user->update(array("use_lastlogin" => new Zend_Db_Expr("NOW()")), $where);
        } catch(Exception $exc) {
            $data->access_id = $access_id;
            return $this;
        }

        $data->access_id = $access_id;

        return $this;
    }

    public function logoutAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
        /* clear cookie language */
        $dfLang = Ht_Utils_SystemSetting::getSetting(Ht_Model_SystemSetting::KEY_DEFAULT_LANGUAGE, self::DEFAULT_LANG);
        setcookie("_lang", $dfLang, time() + 2592000, "/");
        try {
            $sysaccess = new Default_Model_DbTable_SysAccess();
            $sysaccess->update(array(
                "logout_time" => new Zend_Db_Expr("NOW()")
                    ), $sysaccess->getAdapter()->quoteInto("access_id=?", $identity->access_id));

            $this->_dispatcher->notify(new sfEvent($this, 'authentication.log', array(
                'message' => array(sprintf('`%s` logout success.', trim($identity->u_name . ' ' . $identity->u_lastname))),
                'priority' => Zend_Log::INFO
            )));

            $auth->clearIdentity();
        } catch(Exception $e) {
            $this->_dispatcher->notify(new sfEvent($this, 'authentication.log', array(
                'message' => array(
                    sprintf('`%s` logout error.', trim($identity->u_name . ' ' . $identity->u_lastname)),
                    $e->getMessage()
                ),
                'priority' => Zend_Log::ERR
            )));
        }

        $this->_redirect($this->_baseUrl);
    }

    public function requestPasswordAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $data = $this->_getParam('data');
        $config = $this->getInvokeArg('bootstrap')->getOption('mail');

        if(isset($config)) {
            if(isset($config["admin"]) && isset($config['host'])) {
                $mail = new Ht_Mail($config['host'], $config['port'] ? $config['port'] : 25);
                //$mail->setCharset('UTF-8');
                $mail->addTo($config["admin"], $config["adminName"]);
                $mail->setFrom($data["email"], $data["name"]);
                $mail->sentMail("Request Password", $data["message"]);
            }
        }

        $this->_dispatcher->notify(new sfEvent($this, 'authentication.log', array(
            'message' => array(
                sprintf('`%s` request new password.', trim($data["name"]))
            ),
            'priority' => Zend_Log::INFO
        )));
    }

}
