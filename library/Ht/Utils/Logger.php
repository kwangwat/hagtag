<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * @see http://components.symfony-project.org/event-dispatcher/
 */
require_once realpath(APPLICATION_PATH . '/../vendor/symfony/event_dispatcher/sfEvent.php');

/**
 * Description of Ht_Auth_Logger
 */
class Ht_Utils_Logger {
    //
    //    const EMERG   = 0;  // Emergency: system is unusable
    //    const ALERT   = 1;  // Alert: action must be taken immediately
    //    const CRIT    = 2;  // Critical: critical conditions
    //    const ERR     = 3;  // Error: error conditions
    //    const WARN    = 4;  // Warning: warning conditions
    //    const NOTICE  = 5;  // Notice: normal but significant condition
    //    const INFO    = 6;  // Informational: informational messages
    //    const DEBUG   = 7;  // Debug: debug messages

    const LOG_STREAM = 'stream';
    const LOG_DB = 'db';

    /**
     * Singleton instance
     *
     * @var package_name
     */
    protected static $_instance = null;
    protected static $_logger = array(
        self::LOG_STREAM => null,
        self::LOG_DB => null
    );
    protected $_dbLoggerName = "sys_log";

    protected $_isActive = true;
    protected $_writerTypeAvalid = array(
        'stream' => 'stream',
        'db' => 'db'
    );
    protected $_writerType = 'stream';
    protected $_logPath = "";

    public function __construct($type = null) {

        if (Zend_Registry::isRegistered('config')) {
            $_setting = Zend_Registry::get('config');

            if ($_setting instanceof Zend_Config) {
                $_setting = $_setting->toArray();
            }

            if (isset($_setting['log']['active'])) {
                $this->_isActive = ($_setting['log']['active'] == 1);
            }

            if (isset($_setting['log']['type'])) {
                $this->_writerType = (string)$_setting['log']['type'];
            }

            if (self::LOG_STREAM == $this->_writerType && isset($_setting['log']['path'])) {
                $this->_logPath = (string)$_setting['log']['path'];
                if(!realpath($this->_logPath)) {
                    mkdir($this->_logPath, 0755, TRUE);
                }
            } else {
                $this->_logPath = realpath(APPLICATION_PATH . "/../log");
            }
        }

        if (isset($type) && !is_null($type) && is_string($type) && $this->__isAvalideType($type)) {
            $this->_writerType = $type;
        }
    }

    /**
     * Returns an instance of package_name
     *
     * Singleton pattern implementation
     *
     * @return package_name Provides a fluent interface
     */
    public static function getInstance($type = null) {
        if (null === self::$_instance) {
            self::$_instance = new self($type);
        }

        return self::$_instance;
    }

    private function __isAvalideType($type) {
        return (array_search($type, $this->_writerTypeAvalid) !== false);
    }

    /**
     * Get logger
     *
     * @param sgring $type
     * @param string $logName
     * @return Zend_Log
     */
    public function getLogger($type, $logName = null) {
        if(null === self::$_logger[$type]) {
            switch ($type) {
                case self::LOG_DB :
                    self::$_logger[$type] = $this->getDbLogger();
                    break;

                default:
                    self::$_logger[$type] = $this->getStreamLogger($logName);
                    break;
            }
        }

        return self::$_logger[$type];
    }

    /**
     * Get database table logger
     *
     * @return Zend_Log
     */
    public function getDbLogger() {
        return new Zend_Log(new Zend_Log_Writer_Db(Zend_Db_Table_Abstract::getDefaultAdapter(), $this->_dbLoggerName, array(
            'user_id' => 'user_id',
            'priority' => 'priority',
            'name' => 'name',
            'message' => 'message',
            'timestamp' => 'timestamp'
        )));
    }

    /**
     * Get stream logger
     *
     * @param string $logName
     * @return Zend_Log
     */
    public function getStreamLogger($logName) {
        $prefix = date("Ymd");
        return new Zend_Log(new Zend_Log_Writer_Stream($this->_logPath . "/application_" . $prefix . '_' . $logName));
    }

    public function save(sfEvent $event) {
        if ($this->_isActive == false) {
            $event = null;
            return;
        }

        switch ($this->_writerType) {
            case self::LOG_STREAM:
                $this->saveLog($event);
                break;
            case self::LOG_DB:
                $this->saveLogDb($event);
                break;
            default:
                break;
        }
    }

    public function saveLog(sfEvent $event) {
        if ($this->_isActive == false) {
            $event = null;
            return;
        }

        $logger = $this->getLogger(self::LOG_STREAM, $event->getName());

        $params = $event->getParameters();
        $logger->log(implode(", ", $params['message']), $params['priority']);
    }

    public function saveLogDb(sfEvent $event) {

        if ($this->_isActive == false) {
            $event = null;
            return;
        }
        try {
            $logger = $this->getLogger(self::LOG_DB);
            $params = (object)$event->getParameters();

            $identity = Zend_Auth::getInstance()->getIdentity();
            $logger->setEventItem('use_id', $identity ? $identity->use_id : '-1');
            $logger->setEventItem('name', $event->getName());

            $logger->log(implode(", ", $params->message), $params->priority);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
    }

}