<?php
defined('HT_SYSTEM_DEBUG') || define('HT_SYSTEM_DEBUG', FALSE);

class Ht_Utils {

    const CONFIGURATION_TYPE_APP = 'application';
    const CONFIGURATION_TYPE_MAIL = 'mail';
    const CONFIGURATION_TYPE_LDAP = 'ldap';
    const CONFIGURATION_TYPE_CLI = 'cli';
    const ADMIN_USER_ID = 1;

    /**
     * Singleton instance
     *
     * @var Ht_Utils_Translate
     */
    protected static $_instance = null;

    /**
     * This holds the Ht_Cache object
     *
     * @param Ht_Cache $cache The cache object.
     */
    protected static $cache;

    /**
     * This is the hash of your server's hostname. It may seem as a bug
     * or an inflexible solution and may be changed at a later point, however
     * frapi runs on it's own hostname by design and is not sharing domain
     * names just yet.
     *
     * The reason for the hash was that on the same server with multiple vhosts
     * there would be cache collisions and conflicts thus the need for a special
     * hash in the apc keys.
     */
    protected static $_hash = false;

    /**
     * Configration data from file
     *
     * @var Zend_Config_Ini
     */
    protected static $_conf = array();

    /**
     * Log variable for debugging
     *
     * @var array
     */
    protected static $_log = null;

    /**
     * Ht Mailer
     *
     * @var Ht_Mail
     */
    protected static $_mailer = null;

    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Returns an instance of Ht_Utils_FileSystem
     *
     * Singleton pattern implementation
     *
     * @return Ht_Utils_FileSystem Provides a fluent interface
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function translate($string) {
        Zend_Loader::loadClass("Ht_Utils_Translate");
        $trans = Ht_Utils_Translate::getInstance();
        $_translated = $trans->get($string);
        return ($_translated != null) ? $_translated : $string . " *";
    }

    /**
     * Get logger object
     *
     * @param string $logfile
     * @return Zend_Log Instance of zend logger object
     *
     * @deprecated Plase change to Ht_Utils::getLogger($logfile);
     */
    public static function getLog($logfile) {
        return Ht_Utils::getLogger($logfile);
    }

    /**
     * Get logger object
     * Return logger utility for do log
     *
     * @param string $logfile
     * @return Zend_Log Instance of zend logger object
     */
    public static function getLogger($logfile) {
        if (!$logfile) {
            throw new Zend_Controller_Exception("Missing log filename !");
        }
        return new Zend_Log(new Zend_Log_Writer_Stream($logfile));
    }

    /**
     * Ht_Utils::getPermissionArr
     *
     * @package Ht
     * @category Ht_Utils
     * @param number $pmNumber Number of permission.
     * @param string $permList Permisstion list.
     *
     */
    protected function convPermissionNumber2Arr(int $pmNumber, $permList = array("VIEW", "NEW", "EDIT", "DELETE", "PRINT")) {
        $base = 2;
        $i = 0;
        $count = sizeof($permList);
        $permArr = array();
        while ($i < $count) {
            $test = pow($base, $i);
            $permArr[$permList[$i]] = decbin($pmNumber & $test) ? "true" : "false";
            $i++;
        }
        return $permArr;
    }

    /**
     * Get user identity
     *
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    public static function getProfile() {
        //Zend_Loader::loadClass("Zend_Auth");
        return Zend_Auth::getInstance()->getIdentity();
    }

    /**
     * Get system mail instance
     *
     * @param string $host 	E-Mail server host name or IP Address
     * @param int $port	Port number of E-Mail server
     * @param array $config Optional configuration setting to M-Mail transportor (@see Zend_Mail_Protocol_Smtp)
     * @return Ht_Mail Mail utility instance
     */
    public static function getMail($host = "", $port = null, $config = array()) {
        Zend_Loader::loadClass("Ht_Mail");
        return Ht_Mail::getInstance($host, $port, $config);
    }

    /**
     * Get system mail instance
     *
     * @return Ht_Mail Mailer include system configuration
     */
    public static function getMailer() {
        if (null == self::$_mailer) {
            $config = self::getConfiguration(self::CONFIGURATION_TYPE_MAIL);

            $host = $config->smtp->hostname;
            $port = $config->smtp->config->port;
            $configArr = $config->smtp->config->toArray();

            Zend_Loader::loadClass("Ht_Mail");
            self::$_mailer = Ht_Mail::getInstance($host, $port, $configArr);

        }

        return self::$_mailer;
    }

    /**
     * Get user account profile
     *
     * @param int|null $userId
     * @return Zend_Auth
     */
    public static function getUserProfile($userId = null) {
        if (!$userId) {
            Zend_Loader::loadClass("Zend_Auth");
            return Zend_Auth::getInstance()->getIdentity();
        } else {
            // TODO: getUserProfile
        }

        return null;
    }

    /**
     * Logging function
     *
     * @return void
     */
    protected static function log($type, $extra = null) {
        if (defined('HT_SYSTEM_DEBUG') && HT_SYSTEM_DEBUG) {
            if (is_null(self::$_log)) {
                self::$_log = array(
                    'cache-get' => array('times' => 0, 'keys' => array()),
                    'cache-set' => array('times' => 0, 'keys' => array()),
                    'cache-delete' => array('times' => 0, 'keys' => array()),
                    'db' => array('times' => 0)
                );
            }

            switch ($type) {
                case 'cache-get':
                case 'cache-set':
                case 'cache-delete':
                    self::$_log[$type]['times']++;
                    self::$_log[$type]['keys'] [] = $extra;
                    break;
                case 'db':
                    self::$_log[$type]['times']++;
                    break;
            }
        }
    }

    /**
     * Get the activity log
     *
     * @return Array | null
     */
    public static function getActivityLog() {
        return self::$_log;
    }

    /**
     * Get the configuration
     *
     * This method retrieves and loads the information.
     *
     * @param  string $type  The configuration to load (application, ldap, mail, cli).
     * @return Zend_Config_Ini The configuration for a type
     */
    public static function getConfiguration($type) {
        $configPath = dirname(APPLICATION_CONFIG_FILE);
        $configFile = $configPath . "/" . $type . ".ini";

        if (!file_exists($configFile)) {
            throw new Ht_Exception("Configration file name `{$configFile}` not found!");
        }

        $sha1 = sha1_file($configFile);
        $config = new Zend_Config_Ini($configFile, APPLICATION_ENV);
        self::$_conf[$type] = $config;
        self::setCached('Internal.configuration.type.' . $type, self::$_conf[$type]);
        self::setCached('Internal.configuration.type.' . $type . ".sha1", $sha1);

        return self::$_conf[$type];
    }

    /**
     * Get Mail Server Configuration Information
     *
     * @return Zend_Config_Ini
     */
    public static function getMailConfiguration() {
        Zend_Loader::loadClass("Ht_Mail_Configuration");
        return Ht_Mail_Configuration::get();
    }

    /**
     * Get a hash of your server
     *
     * If you happen to have multiple installations of frapi
     * you would get apc cache collisions if we woulnd't have
     * some sort of hashing and identification of the hostnames.
     *
     * Right now this hash is very rudimentary, it's simply and sha1
     * hash of the HTTP_HOST that you are serving frapi from.
     *
     * @return string self::$_hash The sha1-server hash
     */
    public static function getHash() {
        if (self::$_hash) {
            return self::$_hash;
        }
        $host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
        self::$_hash = hash('sha1', $host);
        return self::$_hash;
    }

}
