<?php
class Ht_Model_DbTable_SystemSetting extends Zend_Db_Table_Abstract {

    /**
     * Name of table in database
     *
     * @var string
     */
    protected $_name = "sys_settings";

    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    public function getSetting($key) {
        if (!$key) {
            return "";
        }
        $setting = $this->getSettings();
        if (!isset($setting[$key])) {
            $setting = $this->getSettings();
        }
        return (isset($setting[$key])) ? $setting[$key] : "";
    }

    public function getSettings() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select()->from("sys_settings", array("setting_name", "setting_value"))->order("setting_name");
        $setting = $db->fetchPairs($select);
        return $setting;
    }

    /**
     * Get system setting by filter
     *
     * @param array|string|null $filter
     * @return array Pairs collection data of system settings
     */
    public function getSettingsByFilter($filter = null) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select()->from("sys_settings", array("setting_name", "setting_value"))->order("setting_name");
        if(is_array($filter)) {
            foreach($filter as $key => $val) {
                if(is_numeric($key)) {
                    $select->where($val);
                } else {
                    $select->where($key . "=?", $val);
                }
            }
        } else if(is_string($filter) && strlen($filter) > 0) {
            $select->where($filter);
        }
        return $db->fetchPairs($select);
    }
}
