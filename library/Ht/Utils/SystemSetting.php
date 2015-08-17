<?php

/**
 * System configuration setting helper
 *
 * Ex.
 * require_once 'Ht/Utils/SystemSetting.php';
 * $value = Ht_Utils_SystemSetting::getSetting('<setting_key>');
 */

/**
 * @see Ht_Model_DbTable_SystemSetting
 */
require_once 'Ht/Model/DbTable/SystemSetting.php';

/**
 * Ht_Utils_SystemSetting is utility for help to get configuration setting from database
 *
 */
class Ht_Utils_SystemSetting {

    /**
     * Singleton instance
     *
     * @var Ht_Utils_SystemSetting
     */
    protected static $_instance = null;

    /**
     * Instance of Model
     *
     * @var Ht_Model_DbTable_SystemSetting
     */
    protected static $_model = null;

    /**
     * Constructor
     */
    public function __construct() {
        if(null == self::$_model) {
            self::$_model = new Ht_Model_DbTable_SystemSetting();
        }
    }

    /**
     * Returns an instance of Ht_Utils_SystemSetting
     *
     * Singleton pattern implementation
     *
     * @return Ht_Utils_SystemSetting Provides a fluent interface
     */
    public static function getInstance() {
        if(null === self::$_instance){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function getSetting($key) {
        if(null == self::$_model) {
            self::$_model = new Ht_Model_DbTable_SystemSetting();
        }
        return self::$_model->getSetting($key);
    }

    public static function getSettings() {
        if(null == self::$_model) {
            self::$_model = new Ht_Model_DbTable_SystemSetting();
        }
        return self::$_model->getSettings();
    }

    /**
     *
     * @param array|string|null $filter
     * @return array
     */
    public static function getSettingsByFilter($filter = null) {
        if(null == self::$_model) {
            self::$_model = new Ht_Model_DbTable_SystemSetting();
        }
        return self::$_model->getSettingsByFilter($filter);
    }
}
