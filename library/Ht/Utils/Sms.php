<?php
/**
 * @see http://components.symfony-project.org/event-dispatcher/
 */
require_once realpath(APPLICATION_PATH . '/../vendor/symfony/event_dispatcher/sfEventDispatcher.php');

/**
 * @see Ht_Utils_Logger
 */
require_once 'Ht/Utils/Logger.php';

class Ht_Utils_Sms {
    //@todo push gateway here
    const SMS_PROXY_GATEWAY = "";

    protected static $_instance = null;
    protected $_gateway = null;
    protected $_message = '';
    protected $_sender = 'Hagtag System';
    protected $_receivers = array();
    protected $_dispatcher = null;
    protected $_connectionTimeout = 30;

    public function __construct($config = array()) {
        if (isset($config['sender'])) {
            $this->setSender($config['sender']);
        }

        if (isset($config['message'])) {
            $this->setMessage($config['message']);
        }

        if (isset($config['gateway'])) {
            $this->setGateway($config['gateway']);
        } else {
            $this->setGateway(self::SMS_PROXY_GATEWAY);
        }

        if (isset($config['receivers'])) {
            if (!is_array($config['receivers'])) {
                $this->setReceivers(array($config['receivers']));
            } else {
                $this->setReceivers($config['receivers']);
            }
        }

        if (isset($config['timeout']) && is_numeric($config['timeout'])) {
            $this->setConnectionTimeout($config['timeout']);
        }

        $this->init();
    }

    /**
     * Returns an instance of Ht_Utils_Sms
     *
     * Singleton pattern implementation
     *
     * @return Ht_Utils_Sms Provides a fluent interface
     */
    public static function getInstance($config = array()) {
        if (null === self::$_instance) {
            self::$_instance = new self($config);
        }

        return self::$_instance;
    }

    public function init() {
        $this->setDispatcher(new sfEventDispatcher());
    }

    public function setDispatcher(sfEventDispatcher $dispatcher) {
        $this->_dispatcher = $dispatcher;

        // Register Dispatcher
        $logger = Ht_Utils_Logger::getInstance();
        $this->_dispatcher->connect('sms.log', array(
            $logger, 'save'
        ));
        $this->_dispatcher->connect('system.log', array(
            $logger, 'save'
        ));
    }

    public function setGateway($gateway) {
        $this->_gateway = $gateway;
        return $this;
    }

    public function setMessage($message) {
        $this->_message = $message;
        return $this;
    }

    public function setSender($sender) {
        $this->_sender = $sender;
        return $this;
    }

    public function setReceivers(array $receivers) {
        $this->_receivers = $receivers;
        return $this;
    }

    public function setConnectionTimeout($timeout) {
        $this->_connectionTimeout = (int)$timeout;
    }

    public function send() {
        if ($this->_checkParams()) {
            return $this->_send();
        }
        return false;
    }

    protected function _checkParams() {
        $error = array();
        if (!$this->_gateway) {
            array_push($error, "SMS Gateway is not defined !");
        }

        if (!$this->_sender) {
            array_push($error, "Sender is not defined !");
        }

        if (!$this->_message) {
            array_push($error, "SMS body is empty !");
        }

        if (!$this->_receivers || count($this->_receivers) == 0) {
            array_push($error, "SMS receiver is not defined !");
        }

        if (count($error) > 0) {
            $this->_dispatcher->notify(new sfEvent($this, 'sms.log', array(
                    'message' => $error, 'priority' => 5
                )));
            return false;
        }

        return true;
    }

    protected function _send() {
        $receiver = '';
        $isSentOk = array();

        while (list(, $receiver) = each($this->_receivers)) {
            // TODO : Implement here.
            $isSentOk[$receiver] = $this->__sendSMS($receiver, $this->_message, $this->_sender);
        }
        return $isSentOk;
    }

    private function __sendSMS($mobile, $message, $sender) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->_gateway);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                "message" => iconv("UTF-8", "TIS-620", $message), "mobile" => $mobile, "sender" => $sender
            ));

            $return = curl_exec($ch);
            curl_close($ch);

            $ok = false;
            if (strstr($return, 'Status=0') !== false) {
                $ok = true;
            }
            $this->_dispatcher->notify(new sfEvent($this, 'sms.log', array(
                'message' => array(
                    "[{$sender}] Send SMS to mobile number `{$mobile}` " . ($ok ? "Ok" : "Fail") . ".", strip_tags($return)
                ), 'priority' => $ok ? 6 : 3
            )));
        } catch (Exception $exc) {

            $this->_dispatcher->notify(new sfEvent($this, 'system.log', array(
                'message' => array(
                    "Code: " . $exc->getCode(), $exc->getTraceAsString()
                ), 'priority' => 1
            )));

            return false;
        }
        return true;
    }

}