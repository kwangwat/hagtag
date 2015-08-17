<?php
/**
 * 
 * @author chatupon
 *
 */
class Admin_Model_SystemLog extends MainModel {
    
    
    
    
    public function getSystemlog() {
        $db = $this->getAdapter();
        $sql = $db->select()->from("sys_log","*")->order(array("id"));
        $stmt = $sql->query();
        return $stmt->fetchAll();
    }
    
    
    
    
}