<?php
/**
 * 
 * @author chatupon
 *
 */
class Admin_Model_SystemSetting extends MainModel {
    
    
    
    
    public function getSystemSeting() {
        $db = $this->getAdapter();
        $sql = $db->select()->from("sys_settings","*");
        $stmt = $sql->query();
        return $stmt->fetchAll();
    }
    
    public function deletedata($id) {
        $db = $this->getAdapter();
        return $db->delete("sys_settings", "setting_name =". $db->quote($id));
    }
    
    
    
    
}