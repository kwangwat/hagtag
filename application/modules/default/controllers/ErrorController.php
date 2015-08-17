<?php
/**
 * @see sfEventDispatcher
 */
require_once realpath(APPLICATION_PATH . '/../vendor/symfony/event_dispatcher/sfEventDispatcher.php');

class Default_ErrorController extends Zend_Controller_Action {
    const LOG_EOL = "\r\n";

    const CONF_KEY_404_ERROR_PAGE_MESSAGE = "HT_404_error_page_message";
    const CONF_KEY_APPLICATION_NAME = "HT_application_name";

    protected static $_dispatcher = null;

    public function init() {
        /* Initialize action controller here */
        $this->setDispatcher(new sfEventDispatcher());
    }

    public function getDispatcher() {
        if (null === self::$_dispatcher) {
            $this->setDispatcher(new sfEventDispatcher());
        }

        return self::$_dispatcher;
    }

    public function setDispatcher(sfEventDispatcher $dispatcher) {
        self::$_dispatcher = $dispatcher;

        // Register Dispatcher
        Zend_Loader::loadClass("Ht_Utils_Logger");
        $logger = Ht_Utils_Logger::getInstance();
        self::$_dispatcher->connect('system.log', array($logger, 'save'));
        self::$_dispatcher->connect('error.log', array($logger, 'save'));
    }

    public function errorAction() {
        $http_accept = $_SERVER['HTTP_ACCEPT'];
        
        $this->_redirecUnknownImage($http_accept);

        $errors = $this->_getParam('error_handler');
        $msg = $errors->exception->getMessage();
        $backTrace = $errors->exception->getTraceAsString();
        $this->_handleMessage($errors);
        $this->_helper->viewRenderer->setViewScriptPathSpec('error/'.$this->getResponse()->getHttpResponseCode().'.:suffix');
        
        $this->view->exception = $errors->exception;
        $this->view->request = $errors->request;
        $this->view->ui = 1;
        
        $logPath = realpath(APPLICATION_PATH . '/../log') . "/error";
        if (!is_dir($logPath)) {
            mkdir($logPath, 755, TRUE);
        }

        $log = new Zend_Log(new Zend_Log_Writer_Stream($logPath . "/" . date("Ymd") . "_applicationException.log"));
        $params1 = $this->_request->getParams();
        unset($params1["error_handler"]);

        $params = Zend_Json::encode($params1);
        $messages = array(
            $msg,
            $backTrace,
            $params,
            "HTTP_ACCEPT: " . $http_accept,
            ""
        );
        $log->err(implode(self::LOG_EOL, $messages));

        try {
            self::$_dispatcher->notify(new sfEvent($this, 'error.log', array(
                'message' => array($msg, $backTrace),
                'priority' => 3
            )));
            return;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    protected function _handleMessage(&$errors) {
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
                // if image request not found redirecto unknown image
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->title   = "Page not found !";
                $this->view->message = $this->getSysSetting(self::CONF_KEY_404_ERROR_PAGE_MESSAGE);
                break;

                // 404 error -- controller or action not found
                // $this->getResponse()->setHttpResponseCode(404);
                // $this->view->message = 'Route path not found!';
                // break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->title   = "Application error!";
                $this->view->message = $errors->exception->getMessage();
                break;
        }
    }

    protected function _redirecUnknownImage($http_accept) {
        if (preg_match("/image/i", $http_accept)) {
            $this->getResponse()->setHttpResponseCode(200);
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender();

            ob_end_clean();
            header("Content-Type: image/png;");
            $this->_redirect("/images/icons/unknown_icon.png");
        }
    }

    public function sendMailAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $data = $this->_request->getParams();
        try {
            Zend_Loader::loadClass("Ht_Utils");
            $mailer = Ht_Utils::getMailer();
            $admin = $this->__getAdministratorInfo();
            if ($mailer && $admin) {
                //$mail->setCharset('UTF-8');
                $mailer->addTo($admin->u_email, $admin->u_name . " " . $admin->u_lastname);
                $mailer->setFrom($data["email"], $data["name"]);

                $appName = $this->getSysSetting(self::CONF_KEY_APPLICATION_NAME);

                $subject = "{$appName}: Application Error!";
                $exception = nl2br($data["exception"]);
                $adminName = $admin->u_name . " " . $admin->u_lastname;
                $msg = $data["message"];
                $senderName = $data["name"];
                $message = <<<MESSAGE
<html>
<head><title>$subject</title></head>
<body>
<h3>Dear Khun $adminName</h3>
<br />
$msg
<br />
<hr />
$exception
<br />
<br />
Best Regards,
$senderName
</body>
</html>
MESSAGE;
                $mailer->sentMail($subject, $message);
            }

            $this->getDispatcher()->notify(new sfEvent($this, 'system.log', array(
                'message' => array(sprintf('`%s` sent error report success', trim($data["name"]))),
                'priority' => 6
            )));
        } catch (Exception $e) {
            $this->getDispatcher()->notify(new sfEvent($this, 'error.log', array(
                'message' => array(
                    sprintf('`%s` sent error report fail.', $data["name"]),
                    sprintf("Error Message: %s", $e->getMessage()),
                    sprintf("Stack trace: %s", $e->getTraceAsString())
                ),
                'priority' => 3
            )));

            echo "error";
            return;
        }

        echo "success";
    }

    private function __getAdministratorInfo() {
        Zend_Loader::loadClass("Isfa_Model_DbTable_Account");
        $accountModel = new Isfa_Model_DbTable_Account();
        return $accountModel->fetchRow("u_id=1");
    }

    public function getSysSetting($key, $default = null) {
        return isset($this->view->sysSetting[$key]) ? $this->view->sysSetting[$key] : $default;
    }
}

