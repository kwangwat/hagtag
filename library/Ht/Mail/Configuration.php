<?php
class Ht_Mail_Configuration {

    const CONFIGURATION_TYPE = "mail";
    /**
     * Mail Configuration
     *
     * @var Zend_Config_Ini
     */
    protected static $conf = null;

    /**
     * Get mail configuration
     *
     * @return Zend_Config_Ini
     */
    public static function get() {

        if (null == self::$conf) {
            if (($cachedConfig = self::getCached('Internal.configuration.type.' . self::CONFIGURATION_TYPE)) !== FALSE) {
                self::$conf = $cachedConfig;
            } else {
                $configPath = dirname(APPLICATION_CONFIG_FILE);
                $config = new Zend_Config_Ini($configPath."/" . self::CONFIGURATION_TYPE . ".ini", APPLICATION_ENV);
                self::$conf = $config;
                self::setCached('Internal.configuration.type.' . self::CONFIGURATION_TYPE, self::$conf);
            }
        }

        return self::$conf;
    }
}