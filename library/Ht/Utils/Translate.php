<?php
/**
 * This is about Translate.php.
 *
 */
class Ht_Utils_Translate {

    const TRANSLATE_KEY = "tr";

    /**
     * Singleton instance
     *
     * @var Ht_Utils_Translate
     */
    protected static $_instance = null;
    protected $_config_path = "";
    protected static $_cache = null;
    protected static $_translate = null;
    protected static $_section = 'en';

    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    public function __construct($config = "") {
        if (!empty($config)) {
            $this->_config_path = $this->setConfig($config);
        }

        if (null === self::$_section) {
            self::$_section = $_COOKIE['_lang'] ? $_COOKIE['_lang'] : self::$_section;
        }
        //$this->setDefaultLangConfig();
    }

    /**
     * Set configuration
     *
     * @param string $config
     * @return string
     * @throws Ht_Utils_Translate_Exception
     */
    public function setConfig($config) {
        if (!is_string($config) || !file_exists($config) || !is_dir($config)) {
            /**
             * @see Ht_Utils_Translate_Exception
             */
            throw new Ht_Utils_Translate_Exception("Bad config file path '$config'");
        }
        if (substr($config, - 1) != DIRECTORY_SEPARATOR) {
            $config .= DIRECTORY_SEPARATOR;
        }
        return $config;
    }

    /**
     * Returns an instance of System_Translate
     *
     * Singleton pattern implementation
     *
     * @return System_Translate Provides a fluent interface
     */
    public static function getInstance($config = '') {
        if (null === self::$_instance) {
            self::$_instance = new self($config);
        }

        return self::$_instance;
    }

    public function setDefaultLangConfig() {
        $section = self::$_section;
        $file = realpath(APPLICATION_PATH . "/modules/default/configs/default." . $section . ".lang");
        if (file_exists($file)) {
            Zend_Loader::loadClass('Zend_Config_Ini', 'lang');
            $result = new Zend_Config_Ini($file);
            $_translate = $result->lang->toArray();
            if (is_array($_translate)) {
                self::$_translate = array_merge((array)self::$_translate, $_translate);
            }
        }
        return $this;
    }

    public function translate($var_name) {
        return $this->_translate($var_name);
    }

    public function get($var_name, $section = null) {
        if (isset($section) && $section != '') {
            self::$_section = $section;
        }

        return $this->_translate($var_name);
    }

    protected function _isHasTranslate($var_name) {
        return isset(self::$_translate[$var_name]);
    }

    protected function _get($var_name) {
        if ($this->_isHasTranslate($var_name)) {
            return self::$_translate[$var_name];
        }
        return $var_name . ' *';
    }

    protected function _translate($var_name) {
        $tr = Zend_Registry::get(self::TRANSLATE_KEY);

        return $tr->_(strtoupper($var_name));
    }

}